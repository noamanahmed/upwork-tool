# Memory

## Project Overview

A Laravel 12 application that fetches Upwork job postings via GraphQL API and RSS feeds, then pushes Slack notifications. It also auto-generates AI-powered proposals using multiple LLM providers and fetches proposal statuses from Upwork. The app includes a web UI with Auth0 authentication to view jobs and AI proposals.

**Stack**: PHP 8.3+, Laravel 12, MySQL 8.0, Redis, Docker Compose, Vite

## Architecture

### Core Data Flow
```
upwork:search (every 10s)
  └─ JobSearch job  [queue: scraping]
       ├─ UpWorkService::jobs()
       ├─ JobService::insertJobsFromApiResponse()        → returns NEW jobs only
       ├─ JobService::attachJobsToJobSearches()          → returns NEW pivots only
       └─ DB::transaction, then fan out AFTER commit:
            ├─ AiProposalService::dispatchForJob()       → GenerateAiJobProposal  [queue: ai]
            └─ SendJobSlackNotification(jobId, searchId) → Slack webhook          [queue: notifications]

GenerateAiJobProposal  ──→ AiJobProposalAgent ──→ AI provider
SendJobSlackNotification ──→ SlackService ──→ Slack webhook
ProposalService ──→ proposals table
```

Discovery never sends anything inline. A job that is new to the database triggers AI generation
(one row per enabled provider) and Slack delivery (one per matching search) simultaneously on
separate queues — a slow LLM call cannot block scraping or Slack.

### Queue Architecture (Laravel Horizon)

Every job declares its queue explicitly in a `$queue` property. Supervisors live in `config/horizon.php`.

| Queue | Jobs | Purpose |
|---|---|---|
| `scraping` | `JobSearch`, `RssJobSearch` | Upwork GraphQL/RSS discovery |
| `ai` | `GenerateAiJobProposal` | Provider API calls (rate limited) |
| `notifications` | `SendJobSlackNotification` | Slack webhook delivery |
| `default` | `JobActivity`, queued mail | Delayed activity snapshots, misc |

- AI concurrency is capped by `HORIZON_AI_MAX_PROCESSES` (supervisor `maxProcesses`), **not** by an application semaphore.
- `GenerateAiJobProposal` keeps its own `aiCurrentAttempt` counter and exponential backoff (10s → 600s, max 20 requeues) independent of Laravel's `$tries`.
- Provider rate limiting is an atomic Redis Lua sliding window (`ai_rate:{provider}:{md5(key)}`).
- `queue.connections.redis.retry_after` is 190s and must stay above the largest supervisor timeout (150s).
- `queue.connections.redis.after_commit` is `true`; never dispatch work for uncommitted rows.
- `CACHE_DRIVER` must be `redis` — `ShouldBeUnique` and `Cache::lock` are shared across web, scheduler, and workers.

### Scheduled Tasks (Laravel Scheduler)

All Upwork scraping tasks run conditionally based on `shouldRunUpworkTasks()`:
- **Time window**: configurable start/end time (default 21:00–03:00 Pakistan time)
- **Days**: configurable days (default Mon–Fri)
- **Toggle**: `UPWORK_CRON_ENABLED=true`

| Command | Frequency | Purpose |
|---|---|---|
| `upwork:search` | Every 10s | Dispatches one `JobSearch` job per active search |
| `upwork:proposals` | Every 15m | Fetches all proposal statuses from Upwork |
| `upwork:send-slack-notifications` | Every 5m | Safety net: re-dispatches undelivered Slack notifications |
| `upwork:generate-ai-proposals` | Every 10m | Safety net: dispatches missed proposals + reclaims stuck `queued`/`generating` rows |
| `horizon:snapshot` | Every 5m | Horizon metrics |
| `queue:prune-failed` | Daily | Drops failed jobs older than 7 days |
| `upwork:prune-telescope` | Every 15m | Clears cache/query Telescope entries older than 24h |
| `telescope:prune` | Every 3h | Prunes Telescope data older than 96h |

The two sweepers run outside the scrape window so queued work drains after 03:00.

### Directory Structure

```
app/
├── Ai/Agents/          # AI agent definitions (AiJobProposalAgent)
├── Console/Commands/    # 19 artisan commands (see commands table below)
├── Enums/               # JobSearchStatusEnum, ProposalStatusEnum, JobStatusEnum
├── Http/               # Controllers, Middleware, Requests
├── Jobs/               # Queued jobs (JobSearch, JobActivity, GenerateAiJobProposal, SendJobSlackNotification, RssJobSearch)
├── Models/             # 28 Eloquent models
├── Notifications/      # JobCreated mail notification
├── Services/           # Business logic layer
│   └── ThirdParty/     # SlackService, RssService, TranslationService
├── Repositories/       # Data access layer
├── Transformers/       # API response transformers
└── Mapper/             # Data mappers
```

### Key Services

| Service | Responsibility |
|---|---|
| **UpWorkService** | All Upwork GraphQL API calls (jobs, jobActivity, proposals, categories, skills, analytics). Handles OAuth2 token refresh. |
| **JobService** | Inserts jobs from API responses, attaches jobs to searches, handles RSS jobs. Returns the newly created rows so the caller can fan out work. Deduplication via `upwork_id` unique index + `insertOrIgnore` |
| **AiProposalService** | Single entry point for AI work: resolves enabled providers, idempotently creates `ai_job_proposals` rows, queues `GenerateAiJobProposal` |
| **JobSearchService** | CRUD operations on JobSearch configurations |
| **ProposalService** | Stores Upwork proposals linked to jobs |
| **SlackService** | Sends formatted messages to Slack webhook URLs |
| **RssService** | Parses Upwork RSS feeds using Laminas\Feed |

### Key Models & Relationships

- **JobSearch** → many-to-many → **Job** (pivot: `job_searches_jobs_pivot` with `is_slack_webhook_sent` flag)
- **Job** → hasMany → **JobActivity** (snapshots at intervals: 30m, 1h, 2h, 4h, 8h, 12h, 24h)
- **Job** → hasMany → **AiJobProposal** (per provider per job)
- **Job** → belongsToMany → **Category**, **Skill**
- **RssJobSearches** → hasMany → **RssJobs** (alternative data source)
- **Proposal** → belongsTo → **Job** (linked via upwork_job_id)

### AI Proposal System

Supports 11 AI providers: OpenAI, Anthropic, Gemini, Azure, Bedrock, Groq, xAI, DeepSeek, Mistral, Ollama, OpenRouter.

Uses `laravel/ai` package with `AiJobProposalAgent`. Each enabled provider generates proposals independently.

Statuses (`App\Enums\AiProposalStatusEnum`, stored as strings): `pending` → `queued` → `generating` → `completed` / `failed`. `AiJobProposal` has a unique `(job_id, provider)` index, so retries are idempotent. Provider rate limiting is a Redis ZSET sliding window; concurrency is capped by Horizon's `ai` supervisor processes.

Configuration in `config/services.php` → `services.ai` and `config/admin.php` for freelancer context (skills, achievements, case studies).

### Environment Variables

```
# Upwork OAuth2
UPWORK_CLIENT_ID, UPWORK_CLIENT_SECRET, UPWORK_REDIRECT_URI

# Slack
SLACK_WEBHOOK_URL

# Cron Control
UPWORK_CRON_START=21:00, UPWORK_CRON_END=03:00
UPWORK_CRON_DAYS=mon,tue,wed,thu,fri
UPWORK_CRON_ENABLED=true

# Queue / cache (Redis is required for ShouldBeUnique and Cache::lock)
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST, REDIS_PORT, REDIS_PASSWORD

# AI Provider
AI_PROVIDER=gemini, GEMINI_API_KEY, GEMINI_MODEL=gemini-flash-latest
AI_<PROVIDER>_ENABLED=true (for each provider)
AI_RATE_LIMIT=5, AI_RATE_LIMIT_INTERVAL=60, AI_MAX_PARALLEL_REQUESTS=3

# Horizon
HORIZON_AI_MAX_PROCESSES=3

# Auth0
AUTH0_BASE_URL, AUTH0_CLIENT_ID, AUTH0_CLIENT_SECRET, AUTH0_REDIRECT_URI
```

## All Artisan Commands

| Command | Purpose |
|---|---|
| `upwork:search` | Dispatch one `JobSearch` job per active search onto the `scraping` queue |
| `upwork:rss-search` | Dispatch RssJobSearch queue jobs (disabled by default) |
| `upwork:send-slack-notifications` | Safety net: re-dispatch undelivered Slack notifications (2+ min old, <5 attempts) |
| `upwork:send-rss-slack-notifications` | Send pending Slack notifications for RSS jobs (disabled) |
| `upwork:proposals` | Fetch all proposal statuses (Accepted, Declined, etc.) |
| `upwork:proposal-jobs` | Fetch job details for proposals missing job links |
| `upwork:generate-ai-proposals` | Safety net: dispatch missed proposals and reclaim stuck `queued`/`generating` rows |
| `upwork:jobs` | Bulk-fetch all historical jobs for all searches (backfill) |
| `upwork:categories-skills` | Import Upwork categories and skills into DB |
| `upwork:prune-telescope` | Delete cache/query Telescope entries older than 24h |
| `queue:clear-pending` | Clear pending/reserved/ready jobs from a queue (`scraping`, `ai`, `notifications`, `default`) |

## Slack Notification Format

Jobs get formatted as Slack messages via the `slack_notification_message` accessor on the Job model. Format includes:
- `<!channel>` mention
- Job title, description, client name, location
- Budget, job type (hourly vs fixed), applicant count
- Client stats: total hires, spend, reviews, feedback
- Job link on Upwork (`/jobs/{ciphertext}`)
- Internal proposal link (requires login)
- Public proposal link (24h temporary, no auth required)

## Deployment

- **Production**: a Forge-style VPS (site path `~/default`), no Docker. Redis on `localhost:6379`.
  - Horizon daemon: `php artisan horizon` (not `horizon:work`), `stopwaitsecs` > 150, run `php artisan horizon:terminate` after each deploy.
  - Scheduler: cron `* * * * * cd /home/upwork/default && php artisan schedule:run` (Laravel 11+ handles sub-minute tasks). Fallback: a daemon running `php artisan schedule:work`.
  - `CACHE_DRIVER=redis` is required — `ShouldBeUnique` and `Cache::lock` must be shared across processes.
- **Docker Compose** (`serversideup/php:8.3-fpm-nginx`) still exists for local dev: app (port 10999), MySQL 8.0 (3326), Redis (6479), Mailpit, Adminer (9166). Its `horizon` service is not used in production.
- **GitLab CI**: Runs migrations, seeds, and pest tests with coverage on every push (`QUEUE_CONNECTION=sync` in tests).

## Code Style Guidelines
- Use descriptive variable names
- Follow existing patterns in the codebase
- Extract complex conditions into meaningful boolean variables
- Service classes extend BaseService which provides CRUD, API response helpers, and transformer integration

## Common Workflows

### Adding a new job search
1. Insert record into `job_searches` table with: name, `q` (query), `slack_webhook_url`, filter options (client hires, feedback, proposals, location, days posted)
2. The `upwork:search` command auto-discovers it via cached query every 15 minutes

### Testing Slack notifications
Dispatch `SendJobSlackNotification($jobId, $jobSearchId)` directly, or set `is_slack_webhook_sent=0`
and `slack_attempts=0` on a pivot record so the sweeper picks it up. The job is idempotent: it
re-checks the pivot before sending and writes `slack_sent_at` on success. Failures increment
`slack_attempts` and store `slack_last_error`.

### Running the app locally
```bash
# Start all services
docker-compose up -d

# Run the scheduler (required for job fetching)
docker-compose exec app php artisan schedule:work

# Or run individual commands
docker-compose exec app php artisan upwork:search
docker-compose exec app php artisan upwork:send-slack-notifications
```

### Running tests
```bash
docker-compose exec app php artisan test
docker-compose exec app php artisan test --coverage-html=coverage/
```
