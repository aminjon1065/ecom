<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $db = false;
        $cache = false;

        try {
            DB::connection()->getPdo();
            $db = true;
        } catch (\Throwable) {
        }

        try {
            Cache::store()->put('health_check', true, 10);
            $cache = Cache::store()->get('health_check', false);
        } catch (\Throwable) {
        }

        $healthy = $db && $cache;

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'db' => $db,
            'cache' => $cache,
        ], $healthy ? 200 : 503);
    }
}
