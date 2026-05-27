<?php

namespace App\Http\Controllers\Account;

// use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET /account/orders
    public function orders(Request $request): Response
    {
        $customer = $request->user()->customer;

        abort_unless($customer, 404, 'No customer profile found.');

        $orders = Order::where('customer_id', $customer->id)
            ->with(['items'])
            ->latest()
            ->paginate(10)
            ->through(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'status'         => $o->status,
                'payment_status' => $o->payment_status,
                'total'          => $o->total,
                'item_count'     => $o->items->sum('qty'),
                'created_at'     => $o->created_at->format('d M Y'),
            ]);

        return Inertia::render('Account/Orders', compact('orders'));
    }

    // GET /account/orders/{order}
    public function showOrder(Request $request, Order $order): Response
    {
        // Ensure order belongs to the logged-in customer
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);

        $order->load(['items', 'payments', 'discountCode']);

        return Inertia::render('Account/OrderShow', [
            'order' => [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'payment_status'  => $order->payment_status,
                'payment_method'  => $order->payment_method,
                'subtotal'        => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'shipping_amount' => $order->shipping_amount,
                'total'           => $order->total,
                'tracking_number' => $order->tracking_number,
                'created_at'      => $order->created_at->format('d M Y, H:i'),
                'shipping'        => [
                    'name'    => $order->shipping_name,
                    'phone'   => $order->shipping_phone,
                    'address' => implode(', ', array_filter([
                        $order->shipping_address_line1,
                        $order->shipping_address_line2,
                        $order->shipping_city,
                        $order->shipping_county,
                    ])),
                ],
                'items' => $order->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'variant_name' => $i->variant_name,
                    'sku'          => $i->sku,
                    'qty'          => $i->qty,
                    'unit_price'   => $i->unit_price,
                    'line_total'   => $i->line_total,
                ]),
                'discount_code' => $order->discountCode?->code,
            ],
        ]);
    }

    // GET /account/profile
    public function profile(Request $request): Response
    {
        $user     = $request->user();
        $customer = $user->customer;

        return Inertia::render('Account/Profile', [
            'profile' => [
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $customer?->phone,
                'address_line1' => $customer?->address_line1,
                'address_line2' => $customer?->address_line2,
                'city'          => $customer?->city,
                'county'        => $customer?->county,
                'postal_code'   => $customer?->postal_code,
            ],
        ]);
    }

    // PUT /account/profile
    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'county'        => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $user->update(['name' => "{$data['first_name']} {$data['last_name']}"]);

        $user->customer?->update([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'         => $data['phone'],
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'],
            'city'          => $data['city'],
            'county'        => $data['county'],
            'postal_code'   => $data['postal_code'],
        ]);

        return back()->with('success', 'Profile updated.');
    }

    // PUT /account/password
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password updated.');
    }
}
