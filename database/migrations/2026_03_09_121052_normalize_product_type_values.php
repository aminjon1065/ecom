<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'Топ' => 'top',
        'Рекомендуемый' => 'recommended',
        'Новый' => 'new',
        'Лучший' => 'best',
    ];

    public function up(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('products')
                ->where('product_type', $old)
                ->update(['product_type' => $new]);
        }
    }

    public function down(): void
    {
        foreach (array_flip($this->map) as $ascii => $cyrillic) {
            DB::table('products')
                ->where('product_type', $ascii)
                ->update(['product_type' => $cyrillic]);
        }
    }
};
