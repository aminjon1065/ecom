<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::query()
            ->with('user:id,name,email')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($modelType = $request->input('model_type')) {
            $query->where('model_type', $modelType);
        }

        $modelTypes = AuditLog::query()
            ->whereNotNull('model_type')
            ->distinct()
            ->pluck('model_type')
            ->map(fn (string $fqn) => [
                'value' => $fqn,
                'label' => class_basename($fqn),
            ])
            ->values();

        return Inertia::render('admin/audit-log/index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'modelTypes' => $modelTypes,
            'filters' => [
                'search' => $request->input('search', ''),
                'action' => $request->input('action', ''),
                'model_type' => $request->input('model_type', ''),
            ],
        ]);
    }
}
