<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\Http;

class SlackService{

    public function sendNotification($data){
        $body = [];
        $body = [
            'text' => $data,
        ];
        $body = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $response = Http::withHeaders($headers)->withBody($body)->post(config('services.slack.webhook_url'));
        if($response->ok()) return $response->json();
        return false;
    }
}
