import React from 'react'
import { Link, router } from '@inertiajs/react'
import { clsx } from 'clsx'
import { ShoppingBag, User, LogOut } from 'lucide-react'

interface Props {
    children: React.ReactNode
    active: 'orders' | 'profile'
}

const nav = [
    { id: 'orders',  label: 'My orders',  href: '/account/orders',  Icon: ShoppingBag },
    { id: 'profile', label: 'Profile',     href: '/account/profile', Icon: User },
] as const

export default function AccountLayout({ children, active }: Props) {
    function logout() {
        router.post('/logout')
    }

    return (
        <div className="max-w-5xl mx-auto px-4 py-12">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-8">

                {/* Sidebar */}
                <aside className="md:col-span-1">
                    <nav className="space-y-1">
                        {nav.map(({ id, label, href, Icon }) => (
                            <Link
                                key={id}
                                href={href}
                                className={clsx(
                                    'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors',
                                    active === id
                                        ? 'bg-stone-900 text-white'
                                        : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                                )}
                            >
                                <Icon className="w-4 h-4" />
                                {label}
                            </Link>
                        ))}
                        <button
                            onClick={logout}
                            className="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-stone-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                        >
                            <LogOut className="w-4 h-4" /> Sign out
                        </button>
                    </nav>
                </aside>

                {/* Main */}
                <main className="md:col-span-3">
                    {children}
                </main>
            </div>
        </div>
    )
}
