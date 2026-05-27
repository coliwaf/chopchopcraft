import { Link } from '@inertiajs/react'
import { route } from 'ziggy-js'

export default function Footer() {
    return (
        <footer className="bg-stone-800 text-stone-300 mt-auto">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div>
                        <div className="flex items-center gap-2 mb-3">
                            <span className="text-2xl">🪵</span>
                            <span className="text-white font-bold text-lg">ChopChop Craft</span>
                        </div>
                        <p className="text-sm leading-relaxed text-stone-400">
                            Handcrafted chopping boards made from sustainably sourced hardwoods.
                            Each piece is unique, built to last a lifetime.
                        </p>
                    </div>

                    <div>
                        <h4 className="text-white font-semibold mb-4">Shop</h4>
                        <ul className="space-y-2 text-sm">
                            <li><Link href={route('products.index')} className="hover:text-white transition-colors">All boards</Link></li>
                            <li><Link href={route('products.index') + '?wood_type=Walnut'} className="hover:text-white transition-colors">Walnut</Link></li>
                            <li><Link href={route('products.index') + '?wood_type=Acacia'} className="hover:text-white transition-colors">Acacia</Link></li>
                            <li><Link href={route('products.index') + '?wood_type=Teak'} className="hover:text-white transition-colors">Teak</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 className="text-white font-semibold mb-4">Help</h4>
                        <ul className="space-y-2 text-sm">
                            <li>
                                <a href={`https://wa.me/${import.meta.env.VITE_WA_PHONE ?? '254700000000'}`}
                                   target="_blank" rel="noreferrer"
                                   className="hover:text-white transition-colors">
                                    WhatsApp us
                                </a>
                            </li>
                            <li><Link href={route('login')} className="hover:text-white transition-colors">My account</Link></li>
                            <li><Link href={route('account.orders')} className="hover:text-white transition-colors">Track order</Link></li>
                        </ul>
                    </div>
                </div>

                <div className="border-t border-stone-700 mt-10 pt-6 text-xs text-stone-500 flex flex-col sm:flex-row justify-between gap-2">
                    <span>© {new Date().getFullYear()} ChopChop Craft. All rights reserved.</span>
                    <span>Nairobi, Kenya 🇰🇪</span>
                </div>
            </div>
        </footer>
    )
}
