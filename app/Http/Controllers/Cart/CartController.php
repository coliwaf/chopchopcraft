<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    // GET /cart
    public function index(): Response
    {
        return Inertia::render('Cart/Index', [
            'cart' => $this->cart->toArray(),
        ]);
    }

    // POST /cart
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'qty'        => 'sometimes|integer|min:1|max:20',
        ]);

        try {
            $this->cart->add(
                variantId: $request->integer('variant_id'),
                qty:       $request->integer('qty', 1),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Added to cart.',
            'cart'    => $this->cart->toArray(),
        ]);
    }

    // PATCH /cart/{variantId}
    public function update(Request $request, int $variantId): JsonResponse
    {
        $request->validate(['qty' => 'required|integer|min:0|max:20']);

        try {
            $this->cart->update($variantId, $request->integer('qty'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Cart updated.',
            'cart'    => $this->cart->toArray(),
        ]);
    }

    // DELETE /cart/{variantId}
    public function destroy(int $variantId): JsonResponse
    {
        $this->cart->remove($variantId);

        return response()->json([
            'message' => 'Item removed.',
            'cart'    => $this->cart->toArray(),
        ]);
    }

    // DELETE /cart
    public function clear(): JsonResponse
    {
        $this->cart->clear();

        return response()->json(['message' => 'Cart cleared.', 'cart' => $this->cart->toArray()]);
    }
}
