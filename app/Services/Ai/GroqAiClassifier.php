<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Log;

class GroqAiClassifier implements AiClassifier
{
    public function __construct(private Http $http) {}

    public function classify(string $domain, ?array $enrichment = null): array
    {
        Log::info('[GroqAi] Classifying domain', ['domain' => $domain]);

        $prompt = $this->buildPrompt($domain, $enrichment);

        $response = $this->http
            ->withToken(config('services.groq.key'))
            ->asJson()
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um classificador de risco de domínios para tráfego DNS. Responda estritamente em JSON e siga o schema.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ])->throw()->json();

        $content = $response['choices'][0]['message']['content'] ?? '{}';
        $json = json_decode($content, true) ?? [];

        Log::info('[GroqAi] Classification result', ['domain' => $domain, 'result' => $json]);

        return [
            'risk'        => match (strtolower($json['risk'] ?? 'safe')) {
                'malicious', 'malicioso' => 'malicious',
                'suspicious', 'suspeito' => 'suspicious',
                default => 'safe',
            },
            'score'       => (float)($json['score'] ?? 0.5),
            'explanation' => (string)($json['explanation'] ?? ''),
            'model'       => (string)($response['model'] ?? 'groq'),
        ];
    }

    private function buildPrompt(string $domain, ?array $enrichment): string
    {
        return json_encode([
            'task' => 'Classifique o risco do domínio como Seguro, Suspeito ou Malicioso.',
            'domain' => $domain,
            'context' => [
                'enrichment' => $enrichment,
                'guidelines' => [
                    'Domínios recém-criados, TLDs arriscados (.xyz, .top), ausência de MX em domínios que enviam e-mail, reputação baixa → maior risco.',
                    'Grandes marcas com MX e WHOIS antigos → menor risco.',
                ],
            ],
            'output_schema' => [
                'risk'        => 'Seguro|Suspeito|Malicioso',
                'score'       => '0..1',
                'explanation' => 'string curta',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
