<?php

namespace App\Jobs;

use App\Models\Dns;
use App\Services\Ai\AiClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class ClassifyDomainJob implements ShouldQueue
{
    public function __construct(public string $dnsId) {}
    
    public function handle(AiClassifier $ai)
    {
        $dns = Dns::findOrFail($this->dnsId);

        try {
            $res = $ai->classify($dns->domain, $dns->enrichment);
            $dns->update([
                'risk'          => $res['risk'],
                'ai_score'      => $res['score'],
                'ai_model'      => $res['model'],
                'ai_explanation' => Str::limit($res['explanation'], 2000),
                'status'        => 'classified',
                'error'         => null,
            ]);
        } catch (\Throwable $e) {
            $dns->update(['status' => 'error', 'error' => $e->getMessage()]);
            report($e);
        }
    }
}
