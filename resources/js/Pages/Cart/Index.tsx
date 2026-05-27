import React, { useState } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Price, Divider } from '@/Components/UI'
import { CartItem, PageProps } from '@/types'
import { ShoppingBag, Trash2, Plus, Minus, ArrowRight, ChevronLeft } from 'lucide-react'

interface Props extends PageProps {}

export default function CartIndex({ cart, auth, flash }: Props) {
    const [updating, setUpdating] = useState<number | null>(null)
    const [removing, setRemoving] = useState<number | null>(null)

    function updateQty(variantId: number, qty: number) {
        setUpdating(variantId)
        router.patch(`/cart/${variantId}`, { qty }, {
            preserveScroll: true,
            onFinish: () => setUpdating(null),
        })
    }

    function removeItem(variantId: number) {
        setRemoving(variantId)
        router.delete(`/cart/${variantId}`, {
            preserveScroll: true,
            onFinish: () => setRemoving(null),
        })
    }

    const isEmpty = cart.items.length === 0

    return (
        <AppLayout>
            <Head title="Your Cart" />

            <div className="max-w-4xl mx-auto px-4 py-12">
                {/* Header */}
                <div className="mb-8">
                    <Link href="/products" className="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800 mb-4 transition-colors">
                        <ChevronLeft className="w-4 h-4" /> Continue shopping
                    </Link>
                    <h1 className="font-display text-4xl text-stone-900">Your Cart</h1>
                    {!isEmpty && (
                        <p className="text-stone-500 mt-1">{cart.count} {cart.count === 1 ? 'item' : 'items'}</p>
                    )}
                </div>

                {isEmpty ? (
                    /* Empty state */
                    <div className="text-center py-24 card">
                        <ShoppingBag className="w-12 h-12 text-stone-300 mx-auto mb-4" />
                        <h2 className="font-display text-2xl text-stone-700 mb-2">Your cart is empty</h2>
                        <p className="text-stone-500 mb-8">Discover our handcrafted boards and add some to your cart.</p>
                        <Link href="/products">
                            <Button>Shop now</Button>
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Items list */}
                        <div className="lg:col-span-2 space-y-4">
                            {cart.items.map((item: CartItem) => (
                                <div key={item.variant_id} className="card p-5 flex gap-4">
                                    {/* Image */}
                                    <div className="w-20 h-20 rounded-xl overflow-hidden bg-stone-100 flex-shrink-0">
                                        {item.image
                                            ? <img src={item.image} alt={item.product_name} className="w-full h-full object-cover" />
                                            : <div className="w-full h-full flex items-center justify-center text-stone-300 text-2xl">🪵</div>
                                        }
                                    </div>

                                    {/* Details */}
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-medium text-stone-900 truncate">{item.product_name}</h3>
                                        <p className="text-sm text-stone-500 mb-3">{item.variant_name}</p>

                                        <div className="flex items-center justify-between gap-4">
                                            {/* Qty stepper */}
                                            <div className="flex items-center gap-2 border border-stone-200 rounded-xl overflow-hidden">
                                                <button
                                                    onClick={() => updateQty(item.variant_id, item.qty - 1)}
                                                    disabled={updating === item.variant_id || item.qty <= 1}
                                                    className="p-2 hover:bg-stone-50 transition-colors disabled:opacity-40"
                                                >
                                                    <Minus className="w-3.5 h-3.5" />
                                                </button>
                                                <span className="w-8 text-center text-sm font-medium">
                                                    {updating === item.variant_id ? '…' : item.qty}
                                                </span>
                                                <button
                                                    onClick={() => updateQty(item.variant_id, item.qty + 1)}
                                                    disabled={updating === item.variant_id}
                                                    className="p-2 hover:bg-stone-50 transition-colors disabled:opacity-40"
                                                >
                                                    <Plus className="w-3.5 h-3.5" />
                                                </button>
                                            </div>

                                            {/* Price */}
                                            <Price amount={item.price * item.qty} className="font-semibold text-stone-900" />

                                            {/* Remove */}
                                            <button
                                                onClick={() => removeItem(item.variant_id)}
                                                disabled={removing === item.variant_id}
                                                className="p-1.5 text-stone-400 hover:text-red-500 transition-colors"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Order summary */}
                        <div className="lg:col-span-1">
                            <div className="card p-6 sticky top-6">
                                <h2 className="font-display text-xl mb-5">Order summary</h2>

                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between text-stone-600">
                                        <span>Subtotal ({cart.count} items)</span>
                                        <Price amount={cart.subtotal} />
                                    </div>
                                    <div className="flex justify-between text-stone-400">
                                        <span>Shipping</span>
                                        <span>Calculated at checkout</span>
                                    </div>
                                </div>

                                <Divider className="my-5" />

                                <div className="flex justify-between font-semibold text-stone-900 mb-6">
                                    <span>Estimated total</span>
                                    <Price amount={cart.subtotal} className="text-lg" />
                                </div>

                                <Link href="/checkout" className="block">
                                    <Button className="w-full" size="lg">
                                        Proceed to checkout <ArrowRight className="w-4 h-4" />
                                    </Button>
                                </Link>

                                <p className="text-xs text-stone-400 text-center mt-4">
                                    Secure checkout · M-Pesa, Stripe & PayPal accepted
                                </p>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    )
}
