//
import { Link, usePage } from '@inertiajs/react'
import { route } from 'ziggy-js'
import AppLayout from '@/Components/Layout/AppLayout'
import SeoHead from '@/Components/SEO/SeoHead'
import ProductCard from '@/Components/Shop/ProductCard'
import { Product, PageProps } from '@/types'
import MosaicGallery from '@/Components/Shop/MosaicGallery'

interface Props { featured: Product[] }

const SITE_URL = import.meta.env.VITE_APP_URL ?? 'https://chopchopcraft.ke'

// ── Hero section — rendered INSIDE the background wrapper in AppLayout ────────
function Hero() {
    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 md:py-40 text-center">
            <h1 className="text-4xl md:text-6xl font-bold tracking-tight text-white mb-6 leading-tight
                           drop-shadow-lg">
                Boards Built<br className="hidden md:block" /> to Last a Lifetime
            </h1>
            <p className="text-white/80 text-lg md:text-xl max-w-xl mx-auto mb-10 leading-relaxed drop-shadow">
                Handcrafted in Kenya from sustainably sourced hardwoods.
                Every board is unique — like the meals you'll prepare on it.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    href={route('products.index')}
                    className="bg-amber-500 hover:bg-amber-400 text-white font-semibold
                               px-8 py-4 rounded-sm transition-colors text-lg shadow-lg"
                >
                    Shop Now
                </Link>
            </div>
        </div>
    )
}

export default function Home({ featured }: Props) {
    const { app } = usePage<PageProps>().props
    const waNumber = app?.wa_number ?? '254700000000'

    return (
        <AppLayout heroBg="/images/hero_d7zrxdd7zrxdd7zr.png" hero={<Hero />}>

            <SeoHead
                title="Handcrafted Chopping Boards — Made in Kenya"
                description="Shop handcrafted Acacia, Walnut, Olive and Teak chopping boards made by artisans in Nairobi. Fast nationwide delivery. Order via M-Pesa, Stripe or WhatsApp."
                url={SITE_URL}
                image={`${SITE_URL}/images/og-default.jpg`}
                type="website"
                schema={{
                    '@context': 'https://schema.org',
                    '@type': 'WebSite',
                    name: 'ChopChop Craft',
                    url: SITE_URL,
                    potentialAction: {
                        '@type': 'SearchAction',
                        target: `${SITE_URL}/products?search={search_term_string}`,
                        'query-input': 'required name=search_term_string',
                    },
                }}
            />

            {/* USPs */}
            <section className="bg-amber-50 border-y border-amber-100">
                <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                        {[
                            { icon: '🌿', title: 'Sustainably sourced', desc: 'FSC-certified hardwoods' },
                            { icon: '🔨', title: 'Handcrafted', desc: 'Made by artisans in Nairobi' },
                            { icon: '🚚', title: 'Nationwide delivery', desc: 'Kenya-wide in 2–4 days' },
                            { icon: '💬', title: 'WhatsApp support', desc: 'Order & track via chat' },
                        ].map((usp) => (
                            <div key={usp.title}>
                                <div className="text-3xl mb-2">{usp.icon}</div>
                                <div className="font-semibold text-stone-800 text-sm">{usp.title}</div>
                                <div className="text-stone-500 text-xs mt-0.5">{usp.desc}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <MosaicGallery
                title="Every board, a work of art"
                subtitle="Photographed straight from our workshop in Nairobi."
                images={[
                    { src: '/images/mosaic/211_STR_ALT_PLP.jpg', alt: 'Acacia board grain close-up' },
                    { src: '/images/mosaic/BB03_STR_PDP.jpg', alt: 'Walnut board oiling process' },
                    { src: '/images/mosaic/CB4C-W120801_STR_PLP.jpg', alt: 'Board being sanded by hand' },
                    { src: '/images/mosaic/CB1052-1M1212175_TD_TOP__PLP.jpg', alt: 'Stack of finished boards' },
                    { src: '/images/mosaic/CCB183-R_STR_PLP.jpg', alt: 'Olive wood end grain' },
                    { src: '/images/mosaic/CCB1818-225_TD__PLP.jpg', alt: 'Board with fresh vegetables' },
                    { src: '/images/mosaic/CHY1212150_PLP_2.jpg', alt: 'Teak board on kitchen counter' },
                    { src: '/images/mosaic/CHY-RST1312175_STR_PLP.jpg', alt: 'Workshop tools' },        // ← center
                    { src: '/images/mosaic/MCR1-TD_BTM__PLP.jpg', alt: 'Bamboo boards stacked' },
                    { src: '/images/mosaic/MCS1_TD_BTM__PLP.jpg', alt: 'Board with cheese platter' },
                    { src: '/images/mosaic/PM1514225-P_TD_BTM__PLP.jpg', alt: 'Artisan at work' },
                    { src: '/images/mosaic/R02-PIE_TD_BTM__PLP.jpg', alt: 'Board detail — handle' },
                    { src: '/images/mosaic/RA01_STR_PLP.jpg', alt: 'Raw timber selection' },
                    { src: '/images/mosaic/WAL-1812175-SSF_TD_TOP__PLP.jpg', alt: 'Finished walnut board' },
                    { src: '/images/mosaic/WAL-CCB24-S_STR_ALT_PLP.jpg', alt: 'Board gift wrapped' },
                ]}
            />

            {/* Featured products */}
            {featured.length > 0 && (
                <section className="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div className="flex items-end justify-between mb-8">
                        <div>
                            &nbsp;
                            {/* <h2 className="text-3xl font-bold text-stone-800">Featured Boards</h2>
                            <p className="text-stone-500 mt-1">Our most loved pieces</p> */}
                        </div>
                        <Link href={route('products.index')}
                            className="text-slate-700 hover:text-amber-600 font-bold text-sm">
                            All Products →
                        </Link>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {featured.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                </section>
            )}

            {/* CTA Banner */}
            <section className="bg-stone-800 text-white">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
                    <h2 className="text-3xl font-bold mb-4">Not sure which board to pick?</h2>
                    <p className="text-stone-400 mb-8">
                        Chat with us on WhatsApp — we'll help you find the perfect board for your kitchen.
                    </p>
                    {/* <a href={`https://wa.me/${import.meta.env.VITE_WA_PHONE ?? '254700000000'}?text=${encodeURIComponent("Hi! I'd like help choosing a chopping board.")}`} */}
                    <a
                        href={`https://wa.me/${waNumber}?text=${encodeURIComponent("Hi! I'd like help choosing a chopping board.")}`}
                        target="_blank" rel="noreferrer"
                        className="inline-flex items-center gap-2 bg-green-500 hover:bg-green-400
                                  text-white font-semibold px-8 py-4 rounded-sm transition-colors text-lg">
                        <span>💬</span> Chat on WhatsApp
                    </a>
                </div>
            </section>
        </AppLayout>
    )
}
