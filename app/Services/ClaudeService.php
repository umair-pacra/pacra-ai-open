<?php

namespace App\Services;

use GuzzleHttp\Client;

class ClaudeService
{
    public function generate($prompt)
    {
        $client = new Client();

        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key'         => config('services.claude.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ],
            'json' => [
                'model' => config('services.claude.model'),
                'max_tokens' => 5000,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'text'          => $data['content'][0]['text'] ?? '',
            'input_tokens'  => $data['usage']['input_tokens'] ?? 0,
            'output_tokens' => $data['usage']['output_tokens'] ?? 0,
        ];
    }
}
