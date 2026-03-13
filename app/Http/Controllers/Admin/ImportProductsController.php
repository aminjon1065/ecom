<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportProductsRequest;
use App\Services\ProductImportService;
use Inertia\Inertia;

class ImportProductsController extends Controller
{
    public function import(ImportProductsRequest $request, ProductImportService $service): \Inertia\Response
    {
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
