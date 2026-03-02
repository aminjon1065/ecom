<?php

namespace App\Services;

use App\Imports\ProductsImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function import(UploadedFile $file): array
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $import = new ProductsImport;
        \Log::info('DB CHECK', [
            'connection' => config('database.default'),
            'database' => \DB::connection()->getDatabaseName(),
        ]);
        Excel::import($import, $file);

        if (! empty($import->errors)) {
            \Log::warning('Products import finished with errors', [
                'error_count' => count($import->errors),
                'errors' => $import->errors,
            ]);
        }

        return [
            'success' => empty($import->errors),
            'errors' => $import->errors,
        ];
    }
}
