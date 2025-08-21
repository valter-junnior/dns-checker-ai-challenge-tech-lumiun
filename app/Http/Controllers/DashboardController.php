<?php

namespace App\Http\Controllers;

use App\Models\Dns;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $total = Dns::forUser($userId)->count();

        $counts = Dns::forUser($userId)
            ->selectRaw('risk, COUNT(*) as total')
            ->groupBy('risk')
            ->pluck('total', 'risk');

        $byCategory = [
            'safe'       => (int) ($counts['safe']       ?? 0),
            'suspicious' => (int) ($counts['suspicious'] ?? 0),
            'malicious'  => (int) ($counts['malicious']  ?? 0),
        ];

        $percent = [
            'safe'       => $total ? round($byCategory['safe']       * 100 / $total, 1) : 0.0,
            'suspicious' => $total ? round($byCategory['suspicious'] * 100 / $total, 1) : 0.0,
            'malicious'  => $total ? round($byCategory['malicious']  * 100 / $total, 1) : 0.0,
        ];

        $lastMalicious = Dns::forUser($userId)
            ->where('risk', 'malicious')
            ->orderByDesc('queried_at')
            ->limit(10)
            ->get(['domain', 'client_ip', 'queried_at', 'risk']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total'      => $total,
                'byCategory' => $byCategory,
                'percent'    => $percent,
            ],
            'lastMalicious' => $lastMalicious,
        ]);
    }
}
