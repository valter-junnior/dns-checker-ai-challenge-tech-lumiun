<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;

class Dns extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'user_id',
        'queried_at',
        'domain',
        'client_ip',
        'risk',
        'ai_explanation',
        'ai_score',
        'ai_model',
        'enrichment',
        'status',
        'error'
    ];

    protected $casts = [
        'queried_at' => 'datetime',
        'enrichment' => 'array',
    ];

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
