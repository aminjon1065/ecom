<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used in tests) doesn't support ADD CONSTRAINT after table creation.
        // MySQL 8.0.16+ enforces CHECK constraints as a database-level safety net.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_qty_non_negative CHECK (qty >= 0)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE products DROP CONSTRAINT chk_products_qty_non_negative');
    }
};
