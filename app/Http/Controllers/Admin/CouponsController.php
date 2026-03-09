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
        $data['total_used'] = 0;

        Coupons::create($data);

        return redirect()->back();
    }

    public function update(UpdateCouponRequest $request, Coupons $coupon): RedirectResponse
    {
        $coupon->update($request->validated());

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
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return redirect()->back();
    }

    public function destroy(Coupons $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->back();
    }
}
