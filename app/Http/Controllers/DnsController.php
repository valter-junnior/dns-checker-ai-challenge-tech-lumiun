<?php

namespace App\Http\Controllers;

use App\Models\Dns;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DnsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search'    => $request->string('search')->toString() ?: null,
            'risk'      => $request->string('risk')->toString() ?: null,
            'status'    => $request->string('status')->toString() ?: null,
            'user_id'   => $request->string('user_id')->toString() ?: null,
            'from'      => $request->string('from')->toString() ?: null,
            'to'        => $request->string('to')->toString() ?: null,
        ];

        $q = Dns::query()
            ->forUser(auth()->id())
            ->with(['user:id,name'])
            ->when($filters['search'], function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('domain', 'like', "%{$term}%")
                        ->orWhere('client_ip', 'like', "%{$term}%")
                        ->orWhere('ai_model', 'like', "%{$term}%");
                });
            })
            ->when($filters['risk'], fn($q, $risk) => $q->where('risk', $risk))
            ->when($filters['status'], fn($q, $status) => $q->where('status', $status))
            ->when($filters['user_id'], fn($q, $uid) => $q->where('user_id', $uid))
            ->when($filters['from'], function ($q, $from) {
                $q->whereDate('queried_at', '>=', $from);
            })
            ->when($filters['to'], function ($q, $to) {
                $q->whereDate('queried_at', '<=', $to);
            })
            ->orderByDesc('queried_at');

        $dns = $q->paginate(20)->withQueryString();

        return Inertia::render('Dns/Index', [
            'items'   => $dns,
            'filters' => array_filter($filters, fn($v) => !is_null($v) && $v !== ''),
            'enums'   => [
                'risk'   => ['safe', 'suspicious', 'malicious'],
                'status' => ['pending', 'enriched', 'classified', 'error'],
            ],
        ]);
    }
}
