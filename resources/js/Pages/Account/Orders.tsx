import React from 'react'
import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Price, Badge } from '@/Components/UI'
import { Order, PageProps, PaginatedData } from '@/types'
import { Package, ChevronRight, ShoppingBag } from 'lucide-react'
import AccountLayout from './AccountLayout'

interface Props extends PageProps {
    orders: PaginatedData<Order>
}

const statusVariant = (s: string): 'stock' | 'low-stock' | 'out' | 'default' => {
    if (s === 'delivered' || s === 'confirmed') return 'stock'
    if (s === 'shipped'   || s === 'processing') return 'low-stock'
    if (s === 'cancelled' || s === 'refunded')   return 'out'
    return 'default'
}

export default function Orders({ orders, auth, flash }: Props) {
    return (
        <AppLayout>
            <Head title="My Orders" />
            <AccountLayout active="orders">
                <h2 className="font-display text-2xl mb-6">Order history</h2>

                {orders.data.length === 0 ? (
                    <div className="card p-16 text-center">
                        <ShoppingBag className="w-10 h-10 text-stone-300 mx-auto mb-4" />
                        <p className="text-stone-500 mb-4">You haven't placed any orders yet.</p>
                        <Link href="/products" className="text-amber-800 hover:text-stone-900 font-medium text-sm">
                            Browse our boards →
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {orders.data.map(order => (
                            <Link
                                key={order.id}
                                href={`/account/orders/${order.id}`}
                                className="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow group"
                            >
                                <div className="w-10 h-10 bg-stone-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <Package className="w-5 h-5 text-stone-500" />
                                </div>

                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <span className="font-mono font-semibold text-stone-900 text-sm">{order.order_number}</span>
                                        <Badge variant={statusVariant(order.status)}>
                                            {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                                        </Badge>
                                    </div>
                                    <p className="text-xs text-stone-400 mt-0.5">
                                        {order.created_at} · {order.item_count} {order.item_count === 1 ? 'item' : 'items'}
                                    </p>
                                </div>

                                <Price amount={order.total} className="font-semibold text-stone-900 flex-shrink-0" />

                                <ChevronRight className="w-4 h-4 text-stone-400 group-hover:text-stone-600 transition-colors flex-shrink-0" />
                            </Link>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {orders.last_page > 1 && (
                    <div className="flex justify-center gap-2 mt-8">
                        {orders.links.map((link, i) => (
                            link.url && (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`px-4 py-2 rounded-xl text-sm font-medium transition-colors ${
                                        link.active
                                            ? 'bg-stone-900 text-white'
                                            : 'border border-stone-200 text-stone-600 hover:border-stone-400'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                )}
            </AccountLayout>
        </AppLayout>
    )
}
