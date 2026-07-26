<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Webhooks\MpesaWebhookController;
use App\Http\Controllers\Webhooks\PayPalWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome');
}); 

*/

// ─── Shop ─────────────────────────────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
});

// ─── Cart ─────────────────────────────────────────────────────────────────────
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{variantId}', [CartController::class, 'update'])->name('update');
    Route::delete('/{variantId}', [CartController::class, 'destroy'])->name('destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// ─── Checkout ─────────────────────────────────────────────────────────────────
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('validate-discount', [CheckoutController::class, 'validateDiscount'])->name('validate-discount');
    Route::post('mpesa', [CheckoutController::class, 'initiateMpesa'])->name('mpesa');
    Route::get('mpesa/status', [CheckoutController::class, 'mpesaStatus'])->name('mpesa.status');
    Route::post('stripe/intent', [CheckoutController::class, 'stripeIntent'])->name('stripe.intent');
    Route::post('paypal', [CheckoutController::class, 'initiatePaypal'])->name('paypal');
    Route::get('paypal/capture/{order}', [CheckoutController::class, 'capturePaypal'])->name('paypal.capture');
    Route::get('paypal/cancel/{order}', [CheckoutController::class, 'cancelPaypal'])->name('paypal.cancel');
    Route::get('success/{orderNumber', [CheckoutController::class, 'success'])->name('success');
});

// ─── Auth (guests only) ───────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('login',   [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Account (auth required) ──────────────────────────────────────────────────
Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('orders',         [AccountController::class, 'orders'])->name('orders');
    Route::get('orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
    Route::get('profile',        [AccountController::class, 'profile'])->name('profile');
    Route::put('profile',        [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('password',       [AccountController::class, 'updatePassword'])->name('password.update');
});

// ─── Webhooks — no CSRF, no session ────────────────────────────────────────
// These are called by Safaricom / Stripe / PayPal servers, not browsers.
// They must NOT use web middleware (which enforces CSRF).
// They are defined separately in routes/api.php or excluded below.
Route::prefix('webhooks')
    ->withoutMiddleware([PreventRequestForgery::class])
    // ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::post('mpesa',  [MpesaWebhookController::class,  'handle'])->name('webhooks.mpesa');
        Route::post('stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
        Route::post('paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');
    });
