<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate legacy data to canonical fields before dropping.
        DB::table('coupons')->whereNull('starts_at')->whereNotNull('start_date')
            ->update(['starts_at' => DB::raw('start_date')]);

        DB::table('coupons')->whereNull('ends_at')->whereNotNull('end_date')
            ->update(['ends_at' => DB::raw('end_date')]);

        DB::table('coupons')->whereNull('usage_limit')
            ->update(['usage_limit' => DB::raw('max_use')]);

        // Sync is_active from status for rows where they differ.
        DB::table('coupons')->whereColumn('is_active', '!=', 'status')
            ->update(['is_active' => DB::raw('status')]);

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn(['start_date', 'end_date', 'max_use', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->after('quantity');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('max_use')->default(0)->after('end_date');
            $table->boolean('status')->default(true)->after('discount');
        });

        // Restore legacy data from canonical fields.
        DB::table('coupons')->update([
            'start_date' => DB::raw('DATE(starts_at)'),
            'end_date' => DB::raw('DATE(ends_at)'),
            'max_use' => DB::raw('COALESCE(usage_limit, 0)'),
            'status' => DB::raw('is_active'),
        ]);
    }
};
