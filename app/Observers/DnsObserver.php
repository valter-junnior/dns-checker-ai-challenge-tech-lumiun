<?php

namespace App\Observers;

use App\Events\DnsEvent;
use App\Models\Dns;

class DnsObserver
{
    public function created(Dns $dns): void
    {
        event(new DnsEvent($dns->fresh()));
    }

    public function updated(Dns $dns): void
    {
        event(new DnsEvent($dns->fresh()));
    }

    public function deleted(Dns $dns): void
    {
        event(new DnsEvent($dns));
    }
}
