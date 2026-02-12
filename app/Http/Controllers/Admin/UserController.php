<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()->select(['id', 'name', 'email', 'avatar', 'phone', 'is_active', 'created_at']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->with('roles:id,name')->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/user/index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back();
    }
}
