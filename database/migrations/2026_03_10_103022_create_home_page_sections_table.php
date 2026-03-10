<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_page_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('position');
            $table->string('type');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_page_sections');
    }
};
