<?php

namespace App\Jobs;

use App\Jobs\ClassifyDomainJob;
use App\Models\Dns;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

class ImportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId, public string $path) {}

    public function handle()
    {
        $stream = Storage::readStream($this->path);
        $header = null;
        $ttlSeconds = 3600;

        while (($row = fgetcsv($stream)) !== false) {
            if (!$header) {
                $header = $row;
                continue;
            }

            [$ts, $domain, $ip] = $row;

            $domain = strtolower(trim($domain));
            if (
                !filter_var($ip, FILTER_VALIDATE_IP) ||
                !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)
            ) {
                continue;
            }

            $key = "dns:import:{$this->userId}:" . md5("{$domain}|{$ip}");

            if (Redis::setnx($key, 1)) {
                Redis::expire($key, $ttlSeconds);

                $dns = Dns::updateOrCreate([
                    'user_id'   => $this->userId,
                    'domain'    => $domain,
                    'client_ip' => $ip,
                ],[
                    'user_id'    => $this->userId,
                    'queried_at' => Carbon::parse($ts),
                    'domain'     => $domain,
                    'client_ip'  => $ip,
                    'status'     => 'pending',
                ]);

                dispatch(new ClassifyDomainJob($dns->id));
            }
        }

        fclose($stream);
    }
}
