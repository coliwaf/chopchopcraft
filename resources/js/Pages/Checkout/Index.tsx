import React, { useState, useEffect } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Input, Price, Divider, Flash } from '@/Components/UI'
import { CartItem, PageProps } from '@/types'
import { ChevronLeft, Tag, CheckCircle2, Smartphone, CreditCard, Globe } from 'lucide-react'

interface CheckoutProps extends PageProps {
    stripePublicKey: string
    prefill: {
        name?: string
        email?: string
        phone?: string
        address_line1?: string
        city?: string
        county?: string
    }
}

type PaymentMethod = 'mpesa' | 'stripe' | 'paypal'

type FormData = {
    first_name: string
    last_name: string
    email: string
    phone: string
    address_line1: string
    address_line2: string
    city: string
    county: string
    postal_code: string
    discount_code: string
    payment_method: PaymentMethod
    terms_accepted: boolean
}

type Errors = Partial<Record<keyof FormData, string>>

export default function CheckoutIndex({ cart, auth, flash, stripePublicKey, prefill }: CheckoutProps) {
    const [form, setForm] = useState<FormData>({
        first_name:     prefill.name?.split(' ')[0] ?? '',
        last_name:      prefill.name?.split(' ').slice(1).join(' ') ?? '',
        email:          prefill.email ?? '',
        phone:          prefill.phone ?? '',
        address_line1:  prefill.address_line1 ?? '',
        address_line2:  '',
        city:           prefill.city ?? '',
        county:         prefill.county ?? '',
        postal_code:    '',
        discount_code:  '',
        payment_method: 'mpesa',
        terms_accepted: false,
    })

    const [errors, setErrors]               = useState<Errors>({})
    const [loading, setLoading]             = useState(false)
    const [discountLoading, setDiscountLoading] = useState(false)
    const [discount, setDiscount]           = useState<{ code: string; discount_amount: number; new_total: number } | null>(null)
    const [discountError, setDiscountError] = useState('')
    const [mpesaPolling, setMpesaPolling]   = useState(false)
    const [orderNumber, setOrderNumber]     = useState('')

    function set(field: keyof FormData, value: string | boolean) {
        setForm(f => ({ ...f, [field]: value }))
        if (errors[field]) setErrors(e => ({ ...e, [field]: undefined }))
    }

    // ─── Discount code ────────────────────────────────────────────────────────
    async function applyDiscount() {
        if (!form.discount_code.trim()) return
        setDiscountLoading(true)
        setDiscountError('')
        try {
            const res = await fetch('/checkout/validate-discount', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body: JSON.stringify({ code: form.discount_code, email: form.email }),
            })
            const data = await res.json()
            if (!res.ok) { setDiscountError(data.message); return }
            setDiscount(data)
        } finally {
            setDiscountLoading(false)
        }
    }

    // ─── Submit ───────────────────────────────────────────────────────────────
    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault()
        setLoading(true)
        setErrors({})

        const payload = { ...form, discount_code: discount?.code ?? '' }

        const endpoints: Record<PaymentMethod, string> = {
            mpesa:  '/checkout/mpesa',
            stripe: '/checkout/stripe/intent',
            paypal: '/checkout/paypal',
        }

        try {
            const res = await fetch(endpoints[form.payment_method], {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body: JSON.stringify(payload),
            })
            const data = await res.json()

            if (!res.ok) {
                if (data.errors) setErrors(data.errors)
                else setErrors({ first_name: data.message })
                setLoading(false)
                return
            }

            if (form.payment_method === 'mpesa') {
                setOrderNumber(data.order_number)
                setMpesaPolling(true)
                pollMpesaStatus(data.order_number)
            } else if (form.payment_method === 'stripe') {
                await handleStripe(data.client_secret, data.order_number)
            } else if (form.payment_method === 'paypal') {
                window.location.href = data.approve_url
            }
        } catch {
            setErrors({ first_name: 'Something went wrong. Please try again.' })
            setLoading(false)
        }
    }

    // ─── Stripe ───────────────────────────────────────────────────────────────
    async function handleStripe(clientSecret: string, orderNum: string) {
        const stripe = (window as any).Stripe?.(stripePublicKey)
        if (!stripe) {
            setErrors({ first_name: 'Stripe failed to load. Please refresh and try again.' })
            setLoading(false)
            return
        }
        const { error } = await stripe.confirmPayment({
            clientSecret,
            confirmParams: { return_url: `${window.location.origin}/checkout/success/${orderNum}` },
        })
        if (error) {
            setErrors({ first_name: error.message })
            setLoading(false)
        }
    }

    // ─── M-Pesa polling ───────────────────────────────────────────────────────
    function pollMpesaStatus(orderNum: string) {
        let attempts = 0
        const max = 24 // 2 minutes at 5s intervals
        const interval = setInterval(async () => {
            attempts++
            try {
                const res = await fetch(`/checkout/mpesa/status?order_number=${orderNum}`)
                const data = await res.json()
                if (data.paid) {
                    clearInterval(interval)
                    router.visit(`/checkout/success/${orderNum}`)
                }
            } catch {}
            if (attempts >= max) {
                clearInterval(interval)
                setMpesaPolling(false)
                setLoading(false)
                setErrors({ first_name: 'Payment timeout — please check your M-Pesa messages and contact us if charged.' })
            }
        }, 5000)
    }

    const total = discount ? discount.new_total : cart.subtotal

    // ─── Render ───────────────────────────────────────────────────────────────
    if (mpesaPolling) {
        return (
            <AppLayout>
                <Head title="Waiting for M-Pesa payment" />
                <div className="max-w-md mx-auto px-4 py-24 text-center">
                    <div className="card p-10">
                        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <Smartphone className="w-8 h-8 text-green-600 animate-pulse" />
                        </div>
                        <h2 className="font-display text-2xl mb-3">Check your phone</h2>
                        <p className="text-stone-500 mb-2">
                            An M-Pesa STK push has been sent to <strong>{form.phone}</strong>.
                        </p>
                        <p className="text-stone-500 mb-8 text-sm">Enter your PIN to complete the payment. This page will update automatically.</p>
                        <div className="flex items-center justify-center gap-2 text-sm text-stone-400">
                            <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Waiting for confirmation…
                        </div>
                    </div>
                </div>
            </AppLayout>
        )
    }

    return (
        <AppLayout>
            <Head title="Checkout" />
            {/* Stripe.js — only loaded on checkout */}
            <script src="https://js.stripe.com/v3/" async />

            <div className="max-w-5xl mx-auto px-4 py-12">
                <div className="mb-8">
                    <Link href="/cart" className="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800 mb-4 transition-colors">
                        <ChevronLeft className="w-4 h-4" /> Back to cart
                    </Link>
                    <h1 className="font-display text-4xl text-stone-900">Checkout</h1>
                </div>

                {errors.first_name && typeof errors.first_name === 'string' && !errors.last_name && (
                    <Flash message={errors.first_name} type="error" className="mb-6" />
                )}

                <form onSubmit={handleSubmit}>
                    <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">

                        {/* ── Left column (3/5) ── */}
                        <div className="lg:col-span-3 space-y-6">

                            {/* Contact */}
                            <div className="card p-6">
                                <h2 className="font-display text-xl mb-5">Contact information</h2>
                                <div className="grid grid-cols-2 gap-4">
                                    <Input label="First name" value={form.first_name} onChange={e => set('first_name', e.target.value)} error={errors.first_name} required />
                                    <Input label="Last name"  value={form.last_name}  onChange={e => set('last_name', e.target.value)}  error={errors.last_name}  required />
                                    <Input label="Email" type="email" value={form.email} onChange={e => set('email', e.target.value)} error={errors.email} className="col-span-2" required />
                                    <Input label="Phone (M-Pesa)" type="tel" placeholder="+254712345678" value={form.phone} onChange={e => set('phone', e.target.value)} error={errors.phone} className="col-span-2" required />
                                </div>
                            </div>

                            {/* Shipping */}
                            <div className="card p-6">
                                <h2 className="font-display text-xl mb-5">Delivery address</h2>
                                <div className="grid grid-cols-2 gap-4">
                                    <Input label="Address" value={form.address_line1} onChange={e => set('address_line1', e.target.value)} error={errors.address_line1} className="col-span-2" required />
                                    <Input label="Apartment, floor (optional)" value={form.address_line2} onChange={e => set('address_line2', e.target.value)} className="col-span-2" />
                                    <Input label="City / Town" value={form.city} onChange={e => set('city', e.target.value)} error={errors.city} required />
                                    <Input label="County" value={form.county} onChange={e => set('county', e.target.value)} error={errors.county} />
                                    <Input label="Postal code" value={form.postal_code} onChange={e => set('postal_code', e.target.value)} />
                                </div>
                            </div>

                            {/* Payment method */}
                            <div className="card p-6">
                                <h2 className="font-display text-xl mb-5">Payment method</h2>
                                <div className="space-y-3">
                                    {([
                                        { id: 'mpesa',  label: 'M-Pesa',  sub: 'Pay via STK push to your phone', Icon: Smartphone, color: 'text-green-600' },
                                        { id: 'stripe', label: 'Card (Stripe)', sub: 'Visa, Mastercard, Amex', Icon: CreditCard, color: 'text-blue-600' },
                                        { id: 'paypal', label: 'PayPal', sub: 'Pay with your PayPal balance or card', Icon: Globe, color: 'text-indigo-600' },
                                    ] as const).map(({ id, label, sub, Icon, color }) => (
                                        <label key={id} className={`flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all duration-150 ${form.payment_method === id ? 'border-amber-700 bg-amber-50' : 'border-stone-200 hover:border-stone-300'}`}>
                                            <input type="radio" name="payment_method" value={id} checked={form.payment_method === id} onChange={() => set('payment_method', id)} className="sr-only" />
                                            <Icon className={`w-5 h-5 ${color} flex-shrink-0`} />
                                            <div className="flex-1">
                                                <div className="font-medium text-stone-900 text-sm">{label}</div>
                                                <div className="text-xs text-stone-500">{sub}</div>
                                            </div>
                                            {form.payment_method === id && <CheckCircle2 className="w-5 h-5 text-amber-700" />}
                                        </label>
                                    ))}
                                </div>
                            </div>

                            {/* Terms */}
                            <label className="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" checked={form.terms_accepted} onChange={e => set('terms_accepted', e.target.checked)} className="mt-0.5 accent-amber-700" required />
                                <span className="text-sm text-stone-600">
                                    I agree to the <a href="/terms" className="underline hover:text-stone-900">terms and conditions</a> and <a href="/privacy" className="underline hover:text-stone-900">privacy policy</a>.
                                </span>
                            </label>
                        </div>

                        {/* ── Right column (2/5) — sticky summary ── */}
                        <div className="lg:col-span-2">
                            <div className="card p-6 sticky top-6 space-y-5">
                                <h2 className="font-display text-xl">Order summary</h2>

                                {/* Items */}
                                <div className="space-y-3 max-h-48 overflow-y-auto pr-1">
                                    {cart.items.map((item: CartItem) => (
                                        <div key={item.variant_id} className="flex items-center gap-3">
                                            <div className="relative w-12 h-12 rounded-lg overflow-hidden bg-stone-100 flex-shrink-0">
                                                {item.image
                                                    ? <img src={item.image} alt={item.product_name} className="w-full h-full object-cover" />
                                                    : <span className="absolute inset-0 flex items-center justify-center text-xl">🪵</span>
                                                }
                                                <span className="absolute -top-1 -right-1 w-5 h-5 bg-stone-700 text-white text-xs rounded-full flex items-center justify-center">{item.qty}</span>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-stone-900 truncate">{item.product_name}</p>
                                                <p className="text-xs text-stone-500">{item.variant_name}</p>
                                            </div>
                                            <Price amount={item.price * item.qty} className="text-sm font-medium flex-shrink-0" />
                                        </div>
                                    ))}
                                </div>

                                <Divider />

                                {/* Discount code */}
                                <div>
                                    <p className="text-sm font-medium text-stone-700 mb-2">Discount code</p>
                                    <div className="flex gap-2">
                                        <input
                                            value={form.discount_code}
                                            onChange={e => { set('discount_code', e.target.value.toUpperCase()); setDiscount(null); setDiscountError('') }}
                                            placeholder="e.g. WELCOME10"
                                            className="input flex-1 text-sm py-2"
                                            disabled={!!discount}
                                        />
                                        <Button variant="secondary" size="sm" type="button" loading={discountLoading} onClick={applyDiscount} disabled={!!discount || !form.discount_code}>
                                            <Tag className="w-3.5 h-3.5" /> Apply
                                        </Button>
                                    </div>
                                    {discountError && <p className="mt-1.5 text-xs text-red-500">{discountError}</p>}
                                    {discount && <p className="mt-1.5 text-xs text-green-600">✓ Code applied — saving <Price amount={discount.discount_amount} /></p>}
                                </div>

                                <Divider />

                                {/* Totals */}
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between text-stone-600">
                                        <span>Subtotal</span><Price amount={cart.subtotal} />
                                    </div>
                                    {discount && (
                                        <div className="flex justify-between text-green-600">
                                            <span>Discount ({discount.code})</span>
                                            <span>−<Price amount={discount.discount_amount} /></span>
                                        </div>
                                    )}
                                    <div className="flex justify-between text-stone-400">
                                        <span>Shipping</span><span>KES 200 – 400</span>
                                    </div>
                                </div>

                                <Divider />

                                <div className="flex justify-between font-semibold text-stone-900">
                                    <span>Total</span>
                                    <Price amount={total} className="text-xl" />
                                </div>

                                <Button type="submit" size="lg" className="w-full" loading={loading}>
                                    {loading ? 'Processing…' : `Pay ${form.payment_method === 'mpesa' ? 'via M-Pesa' : form.payment_method === 'stripe' ? 'with Card' : 'with PayPal'}`}
                                </Button>

                                <p className="text-xs text-stone-400 text-center">
                                    🔒 Your payment is secured by{' '}
                                    {form.payment_method === 'mpesa' ? 'Safaricom Daraja' :
                                     form.payment_method === 'stripe' ? 'Stripe' : 'PayPal'}
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    )
}

function getCsrf(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? ''
}
