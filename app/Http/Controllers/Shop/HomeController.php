<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featured = Product::active()
            ->featured()
            ->inStock()
            ->with(['variants' => fn($q) => $q->active()->orderBy('sort_order')])
            ->withCount('variants')
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'slug'        => $p->slug,
                'description' => $p->description,
                'wood_type'   => $p->wood_type,
                'min_price'   => $p->min_price,
                'image'       => $p->getFirstMediaUrl('images', 'card'),
                'in_stock'    => $p->isInStock(),
                'is_featured' => $p->is_featured,
                'variants'    => $p->variants->map(fn($v) => [
                    'id'        => $v->id,
                    'name'      => $v->name,
                    'size'      => $v->size,
                    'price'     => $v->effective_price,
                    'stock_qty' => $v->stock_qty,
                    'in_stock'  => $v->isInStock(),
                    'low_stock' => $v->isLowStock(),
                ]),
            ]);

        return Inertia::render('Home', compact('featured'));
    }
}
