<?php

namespace App\Services\Ai;

interface AiClassifier {
    /** @return array{risk:string, score:float, explanation:string, model:string} */
    public function classify(string $domain, ?array $enrichment = null): array;
}