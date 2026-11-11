# Taste (Continuously Learned by [CommandCode][cmd])

[cmd]: https://commandcode.ai/

# architecture
- For custom re-queue logic (e.g., rate-limit retries), maintain a separate retry counter independent of Laravel's built-in `$tries` to avoid conflating custom backoff with framework retry semantics. Confidence: 0.70
- Use exponential backoff for rate-limit re-queues rather than fixed delays. Confidence: 0.70
- Prefers Laravel Horizon with separate named queues for distinct workloads (e.g., scraping, Slack notifications, and AI generation) rather than a shared default queue. Confidence: 0.85

# workflow
- For architecture work, wants the existing system analyzed first (gaps, technical debt) and then a concrete implementation plan presented before code is written. Confidence: 0.6

