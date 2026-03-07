<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('target_price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->decimal('last_notified_price', 12, 2)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index(['is_active', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
