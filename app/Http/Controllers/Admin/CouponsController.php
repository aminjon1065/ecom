<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupons;
use Illuminate\Http\RedirectResponse;
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

    public function create(): Response
    {
        return Inertia::render('admin/coupon/create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['status'] = $data['status'] ?? true;
        $data['is_active'] = $data['status'];
        $data['usage_limit'] = $data['max_use'];
        $data['usage_per_user'] = $data['usage_per_user'] ?? 1;
        $data['starts_at'] = $data['start_date'];
        $data['ends_at'] = $data['end_date'];
        $data['total_used'] = 0;

        Coupons::create($data);

        return redirect()->back();
    }

    public function update(UpdateCouponRequest $request, Coupons $coupon): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['status'] ?? true;
        $data['usage_limit'] = $data['max_use'];
        $data['starts_at'] = $data['start_date'];
        $data['ends_at'] = $data['end_date'];

        $coupon->update($data);

        return redirect()->back();
    }

    public function edit(Coupons $coupon): Response
    {
        return Inertia::render('admin/coupon/edit', [
            'coupon' => $coupon,
        ]);
    }

    public function toggleStatus(Coupons $coupon): RedirectResponse
    {
        $nextStatus = ! $coupon->status;
        $coupon->update([
            'status' => $nextStatus,
            'is_active' => $nextStatus,
        ]);

        return redirect()->back();
    }

    public function destroy(Coupons $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->back();
    }
}
