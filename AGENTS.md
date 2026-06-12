# Memory

## Project Overview

A Laravel 12 application that fetches Upwork job postings via GraphQL API and RSS feeds, then pushes Slack notifications. It also auto-generates AI-powered proposals using multiple LLM providers and fetches proposal statuses from Upwork. The app includes a web UI with Auth0 authentication to view jobs and AI proposals.

**Stack**: PHP 8.3+, Laravel 12, MySQL 8.0, Redis, Docker Compose, Vite

## Architecture

### Core Data Flow
```
Upwork GraphQL API ──→ UpWorkService ──→ JobService ──→ jobs table
                                                       │
                                              JobSearch (queued job)
                                                       │
                                        SlackService ←─┘──→ Slack webhook
                                                       │
                                   GenerateAiJobProposal ──→ AiJobProposalAgent ──→ AI provider
                                                       │
                                   ProposalService ──→ proposals table
```

### Scheduled Tasks (Laravel Scheduler)

All Upwork tasks run conditionally based on `shouldRunUpworkTasks()`:
- **Time window**: configurable start/end time (default 21:00–03:00 Pakistan time)
- **Days**: configurable days (default Mon–Fri)
- **Toggle**: `UPWORK_CRON_ENABLED=true`

| Command | Frequency | Purpose |
|---|---|---|
| `upwork:search` | Every 10s | Dispatches JobSearch queued jobs for each configured search |
| `upwork:send-slack-notifications` | Every 5s | Sends unsent Slack notifications for new jobs |
| `upwork:proposals` | Every 15m | Fetches all proposal statuses from Upwork |
| `upwork:generate-ai-proposals` | Every 1m | Dispatches AI proposal generation for all enabled providers |
| `upwork:prune-telescope` | Every 15m | Clears cache/query Telescope entries older than 24h |
| `telescope:prune` | Every 3h | Prunes Telescope data older than 96h |

### Directory Structure

```
app/
├── Ai/Agents/          # AI agent definitions (AiJobProposalAgent)
├── Console/Commands/    # 19 artisan commands (see commands table below)
├── Enums/               # JobSearchStatusEnum, ProposalStatusEnum, JobStatusEnum
├── Http/               # Controllers, Middleware, Requests
├── Jobs/               # Queued jobs (JobSearch, JobActivity, GenerateAiJobProposal, RssJobSearch)
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
| **JobService** | Inserts jobs from API responses, attaches jobs to searches, handles RSS jobs, deduplication via upwork_id |
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

Uses `laravel/ai` package with `AiJobProposalAgent`. Each enabled provider generates proposals independently. Rate limiting and concurrency control via Redis ZSETs and semaphores.

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

# AI Provider
AI_PROVIDER=gemini, GEMINI_API_KEY, GEMINI_MODEL=gemini-flash-latest
AI_<PROVIDER>_ENABLED=true (for each provider)

# Auth0
AUTH0_BASE_URL, AUTH0_CLIENT_ID, AUTH0_CLIENT_SECRET, AUTH0_REDIRECT_URI
```

## All Artisan Commands

| Command | Purpose |
|---|---|
| `upwork:search` | Dispatch JobSearch queue jobs for all active job searches |
| `upwork:rss-search` | Dispatch RssJobSearch queue jobs (disabled by default) |
| `upwork:send-slack-notifications` | Send pending Slack notifications for GraphQL jobs |
| `upwork:send-rss-slack-notifications` | Send pending Slack notifications for RSS jobs (disabled) |
| `upwork:proposals` | Fetch all proposal statuses (Accepted, Declined, etc.) |
| `upwork:proposal-jobs` | Fetch job details for proposals missing job links |
| `upwork:generate-ai-proposals` | Generate AI proposals for recent jobs across all enabled providers |
| `upwork:jobs` | Bulk-fetch all historical jobs for all searches (backfill) |
| `upwork:categories-skills` | Import Upwork categories and skills into DB |
| `upwork:prune-telescope` | Delete cache/query Telescope entries older than 24h |
| `queue:clear-pending` | Clear all pending/reserved/ready Redis queue jobs |

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

- **Docker Compose** with `serversideup/php:8.3-fpm-nginx` image
- Services: app (PHP + Nginx on port 10999), MySQL 8.0 (port 3326), Redis (port 6479), Mailpit, Adminer (port 9166)
- **GitLab CI**: Runs migrations, seeds, and pest tests with coverage on every push

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
Send a test notification using the SlackService directly, or set `is_slack_webhook_sent=0` on a pivot record to re-trigger sending.

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
