<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    // GET /products
    public function index(Request $request): Response
    {
        $query = Product::active()
            ->inStock()
            ->with(['variants' => fn($q) => $q->active()->orderBy('sort_order')]);
        // ->orderBy('sort_order');

        // Filter by wood type
        if ($request->filled('wood_type')) {
            $query->where('wood_type', $request->wood_type);
        }

        // Filter by size (via variants)
        if ($request->filled('size')) {
            $query->whereHas('variants', fn($q) => $q->where('size', $request->size)->where('stock_qty', '>', 0));
        }

        // Sort
        $query = match ($request->input('sort', 'featured')) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            default      => $query->orderBy('is_featured', 'desc')->orderBy('sort_order'),
        };

        $products = $query->paginate(20)->through(fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'slug'        => $p->slug,
            'description' => $p->description,
            'wood_type'   => $p->wood_type,
            'finish'      => $p->finish,
            'min_price'   => $p->min_price,
            'image'       => $p->getFirstMediaUrl('images', 'card'),
            'in_stock'    => $p->isInStock(),
            'is_featured' => $p->is_featured,
            'variants'    => $p->variants->map(fn($v) => [
                'id'    => $v->id,
                'name'  => $v->name,
                'size'  => $v->size,
                'price' => $v->effective_price,
                'stock' => $v->stock_qty,
            ]),
        ]);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'filters'    => $request->only(['wood_type', 'size', 'sort']),
            'wood_types' => Product::active()->distinct()->pluck('wood_type')->filter()->values(),
            'sizes'      => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
    }

    // GET /products/{slug}
    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'variants' => fn($q) => $q->active()->orderBy('sort_order'),
        ]);

        // Build WhatsApp order URL
        $waNumber  = ltrim(config('services.whatsapp.business_phone', ''), '+');
        $waMessage = urlencode("Hi! I'd like to order a *{$product->name}*. Could you help me with availability and delivery?");
        $waUrl     = "https://wa.me/{$waNumber}?text={$waMessage}";

        return Inertia::render('Products/Show', [
            'product' => [
                'id'                 => $product->id,
                'name'               => $product->name,
                'slug'               => $product->slug,
                'description'        => $product->description,
                'long_description'   => $product->long_description,
                'wood_type'          => $product->wood_type,
                'finish'             => $product->finish,
                'care_instructions'  => $product->care_instructions ?? [],
                'dimensions'         => $product->dimensions ?? [],
                'images'             => $product->getMedia('images')->map(fn($m) => [
                    // 'url'   => $m->hasGeneratedConversion('card')  ? $m->getUrl('card')  : $m->getUrl(),
                    'url'   => $m->getUrl(),
                    'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
                    'alt'   => $product->name,
                ])->filter(fn($img) => $img['url'] !== '')->values(),
                'variants' => $product->variants->map(fn($v) => [
                    'id'        => $v->id,
                    'name'      => $v->name,
                    'sku'       => $v->sku,
                    'size'      => $v->size,
                    'price'     => $v->effective_price,
                    'stock_qty' => $v->stock_qty,
                    'in_stock'  => $v->isInStock(),
                    'low_stock' => $v->isLowStock(),
                ]),
            ],
            'whatsapp_url'    => $waUrl,
            'related_products' => Product::active()
                ->inStock()
                ->where('id', '!=', $product->id)
                ->where('wood_type', $product->wood_type)
                ->with(['variants' => fn($q) => $q->active()->orderBy('sort_order')])
                ->orderBy('is_featured', 'desc')
                ->limit(4)
                ->get()
                ->map(fn($p) => [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'slug'        => $p->slug,
                    'description' => $p->description,
                    'wood_type'   => $p->wood_type,
                    'min_price'   => $p->min_price,
                    'image'       => $p->hasGeneratedConversion('card')
                        ? $p->getFirstMediaUrl('images', 'card')
                        : $p->getFirstMediaUrl('images'),
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
                ]),
        ]);
    }
}
