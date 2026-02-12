<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:flat,free_shipping,min_cost'],
            'min_cost' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        $data['status'] = $data['status'] ?? true;

        ShippingRules::create($data);

        return redirect()->back();
    }

    public function update(Request $request, ShippingRules $shippingRule): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:flat,free_shipping,min_cost'],
            'min_cost' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        $shippingRule->update($data);

        return redirect()->back();
    }

    public function toggleStatus(ShippingRules $shippingRule): RedirectResponse
    {
        $shippingRule->update(['status' => !$shippingRule->status]);

        return redirect()->back();
    }

    public function destroy(ShippingRules $shippingRule): RedirectResponse
    {
        $shippingRule->delete();

        return redirect()->back();
    }
}
