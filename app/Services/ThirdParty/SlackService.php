<?php

namespace App\Services\ThirdParty;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SlackService
{
    protected $webhookUrl = null;

    public function setWebhookUrl($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    /**
     * Send a plain-text message and throw when Slack does not accept it.
     */
    public function sendMessage(string $webhookUrl, string $text): void
    {
        try {
            Http::timeout(10)
                ->connectTimeout(5)
                ->withBody(json_encode(['text' => $text]), 'application/json')
                ->post($webhookUrl)
                ->throw();
        } catch (Throwable $e) {
            Log::error('Slack notification failed', [
                'status' => $e instanceof RequestException ? $e->response->status() : null,
                'message_hash' => md5($text),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @deprecated Use sendMessage() with an explicit webhook URL.
     */
    public function sendNotification($data)
    {
        if (empty($this->webhookUrl)) {
            throw new RuntimeException('Slack webhook URL is not set.');
        }

        $this->sendMessage($this->webhookUrl, (string) $data);

        return true;
    }
}
