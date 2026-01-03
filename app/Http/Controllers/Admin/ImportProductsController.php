<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ImportProductsController extends Controller
{
    public function import(Request $request, ProductImportService $service): \Inertia\Response
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $result = $service->import($request->file('file'));

        return Inertia::render('admin/import-products/index', [
            'importErrors' => $result['errors'],
            'success' => empty($result['errors']),
        ]);
    }

    public function page(): \Inertia\Response
    {
        return Inertia::render('admin/import-products/index', []);
    }
}
