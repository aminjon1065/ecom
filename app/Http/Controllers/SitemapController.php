<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('status', true)
            ->where('is_approved', true)
            ->select('slug', 'updated_at')
            ->get();

        $categories = Category::query()
            ->where('status', true)
            ->select('slug', 'updated_at')
            ->get();

        $xml = view('sitemap', compact('products', 'categories'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
