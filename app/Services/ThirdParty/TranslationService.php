<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\Http;

class TranslationService{

    public function translateJson($json, $toLanguage , $fromLanguage = 'en'){
        $body = [];
        $body = [
            'to' => $toLanguage,
            'from' => $fromLanguage,
            'json' => $json,
        ];

        $headers = [
            'X-RapidAPI-Host' => config('rapidapi.translation.api_host'),
            'X-RapidAPI-Key' =>config('rapidapi.translation.api_key'),
        ];

        $response = Http::withHeaders($headers)->post(config('rapidapi.translation.json_api_url'),$body);
        if($response->ok()) return $response->json()['trans'];
        return false;
    }
}
