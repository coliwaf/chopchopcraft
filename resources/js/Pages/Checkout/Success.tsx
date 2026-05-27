import React from 'react'
import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Price, Divider } from '@/Components/UI'
import { Order, PageProps } from '@/types'
import { CheckCircle2, Package, MessageCircle, ArrowRight } from 'lucide-react'

interface Props extends PageProps {
    order: Order
}

export default function CheckoutSuccess({ order }: Props) {
    const waNumber = '254700000000' // Replace with your business WhatsApp

    return (
        <AppLayout>
            <Head title={`Order ${order.order_number} confirmed`} />

            <div className="max-w-2xl mx-auto px-4 py-16">

                {/* Confirmation header */}
                <div className="text-center mb-10">
                    <div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                        <CheckCircle2 className="w-10 h-10 text-green-600" />
                    </div>
                    <h1 className="font-display text-4xl text-stone-900 mb-2">Order confirmed!</h1>
                    <p className="text-stone-500">
                        Thank you for your purchase. We've sent a confirmation to your WhatsApp.
                    </p>
                </div>

                {/* Order card */}
                <div className="card p-6 mb-6">
                    <div className="flex items-center justify-between mb-5">
                        <div>
                            <p className="text-xs text-stone-400 uppercase tracking-widest mb-1">Order number</p>
                            <p className="font-mono font-semibold text-stone-900 text-lg">{order.order_number}</p>
                        </div>
                        <span className={`badge text-sm px-3 py-1 rounded-full font-medium ${
                            order.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'
                        }`}>
                            {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </div>

                    <Divider className="mb-5" />

                    {/* Items */}
                    {order.items && (
                        <div className="space-y-3 mb-5">
                            {order.items.map((item, i) => (
                                <div key={i} className="flex justify-between items-center text-sm">
                                    <div>
                                        <span className="font-medium text-stone-900">{item.product_name}</span>
                                        <span className="text-stone-500"> · {item.variant_name}</span>
                                        <span className="text-stone-400"> × {item.qty}</span>
                                    </div>
                                    <Price amount={item.line_total} className="text-stone-900" />
                                </div>
                            ))}
                        </div>
                    )}

                    <Divider className="mb-4" />

                    {/* Totals */}
                    <div className="space-y-2 text-sm">
                        <div className="flex justify-between text-stone-600">
                            <span>Subtotal</span><Price amount={order.subtotal} />
                        </div>
                        {order.discount_amount > 0 && (
                            <div className="flex justify-between text-green-600">
                                <span>Discount {order.discount_code && `(${order.discount_code})`}</span>
                                <span>−<Price amount={order.discount_amount} /></span>
                            </div>
                        )}
                        <div className="flex justify-between text-stone-600">
                            <span>Shipping</span><Price amount={order.shipping_amount} />
                        </div>
                        <Divider className="my-2" />
                        <div className="flex justify-between font-semibold text-stone-900 text-base">
                            <span>Total</span><Price amount={order.total} />
                        </div>
                    </div>
                </div>

                {/* Delivery info */}
                {order.shipping && (
                    <div className="card p-5 mb-6 flex gap-4">
                        <Package className="w-5 h-5 text-stone-400 flex-shrink-0 mt-0.5" />
                        <div className="text-sm">
                            <p className="font-medium text-stone-900 mb-1">Delivery to</p>
                            <p className="text-stone-500">{order.shipping.name}</p>
                            <p className="text-stone-500">{order.shipping.address}</p>
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="flex flex-col sm:flex-row gap-3">
                    <Link href="/account/orders" className="flex-1">
                        <Button variant="secondary" className="w-full">
                            View all orders
                        </Button>
                    </Link>

                    <a
                        href={`https://wa.me/${waNumber}?text=${encodeURIComponent(`Hi! I have a question about my order ${order.order_number}.`)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex-1"
                    >
                        <Button variant="whatsapp" className="w-full">
                            <MessageCircle className="w-4 h-4" /> Chat on WhatsApp
                        </Button>
                    </a>
                </div>

                <div className="text-center mt-8">
                    <Link href="/products" className="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800 transition-colors">
                        Continue shopping <ArrowRight className="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </AppLayout>
    )
}
