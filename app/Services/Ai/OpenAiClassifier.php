<?php 

namespace App\Services\Ai;

use Illuminate\Support\Facades\Log;

class OpenAiClassifier implements AiClassifier {
    public function __construct(private \Illuminate\Http\Client\Factory $http) {}

    public function classify(string $domain, ?array $enrichment = null): array {
        $prompt = $this->buildPrompt($domain, $enrichment);

        Log::info('[OpenAi] Classifying domain', ['domain' => $domain]);

        $res = $this->http->withToken(config('services.openai.key'))
          ->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model','gpt-4o-mini'),
            'temperature' => 0,
            'messages' => [
              ['role'=>'system','content'=>'Você é um classificador de risco de domínios para tráfego DNS. Responda estritamente em JSON.'],
              ['role'=>'user','content'=>$prompt],
            ],
          ])->throw()->json();

        $json = json_decode($res['choices'][0]['message']['content'] ?? '{}', true);

        Log::info('[OpenAi] Classification result', ['domain' => $domain, 'result' => $json]);

        return [
          'risk'        => match(strtolower($json['risk'] ?? 'safe')) {
              'malicious','malicioso' => 'malicious',
              'suspicious','suspeito' => 'suspicious',
              default => 'safe',
          },
          'score'       => (float)($json['score'] ?? 0.5),
          'explanation' => (string)($json['explanation'] ?? ''),
          'model'       => (string)($res['model'] ?? 'openai'),
        ];
    }

    private function buildPrompt(string $domain, ?array $enrichment): string {
        return json_encode([
          'task' => 'Classifique o risco do domínio como Seguro, Suspeito ou Malicioso.',
          'domain' => $domain,
          'context' => [
            'enrichment' => $enrichment,
            'guidelines' => [
              'Domínios recém-criados, TLDs arriscados (.xyz, .top), ausência de MX em domínios que enviam e-mail, reputação alta → maior risco.',
              'Grandes marcas com MX e WHOIS antigos → menor risco.',
            ],
          ],
          'output_schema' => ['risk'=>'Seguro|Suspeito|Malicioso','score'=>'0..1','explanation'=>'string curta'],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
