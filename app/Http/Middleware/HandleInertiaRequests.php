<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /* return [
            ...parent::share($request),
            //
        ]; */

        $cart = app(CartService::class);

        return array_merge(parent::share($request), [

            // Currently authenticated user (null if guest)
            'auth' => [
                'user' => $request->user() ? [
                    'id'    => $request->user()->id,
                    'name'  => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
            ],

            // Cart summary (count badge in navbar, totals in cart/checkout)
            'cart' => fn () => $cart->toArray(),

            // One-time flash messages (success / error)
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // App meta — useful for WhatsApp links, site name etc.
            'app' => [
                'name'    => config('app.name'),
                'wa_number' => config('services.whatsapp.business_number', '254700000000'),
            ],
        ]);

    }
}
