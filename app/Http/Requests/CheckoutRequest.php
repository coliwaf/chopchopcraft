<?php

namespace App\Http\Requests;

use App\Enums\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled in controller / guest checkout allowed
    }

    public function rules(): array
    {
        return [
            // Contact
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',

            // Shipping address
            'address_line1'  => 'required|string|max:255',
            'address_line2'  => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'county'         => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'country'        => 'nullable|string|size:2',

            // Payment
            'payment_method' => ['required', Rule::enum(PaymentGateway::class)],

            // Optional discount
            'discount_code'  => 'nullable|string|max:50',

            // Terms
            'terms_accepted' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
            'payment_method.required' => 'Please select a payment method.',
        ];
    }

    /**
     * Returns only the shipping-related fields as an array,
     * ready to pass into OrderService::createFromCart().
     */
    public function shippingFields(): array
    {
        return [
            'name'          => "{$this->first_name} {$this->last_name}",
            'phone'         => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city'          => $this->city,
            'county'        => $this->county,
            'postal_code'   => $this->postal_code,
            'country'       => $this->country ?? 'KE',
        ];
    }
}
