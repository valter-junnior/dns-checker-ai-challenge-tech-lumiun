<?php

namespace App\Providers;

use App\Models\Dns;
use App\Observers\DnsObserver;
use App\Services\Ai\AiClassifier;
use App\Services\Ai\GroqAiClassifier;
use App\Services\Ai\OpenAiClassifier;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiClassifier::class, function ($app) {
            $driver = config('services.ai.driver');
            Log::info('[AppServiceProvider] Binding AI classifier', ['driver' => $driver]);

            return match ($driver) {
                'groq'   => $app->make(GroqAiClassifier::class),
                'openai' => $app->make(OpenAiClassifier::class),
                default  => throw new \InvalidArgumentException("AI driver inválido: {$driver}"),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('ai', fn() => Limit::perMinute(60)->by('ai-global'));
        
        Dns::observe(DnsObserver::class);
    }
}
