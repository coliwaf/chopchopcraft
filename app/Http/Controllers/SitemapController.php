<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::active()
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('sitemap', compact('products'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600', // cache 1 hour
        ]);
    }
}
