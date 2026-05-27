import React from 'react'
import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Price, Badge, Divider } from '@/Components/UI'
import { Order, PageProps } from '@/types'
import { ChevronLeft, MapPin, MessageCircle, Package } from 'lucide-react'
import AccountLayout from './AccountLayout'

interface Props extends PageProps {
    order: Order
}

export default function OrderShow({ order }: Props) {
    const waNumber = '254700000000'

    return (
        <AppLayout>
            <Head title={`Order ${order.order_number}`} />
            <AccountLayout active="orders">
                <Link href="/account/orders" className="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800 mb-6 transition-colors">
                    <ChevronLeft className="w-4 h-4" /> All orders
                </Link>

                <div className="flex items-start justify-between mb-6 flex-wrap gap-3">
                    <div>
                        <h2 className="font-display text-2xl">{order.order_number}</h2>
                        <p className="text-stone-500 text-sm mt-0.5">Placed on {order.created_at}</p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Badge variant={order.status === 'delivered' ? 'stock' : order.status === 'cancelled' ? 'out' : 'low-stock'} className="text-sm px-3 py-1">
                            {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </Badge>
                        {order.tracking_number && (
                            <span className="text-xs font-mono bg-stone-100 px-2.5 py-1 rounded-lg text-stone-600">
                                Tracking: {order.tracking_number}
                            </span>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {/* Items */}
                    <div className="card p-5 md:col-span-2">
                        <h3 className="font-medium text-stone-900 mb-4 flex items-center gap-2">
                            <Package className="w-4 h-4 text-stone-400" /> Items ordered
                        </h3>
                        <div className="space-y-4">
                            {order.items?.map((item, i) => (
                                <div key={i} className="flex justify-between items-center">
                                    <div>
                                        <p className="font-medium text-stone-900 text-sm">{item.product_name}</p>
                                        <p className="text-xs text-stone-500">{item.variant_name} · SKU: {item.sku} · Qty: {item.qty}</p>
                                    </div>
                                    <Price amount={item.line_total} className="text-sm font-medium" />
                                </div>
                            ))}
                        </div>

                        <Divider className="my-4" />

                        <div className="space-y-2 text-sm max-w-xs ml-auto">
                            <div className="flex justify-between text-stone-600"><span>Subtotal</span><Price amount={order.subtotal} /></div>
                            {order.discount_amount > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>Discount {order.discount_code && `(${order.discount_code})`}</span>
                                    <span>−<Price amount={order.discount_amount} /></span>
                                </div>
                            )}
                            <div className="flex justify-between text-stone-600"><span>Shipping</span><Price amount={order.shipping_amount} /></div>
                            <Divider className="my-1" />
                            <div className="flex justify-between font-semibold text-stone-900 text-base">
                                <span>Total</span><Price amount={order.total} />
                            </div>
                        </div>
                    </div>

                    {/* Shipping */}
                    {order.shipping && (
                        <div className="card p-5">
                            <h3 className="font-medium text-stone-900 mb-3 flex items-center gap-2">
                                <MapPin className="w-4 h-4 text-stone-400" /> Delivery address
                            </h3>
                            <p className="text-sm text-stone-700">{order.shipping.name}</p>
                            <p className="text-sm text-stone-500">{order.shipping.phone}</p>
                            <p className="text-sm text-stone-500 mt-1">{order.shipping.address}</p>
                        </div>
                    )}

                    {/* Payment */}
                    <div className="card p-5">
                        <h3 className="font-medium text-stone-900 mb-3">Payment</h3>
                        <div className="space-y-1.5 text-sm">
                            <div className="flex justify-between">
                                <span className="text-stone-500">Method</span>
                                <span className="font-medium text-stone-900 capitalize">{order.payment_method}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-stone-500">Status</span>
                                <Badge variant={order.payment_status === 'paid' ? 'stock' : 'default'}>
                                    {order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    {/* Help */}
                    <div className="card p-5 md:col-span-2 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p className="font-medium text-stone-900 text-sm">Need help with this order?</p>
                            <p className="text-xs text-stone-500 mt-0.5">Our team is available on WhatsApp.</p>
                        </div>
                        <a
                            href={`https://wa.me/${waNumber}?text=${encodeURIComponent(`Hi! I need help with order ${order.order_number}.`)}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn-whatsapp text-sm px-5 py-2.5"
                        >
                            <MessageCircle className="w-4 h-4" /> Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </AccountLayout>
        </AppLayout>
    )
}
