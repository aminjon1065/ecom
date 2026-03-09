<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // orders: admin filters by status & payment; users sort by date
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'order_status'], 'orders_user_status_idx');
            $table->index(['order_status', 'created_at'], 'orders_status_date_idx');
            $table->index('payment_status', 'orders_payment_status_idx');
        });

        // product_reviews: public listing filters by product + approved status
        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->index(['product_id', 'status'], 'reviews_product_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_user_status_idx');
            $table->dropIndex('orders_status_date_idx');
            $table->dropIndex('orders_payment_status_idx');
        });

        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->dropIndex('reviews_product_status_idx');
        });
    }
};
