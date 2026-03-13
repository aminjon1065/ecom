<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingRuleRequest;
use App\Http\Requests\Admin\UpdateShippingRuleRequest;
use App\Models\ShippingRules;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShippingRulesController extends Controller
{
    public function index(): Response
    {
        $shippingRules = ShippingRules::latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/shipping-rule/index', [
            'shippingRules' => $shippingRules,
        ]);
    }

    public function store(StoreShippingRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['status'] = $data['status'] ?? true;

        ShippingRules::create($data);

        return redirect()->back();
    }

    public function update(UpdateShippingRuleRequest $request, ShippingRules $shippingRule): RedirectResponse
    {
        $data = $request->validated();

        $shippingRule->update($data);

        return redirect()->back();
    }

    public function toggleStatus(ShippingRules $shippingRule): RedirectResponse
    {
        $shippingRule->update(['status' => ! $shippingRule->status]);

        return redirect()->back();
    }

    public function destroy(ShippingRules $shippingRule): RedirectResponse
    {
        $shippingRule->delete();

        return redirect()->back();
    }
}
