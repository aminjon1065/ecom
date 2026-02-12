<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponsController extends Controller
{
    public function index(): Response
    {
        $coupons = Coupons::latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/coupon/index', [
            'coupons' => $coupons,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:coupons,code'],
            'quantity' => ['required', 'integer', 'min:1'],
            'max_use' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'discount_type' => ['required', 'string', 'in:percent,fixed'],
            'discount' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        $data['status'] = $data['status'] ?? true;
        $data['total_used'] = 0;

        Coupons::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Coupons $coupon): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:coupons,code,' . $coupon->id],
            'quantity' => ['required', 'integer', 'min:1'],
            'max_use' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'discount_type' => ['required', 'string', 'in:percent,fixed'],
            'discount' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        $coupon->update($data);

        return redirect()->back();
    }

    public function toggleStatus(Coupons $coupon): RedirectResponse
    {
        $coupon->update(['status' => !$coupon->status]);

        return redirect()->back();
    }

    public function destroy(Coupons $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->back();
    }
}
