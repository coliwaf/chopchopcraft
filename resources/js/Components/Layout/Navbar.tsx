import { Link, usePage } from '@inertiajs/react'
import { ShoppingCart, Menu, X } from 'lucide-react'
import { useState } from 'react'
import { route } from 'ziggy-js'
import { PageProps } from '@/types'
import Logo from '@/assets/chopchop_craft_logo_file.svg?react';
// import Logo from '@/assets/logo.svg?react';

interface NavbarProps {
    // When true: no background, white text — sits over a hero image.
    // When false (default): solid white bg, dark text — normal page navbar.
    transparent?: boolean
}

export default function Navbar({ transparent = false }: NavbarProps) {
    const { auth, cart } = usePage<PageProps>().props
    const [mobileOpen, setMobileOpen] = useState(false)

    
    const navBase   = transparent
        ? 'text-white/90 hover:text-white'
        : 'text-stone-600 hover:text-stone-900'

    const wrapperBg = transparent
        ? 'bg-transparent'
        : 'bg-white border-b border-stone-200 sticky top-0 z-50'

    const logoColor  = transparent ? 'text-white/80' : 'text-stone-800/90'
    const mobileBg  = transparent ? 'bg-black/60 backdrop-blur-sm' : 'bg-white'
    const mobileDivider = transparent ? 'border-white/20' : 'border-stone-100'
    const mobileText    = transparent ? 'text-white/90' : 'text-stone-700'
    const iconColor     = transparent ? 'text-white' : 'text-stone-700'
    const toggleHover   = transparent ? 'hover:bg-white/10' : 'hover:bg-stone-100'

    return (
        <nav className={wrapperBg}>
            <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16">

                    {/* Logo */}
                    <Link href={route('home')} className="pt-3">
                        <Logo className={`w-50 h-50 ${logoColor}`} />
                    </Link>

                    {/* Desktop nav */}
                    <div className="hidden md:flex items-center gap-8">
                        <Link href={route('products.index')}
                              className={`font-medium transition-colors ${navBase}`}>
                            Shop
                        </Link>
                        <Link href={route('products.index') + '?wood_type=Walnut'}
                              className={`font-medium transition-colors ${navBase}`}>
                            Collections
                        </Link>
                    </div>

                    {/* Right side */}
                    <div className="flex items-center gap-4">
                        {auth.user ? (
                            <Link href={route('account.orders')}
                                  className={`text-sm font-medium hidden md:block transition-colors ${navBase}`}>
                                My Orders
                            </Link>
                        ) : (
                            <Link href={route('login')}
                                  className={`text-sm font-medium hidden md:block transition-colors ${navBase}`}>
                                Sign in
                            </Link>
                        )}

                        {/* Cart */}
                        <Link href={route('cart.index')} 
                                className={`relative p-2 rounded-lg transition-colors ${toggleHover}`}>
                            <ShoppingCart className={`w-5 h-5 ${iconColor}`} />
                            {cart.count > 0 && (
                                <span className="absolute -top-0.5 -right-0.5 bg-amber-600 text-white text-xs font-bold
                                                 rounded-full w-4 h-4 flex items-center justify-center leading-none">
                                    {cart.count > 9 ? '9+' : cart.count}
                                </span>
                            )}
                        </Link>

                        {/* Mobile menu toggle */}
                        <button
                            className={`md:hidden p-2 rounded-lg transition-colors ${toggleHover}`}
                            onClick={() => setMobileOpen(!mobileOpen)}
                        >
                            {mobileOpen ? <X className={`w-5 h-5 ${iconColor}`} /> : <Menu className={`w-5 h-5 ${iconColor}`} />}
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile menu */}
            {mobileOpen && (
                <div className={`md:hidden border-t ${mobileDivider} ${mobileBg} px-4 py-4 space-y-3`}>
                    <Link href={route('products.index')} onClick={() => setMobileOpen(false)}
                          className={`block font-medium py-2 ${mobileText}`}>
                        Shop
                    </Link>
                    <Link href={route('products.index') + '?wood_type=Walnut'} onClick={() => setMobileOpen(false)}
                          className={`block font-medium py-2 ${mobileText}`}>
                        Collections
                    </Link>
                    {auth.user ? (
                        <Link href={route('account.orders')} onClick={() => setMobileOpen(false)}
                              className={`block font-medium py-2 ${mobileText}`}>
                            My Orders
                        </Link>
                    ) : (
                        <Link href={route('login')} onClick={() => setMobileOpen(false)}
                              className={`block font-medium py-2 ${mobileText}`}>
                            Sign in
                        </Link>
                    )}
                </div>
            )}
        </nav>
    )
}
