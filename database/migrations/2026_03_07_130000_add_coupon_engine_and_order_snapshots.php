<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->timestamp('starts_at')->nullable()->after('end_date');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->unsignedInteger('usage_limit')->nullable()->after('max_use');
            $table->unsignedInteger('usage_per_user')->nullable()->after('usage_limit');
            $table->decimal('min_subtotal', 10, 2)->nullable()->after('discount');
            $table->boolean('is_active')->default(true)->after('status');
            $table->boolean('first_order_only')->default(false)->after('is_active');
        });

        Schema::create('coupon_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamp('used_at');
            $table->timestamps();
            $table->unique(['coupon_id', 'user_id', 'order_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('subtotal', 12, 2)->default(0)->after('amount');
            $table->decimal('discount_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_total', 12, 2)->default(0)->after('discount_total');
            $table->decimal('grand_total', 12, 2)->default(0)->after('shipping_total');
            $table->string('coupon_code')->nullable()->after('coupon');
        });

        Schema::table('order_products', function (Blueprint $table): void {
            $table->decimal('discount_amount', 12, 2)->default(0)->after('unit_price');
            $table->decimal('line_total', 12, 2)->default(0)->after('discount_amount');
            $table->string('product_name')->nullable()->after('line_total');
            $table->string('product_sku')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table): void {
            $table->dropColumn([
                'discount_amount',
                'line_total',
                'product_name',
                'product_sku',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'subtotal',
                'discount_total',
                'shipping_total',
                'grand_total',
                'coupon_code',
            ]);
        });

        Schema::dropIfExists('coupon_usages');

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn([
                'starts_at',
                'ends_at',
                'usage_limit',
                'usage_per_user',
                'min_subtotal',
                'is_active',
                'first_order_only',
            ]);
        });
    }
};
