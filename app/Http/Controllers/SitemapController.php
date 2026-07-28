<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $categories = Category::all();
        $products = Product::where('status', 'published')->latest()->get();

        $content = view('sitemap', compact('categories', 'products'))->render();

        return response($content, 200, [
            'Content-Type' => 'text/xml',
        ]);
    }
}
