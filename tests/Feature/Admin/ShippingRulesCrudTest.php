<?php

use App\Models\ShippingRules;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeShippingAdmin(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

it('admin can create a flat rate shipping rule', function () {
    $admin = makeShippingAdmin();

    $this->actingAs($admin)->post(route('admin.shipping-rule.store'), [
        'name' => 'Standard Flat Rate',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 150.0,
        'status' => true,
    ])->assertRedirect();

    $rule = ShippingRules::query()->where('name', 'Standard Flat Rate')->firstOrFail();

    expect($rule->type)->toBe('flat')
        ->and($rule->cost)->toBe(150.0)
        ->and($rule->status)->toBeTrue();
});

it('admin can create a free shipping rule', function () {
    $admin = makeShippingAdmin();

    $this->actingAs($admin)->post(route('admin.shipping-rule.store'), [
        'name' => 'Free Shipping',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0.0,
        'status' => true,
    ])->assertRedirect();

    $rule = ShippingRules::query()->where('name', 'Free Shipping')->firstOrFail();

    expect($rule->type)->toBe('free_shipping')
        ->and($rule->cost)->toBe(0.0);
});

it('admin can create a min cost shipping rule', function () {
    $admin = makeShippingAdmin();

    $this->actingAs($admin)->post(route('admin.shipping-rule.store'), [
        'name' => 'Free over 500',
        'type' => 'min_cost',
        'min_cost' => 500.0,
        'cost' => 0.0,
        'status' => true,
    ])->assertRedirect();

    $rule = ShippingRules::query()->where('name', 'Free over 500')->firstOrFail();

    expect($rule->type)->toBe('min_cost')
        ->and((float) $rule->min_cost)->toBe(500.0);
});

it('admin can update a shipping rule', function () {
    $admin = makeShippingAdmin();

    $rule = ShippingRules::query()->create([
        'name' => 'Old Name',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 100.0,
        'status' => true,
    ]);

    $this->actingAs($admin)->put(route('admin.shipping-rule.update', $rule), [
        'name' => 'Updated Name',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 200.0,
        'status' => true,
    ])->assertRedirect();

    expect($rule->fresh()->name)->toBe('Updated Name')
        ->and($rule->fresh()->cost)->toBe(200.0);
});

it('admin can toggle shipping rule status', function () {
    $admin = makeShippingAdmin();

    $rule = ShippingRules::query()->create([
        'name' => 'Toggle Rule',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 50.0,
        'status' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.shipping-rule.toggle-status', $rule))->assertRedirect();

    expect($rule->fresh()->status)->toBeFalse();
});

it('admin can delete a shipping rule', function () {
    $admin = makeShippingAdmin();

    $rule = ShippingRules::query()->create([
        'name' => 'Delete Me',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0.0,
        'status' => true,
    ]);

    $this->actingAs($admin)->delete(route('admin.shipping-rule.destroy', $rule))->assertRedirect();

    expect(ShippingRules::query()->find($rule->id))->toBeNull();
});

it('shipping rule store rejects invalid type', function () {
    $admin = makeShippingAdmin();

    $this->actingAs($admin)->post(route('admin.shipping-rule.store'), [
        'name' => 'Bad Type',
        'type' => 'invalid_type',
        'min_cost' => null,
        'cost' => 50.0,
    ])->assertSessionHasErrors('type');
});

it('shipping rule store requires cost', function () {
    $admin = makeShippingAdmin();

    $this->actingAs($admin)->post(route('admin.shipping-rule.store'), [
        'name' => 'No Cost',
        'type' => 'flat',
        'min_cost' => null,
    ])->assertSessionHasErrors('cost');
});
