<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Coupon code is looked up on every checkout — needs a unique index for O(1) lookup.
        // is_active is filtered on every coupon validation query.
        Schema::table('coupons', function (Blueprint $table): void {
            $table->unique('code')->change();
            $table->index('is_active');
        });

        // Newsletter subscribers: email looked up on subscribe/unsubscribe,
        // is_verified filtered when sending bulk newsletters.
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->unique('email');
            $table->index('is_verified');
        });

        // Blog status filtered on all public blog listings.
        Schema::table('blogs', function (Blueprint $table): void {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropUnique(['email']);
            $table->dropIndex(['is_verified']);
        });

        Schema::table('blogs', function (Blueprint $table): void {
            $table->dropIndex(['status']);
        });
    }
};
