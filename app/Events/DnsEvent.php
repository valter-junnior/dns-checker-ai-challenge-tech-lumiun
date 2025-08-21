<?php 

namespace App\Events;

use App\Models\Dns;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class DnsEvent implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public Dns $dns) {}

    public function broadcastOn(): Channel
    {
        return new Channel('dns.'.$this->dns->user_id);
    }

    public function broadcastAs(): string
    {
        return 'DnsEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->dns->id,
            'user_id'    => $this->dns->user_id,
            'domain'     => $this->dns->domain,
            'client_ip'  => $this->dns->client_ip,
            'risk'       => $this->dns->risk,
            'ai_score'   => $this->dns->ai_score,
            'ai_model'   => $this->dns->ai_model,
            'status'     => $this->dns->status,
            'queried_at' => optional($this->dns->queried_at)->toISOString(),
            'updated_at' => optional($this->dns->updated_at)->toISOString(),
        ];
    }
}
