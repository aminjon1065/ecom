<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cart: replace the old (user_id, product_id) unique with
        // (user_id, product_id, product_variant_item_id) so that
        // the same product in different variants occupies separate cart slots.
        Schema::table('carts', function (Blueprint $table): void {
            // MySQL uses the composite unique as the supporting index for the user_id FK.
            // Add a dedicated user_id index so we can safely drop the composite unique.
            $table->index('user_id', 'carts_user_id_index');
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->dropUnique('carts_user_id_product_id_unique');
            $table->foreignId('product_variant_item_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variant_items')
                ->nullOnDelete();
            // Application logic enforces non-variant uniqueness;
            // this index covers the variant case.
            $table->unique(['user_id', 'product_id', 'product_variant_item_id']);
        });

        // OrderProduct: snapshot the selected variant so order history
        // shows exactly what the customer ordered, even if variants change later.
        Schema::table('order_products', function (Blueprint $table): void {
            $table->foreignId('product_variant_item_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variant_items')
                ->nullOnDelete();
            $table->string('variant_name')->nullable()->after('product_variant_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'product_id', 'product_variant_item_id']);
            $table->dropConstrainedForeignId('product_variant_item_id');
            $table->index('user_id', 'carts_user_id_index_temp');
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->dropIndex('carts_user_id_index_temp');
            $table->dropIndex('carts_user_id_index');
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('order_products', function (Blueprint $table): void {
            $table->dropColumn('variant_name');
            $table->dropConstrainedForeignId('product_variant_item_id');
        });
    }
};
