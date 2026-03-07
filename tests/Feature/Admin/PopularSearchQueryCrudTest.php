<?php

use App\Models\PopularSearchQuery;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeAdminForPopularSearchCrud(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

it('creates popular search query from admin panel', function () {
    $admin = makeAdminForPopularSearchCrud();

    $response = $this->actingAs($admin)->post(route('admin.popular-search-query.store'), [
        'query' => 'iPhone',
        'priority' => 120,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.popular-search-query.index'));

    $record = PopularSearchQuery::query()->where('query', 'iPhone')->firstOrFail();

    expect($record->priority)->toBe(120)
        ->and($record->is_active)->toBeTrue();
});

it('renders popular search create and edit pages in admin panel', function () {
    $admin = makeAdminForPopularSearchCrud();
    $query = PopularSearchQuery::query()->create([
        'query' => 'Galaxy',
        'priority' => 10,
        'is_active' => true,
    ]);

    $createResponse = $this->actingAs($admin)->get(route('admin.popular-search-query.create'));
    $createResponse->assertSuccessful();
    $createResponse->assertInertia(fn (Assert $page) => $page->component('admin/popular-search-query/create'));

    $editResponse = $this->actingAs($admin)->get(route('admin.popular-search-query.edit', $query));
    $editResponse->assertSuccessful();
    $editResponse->assertInertia(fn (Assert $page) => $page
        ->component('admin/popular-search-query/edit')
        ->where('popularSearchQuery.id', $query->id)
        ->where('popularSearchQuery.query', $query->query)
    );
});

it('updates, toggles and deletes popular search query from admin panel', function () {
    $admin = makeAdminForPopularSearchCrud();
    $query = PopularSearchQuery::query()->create([
        'query' => 'Old Query',
        'priority' => 1,
        'is_active' => true,
    ]);

    $updateResponse = $this->actingAs($admin)->put(route('admin.popular-search-query.update', $query), [
        'query' => 'Updated Query',
        'priority' => 50,
        'is_active' => false,
    ]);

    $updateResponse->assertRedirect(route('admin.popular-search-query.index'));

    expect($query->fresh()->query)->toBe('Updated Query')
        ->and($query->fresh()->priority)->toBe(50)
        ->and($query->fresh()->is_active)->toBeFalse();

    $toggleResponse = $this->actingAs($admin)->patch(route('admin.popular-search-query.toggle-status', $query));
    $toggleResponse->assertRedirect();
    expect($query->fresh()->is_active)->toBeTrue();

    $deleteResponse = $this->actingAs($admin)->delete(route('admin.popular-search-query.destroy', $query));
    $deleteResponse->assertRedirect();

    expect(PopularSearchQuery::query()->whereKey($query->id)->exists())->toBeFalse();
});
