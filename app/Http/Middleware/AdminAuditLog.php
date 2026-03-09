<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditLog
{
    private const SENSITIVE_FIELDS = ['password', 'password_confirmation', 'current_password', '_token'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') || ! Auth::check()) {
            return $response;
        }

        if (! in_array($response->getStatusCode(), [200, 201, 302, 303], true)) {
            return $response;
        }

        $this->record($request);

        return $response;
    }

    private function record(Request $request): void
    {
        $payload = collect($request->except(self::SENSITIVE_FIELDS))
            ->filter(fn ($v) => ! is_null($v) && $v !== '' && is_scalar($v))
            ->toArray();

        [$modelType, $modelId] = $this->resolveModel($request);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => strtoupper($request->method()).' '.$request->path(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'new_values' => $payload ?: null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    private function resolveModel(Request $request): array
    {
        $segments = $request->segments();
        $knownTypes = [
            'products' => 'App\\Models\\Product',
            'product' => 'App\\Models\\Product',
            'orders' => 'App\\Models\\Order',
            'order' => 'App\\Models\\Order',
            'users' => 'App\\Models\\User',
            'user' => 'App\\Models\\User',
            'vendors' => 'App\\Models\\Vendor',
            'vendor' => 'App\\Models\\Vendor',
            'brands' => 'App\\Models\\Brand',
            'brand' => 'App\\Models\\Brand',
            'categories' => 'App\\Models\\Category',
            'category' => 'App\\Models\\Category',
            'coupons' => 'App\\Models\\Coupons',
            'coupon' => 'App\\Models\\Coupons',
            'reviews' => 'App\\Models\\ProductReview',
            'review' => 'App\\Models\\ProductReview',
            'sliders' => 'App\\Models\\Slider',
            'slider' => 'App\\Models\\Slider',
        ];

        foreach ($segments as $i => $segment) {
            if (isset($knownTypes[$segment])) {
                $id = $segments[$i + 1] ?? null;

                return [$knownTypes[$segment], is_numeric($id) ? (int) $id : null];
            }
        }

        return [null, null];
    }
}
