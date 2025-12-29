<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Neutron');
            $table->string('contact_email')->default('info@neutron.tj');
            $table->string('contact_phone')->nullable()->default("+992000001314");
            $table->string('contact_address')->nullable();
            $table->string('telegram_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('somontj_link')->nullable();
            $table->text('address')->nullable();
            $table->text('address_on_map')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
