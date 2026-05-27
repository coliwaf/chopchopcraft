<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Enums\PaymentGateway;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\Payment\MpesaService;
use App\Services\Payment\StripeService;
use App\Services\Payment\PayPalService;
use App\Jobs\SendWhatsAppOrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService    $cart,
        private OrderService   $orderService,
        private MpesaService   $mpesa,
        private StripeService  $stripe,
        private PayPalService  $paypal,
    ) {}

    // ─── GET /checkout ────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        if ($this->cart->isEmpty()) {
            return Inertia::location(route('cart.index'));
        }

        $user = $request->user();

        return Inertia::render('Checkout/Index', [
            'cart'            => $this->cart->toArray(),
            'stripePublicKey' => config('services.stripe.key'),
            'prefill'         => $user ? [
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->customer?->phone,
                'address_line1'=> $user->customer?->address_line1,
                'city'         => $user->customer?->city,
                'county'       => $user->customer?->county,
            ] : [],
        ]);
    }

    // ─── POST /checkout/validate-discount ────────────────────────────────────
    public function validateDiscount(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $code = DiscountCode::where('code', strtoupper($request->code))->valid()->first();

        if (! $code) {
            return response()->json(['message' => 'Invalid or expired discount code.'], 422);
        }

        $customer = $this->resolveCustomer($request);

        if ($customer) {
            $error = $code->validateForCustomer($customer, $this->cart->subtotal());
            if ($error) {
                return response()->json(['message' => $error], 422);
            }
        }

        $discount = $code->calculateDiscount($this->cart->subtotal());

        return response()->json([
            'code'            => $code->code,
            'type'            => $code->type,
            'value'           => $code->value,
            'discount_amount' => $discount,
            'new_total'       => max(0, $this->cart->subtotal() - $discount),
        ]);
    }

    // ─── POST /checkout/mpesa ─────────────────────────────────────────────────
    public function initiateMpesa(CheckoutRequest $request): JsonResponse
    {
        $order = $this->createOrder($request, PaymentGateway::Mpesa);

        try {
            $result = $this->mpesa->stkPush($order, $request->phone);

            return response()->json([
                'order_number'       => $order->order_number,
                'checkout_request_id'=> $result['CheckoutRequestID'],
                'message'            => 'Check your phone for the M-Pesa prompt.',
            ]);
        } catch (\Exception $e) {
            // Roll back the order if STK fails immediately
            $order->update(['status' => 'cancelled']);
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── POST /checkout/stripe/intent ─────────────────────────────────────────
    public function stripeIntent(CheckoutRequest $request): JsonResponse
    {
        $order  = $this->createOrder($request, PaymentGateway::Stripe);
        $result = $this->stripe->createPaymentIntent($order);

        return response()->json([
            'order_number'  => $order->order_number,
            'client_secret' => $result['client_secret'],
        ]);
    }

    // ─── POST /checkout/paypal ────────────────────────────────────────────────
    public function initiatePayPal(CheckoutRequest $request): JsonResponse
    {
        $order  = $this->createOrder($request, PaymentGateway::PayPal);
        $result = $this->paypal->createOrder($order);

        return response()->json([
            'order_number' => $order->order_number,
            'approve_url'  => $result['approve_url'],
        ]);
    }

    // ─── GET /checkout/paypal/capture/{order} ─────────────────────────────────
    public function capturePayPal(Request $request, Order $order)
    {
        $paypalOrderId = $request->query('token'); // PayPal appends ?token=...
        $this->paypal->captureOrder($paypalOrderId);

        $this->cart->clear();
        SendWhatsAppOrderConfirmation::dispatch($order)->delay(now()->addSeconds(5));

        return Inertia::location(route('checkout.success', $order->order_number));
    }

    // ─── GET /checkout/paypal/cancel/{order} ──────────────────────────────────
    public function cancelPayPal(Order $order)
    {
        $order->update(['status' => 'cancelled']);
        return Inertia::location(route('checkout.index') . '?paypal_cancelled=1');
    }

    // ─── GET /checkout/success/{orderNumber} ──────────────────────────────────
    public function success(string $orderNumber): Response
    {
        $order = Order::where('order_number', $orderNumber)
                      ->with(['items', 'customer'])
                      ->firstOrFail();

        return Inertia::render('Checkout/Success', compact('order'));
    }

    // ─── M-Pesa polling endpoint (client polls after STK push) ───────────────
    public function mpesaStatus(Request $request): JsonResponse
    {
        $request->validate(['order_number' => 'required|string']);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        return response()->json([
            'payment_status' => $order->payment_status,
            'order_status'   => $order->status,
            'paid'           => $order->isPaid(),
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────
    private function createOrder(CheckoutRequest $request, PaymentGateway $gateway): Order
    {
        $customer = $this->resolveOrCreateCustomer($request);
        $discount = $request->discount_code
            ? DiscountCode::where('code', $request->discount_code)->valid()->first()
            : null;

        $order = $this->orderService->createFromCart(
            customer: $customer,
            shipping: $request->shippingFields(),
            gateway:  $gateway,
            discount: $discount,
        );

        // Clear cart after order creation
        $this->cart->clear();

        return $order;
    }

    private function resolveOrCreateCustomer(CheckoutRequest $request): Customer
    {
        $user = $request->user();

        if ($user && $user->customer) {
            // Sync latest shipping info
            $user->customer->update([
                'phone'         => $request->phone,
                'address_line1' => $request->address_line1,
                'city'          => $request->city,
                'county'        => $request->county,
            ]);
            return $user->customer;
        }

        return Customer::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'phone'         => $request->phone,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city'          => $request->city,
                'county'        => $request->county,
                'postal_code'   => $request->postal_code,
                'source'        => 'website',
                'user_id'       => $user?->id,
            ]
        );
    }

    private function resolveCustomer(Request $request): ?Customer
    {
        return $request->user()?->customer
            ?? Customer::where('email', $request->email)->first();
    }
}
