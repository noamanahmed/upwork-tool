<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\Http;

class SlackService{

    protected $webhookUrl = null;
    public function setWebhookUrl($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }
    public function sendNotification($data){
        $body = [];
        $body = [
            'text' => $data,
        ];
        $body = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $response = Http::withHeaders($headers)->withBody($body)->post($this->webhookUrl);
        if($response->ok()) return $response->json();
        return false;
    }
}
