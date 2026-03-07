<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->boolean('verified_purchase')->default(false)->after('status');
            $table->index(['product_id', 'status', 'verified_purchase'], 'product_reviews_visibility_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table): void {
            $table->dropIndex('product_reviews_visibility_idx');
            $table->dropColumn('verified_purchase');
        });
    }
};
