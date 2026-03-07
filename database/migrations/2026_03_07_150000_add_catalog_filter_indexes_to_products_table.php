<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index(['status', 'is_approved', 'category_id', 'brand_id', 'price'], 'products_catalog_filter_idx');
            $table->index(['status', 'is_approved', 'qty'], 'products_stock_sort_idx');
            $table->index(['status', 'is_approved', 'created_at'], 'products_listing_latest_idx');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table): void {
                $table->fullText(['name', 'short_description'], 'products_name_short_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropFullText('products_name_short_fulltext');
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_catalog_filter_idx');
            $table->dropIndex('products_stock_sort_idx');
            $table->dropIndex('products_listing_latest_idx');
        });
    }
};
