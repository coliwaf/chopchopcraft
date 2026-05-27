import { Head, Link, router } from '@inertiajs/react'
import { useState, useRef } from 'react'
import axios from 'axios'
import { route } from 'ziggy-js'
import AppLayout from '@/Components/Layout/AppLayout'
import ProductCard from '@/Components/Shop/ProductCard'
import { Product, Variant } from '@/types'
import { formatPrice } from '@/lib/utils'
import {
    ShoppingCart, MessageCircle,
    ChevronDown, ChevronUp,
    ChevronRight, Check,
} from 'lucide-react'
import SeoHead from '@/Components/SEO/SeoHead'

const SITE_URL = import.meta.env.VITE_APP_URL ?? 'https://chopchopcraft.ke'

interface Props {
    product: Product
    whatsapp_url: string
    related_products: Product[]
}

// ── Breadcrumbs ───────────────────────────────────────────────────────────────
function Breadcrumbs({ product }: { product: Product }) {
    return (
        <div className="border-b border-stone-100 bg-white">
            <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-11">
                    {/* Left — crumb trail */}
                    <nav className="flex items-center gap-1.5 text-sm text-stone-400">
                        <Link href={route('home')}
                            className="hover:text-stone-700 transition-colors">
                            Home
                        </Link>
                        <ChevronRight className="w-3.5 h-3.5 shrink-0" />
                        <Link href={route('products.index')}
                            className="hover:text-stone-700 transition-colors">
                            Shop
                        </Link>
                        {product.wood_type && (
                            <>
                                <ChevronRight className="w-3.5 h-3.5 shrink-0" />
                                <Link
                                    href={route('products.index') + '?wood_type=' + product.wood_type}
                                    className="hover:text-stone-700 transition-colors"
                                >
                                    {product.wood_type}
                                </Link>
                            </>
                        )}
                        <ChevronRight className="w-3.5 h-3.5 shrink-0" />
                        <span className="text-stone-600 font-medium truncate max-w-[160px] sm:max-w-xs">
                            {product.name}
                        </span>
                    </nav>

                    {/* Right — SKU badge */}
                    {product.variants[0]?.sku && (
                        <span className="text-xs text-stone-400 font-mono bg-stone-50
                                         border border-stone-200 rounded-md px-2 py-1 shrink-0">
                            Model - SKU: {product.variants[0].sku}
                        </span>
                    )}
                </div>
            </div>
        </div>
    )
}

// ── Desktop vertical image stack ──────────────────────────────────────────────
function DesktopGallery({ images, name }: { images: NonNullable<Product['images']>; name: string }) {
    if (images.length === 0) {
        return (
            <div className="w-full aspect-square bg-stone-100 rounded-sm flex items-center
                            justify-center text-8xl">
                🪵
            </div>
        )
    }
    return (
        <div className="space-y-3">
            {images.map((img, i) => (
                <div key={i}
                    className="w-full aspect-square bg-stone-100 rounded-sm overflow-hidden">
                    <img
                        src={img.url}
                        alt={img.alt ?? name}
                        className="w-full h-full object-cover"
                        loading={i === 0 ? 'eager' : 'lazy'}
                    />
                </div>
            ))}
        </div>
    )
}

// ── Mobile image carousel (large + thumbnail strip) ───────────────────────────
function MobileGallery({
    images, name, activeImage, setActiveImage,
}: {
    images: NonNullable<Product['images']>
    name: string
    activeImage: number
    setActiveImage: (i: number) => void
}) {
    return (
        <div className="space-y-3">
            <div className="aspect-square bg-stone-100 rounded-sm overflow-hidden">
                {images.length > 0 ? (
                    <img
                        src={images[activeImage]?.url}
                        alt={images[activeImage]?.alt ?? name}
                        className="w-full h-full object-cover"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-8xl">🪵</div>
                )}
            </div>
            {images.length > 1 && (
                <div className="flex gap-2 overflow-x-auto pb-1">
                    {images.map((img, i) => (
                        <button
                            key={i}
                            onClick={() => setActiveImage(i)}
                            className={`shrink-0 w-16 h-16 rounded-sm overflow-hidden
                                        border-2 transition-colors ${activeImage === i
                                    ? 'border-amber-500'
                                    : 'border-transparent'
                                }`}
                        >
                            <img src={img.thumb || img.url} alt=""
                                className="w-full h-full object-cover" />
                        </button>
                    ))}
                </div>
            )}
        </div>
    )
}

// ── Product detail panel (shared by mobile + desktop) ─────────────────────────
function DetailPanel({
    product, selectedVariant, setSelectedVariant,
    qty, setQty, adding, added, error, careOpen, setCareOpen,
    addToCart, waVariantUrl,
}: {
    product: Product
    selectedVariant: Variant | null
    setSelectedVariant: (v: Variant) => void
    qty: number
    setQty: (n: number) => void
    adding: boolean
    added: boolean
    error: string | null
    careOpen: boolean
    setCareOpen: (b: boolean) => void
    addToCart: () => void
    waVariantUrl: string
}) {
    return (
        <div className="space-y-6">
            {/* Name + wood type */}
            <div>
                {product.wood_type && (
                    <span className="text-amber-700 text-xs font-semibold uppercase tracking-widest">
                        {product.wood_type}
                        {product.finish && ` · ${product.finish}`}
                    </span>
                )}
                <h1 className="text-2xl lg:text-3xl font-bold text-stone-800 mt-1 leading-tight">
                    {product.name}
                </h1>
                {product.description && (
                    <p className="text-stone-500 mt-2 leading-relaxed text-sm">
                        {product.description}
                    </p>
                )}
            </div>

            {/* Price */}
            <div className="text-3xl font-bold text-stone-800">
                {selectedVariant
                    ? formatPrice(selectedVariant.price)
                    : formatPrice(product.min_price)}
            </div>

            {/* Variant selector */}
            {product.variants.length > 0 ? (
                <div>
                    <label className="block text-sm font-semibold text-stone-700 mb-2.5">
                        Size / Type
                        {selectedVariant && (
                            <span className="font-normal text-stone-400 ml-2">
                                — {selectedVariant.name}
                            </span>
                        )}
                    </label>
                    <div className="flex flex-wrap gap-2">
                        {product.variants.map((v) => (
                            <button
                                key={v.id}
                                disabled={!v.in_stock}
                                onClick={() => { setSelectedVariant(v); setQty(1) }}
                                className={`px-4 py-2 rounded-sm border font-medium text-sm
                                            transition-all ${selectedVariant?.id === v.id
                                        ? 'bg-stone-800 border-stone-800 text-white'
                                        : v.in_stock
                                            ? 'border-stone-200 text-stone-700 hover:border-stone-400'
                                            : 'border-stone-100 text-stone-300 cursor-not-allowed line-through'
                                    }`}
                            >
                                {v.size ?? v.name}
                                {v.low_stock && v.in_stock && (
                                    <span className="ml-1.5 text-amber-500 text-xs">
                                        Only {v.stock_qty} left
                                    </span>
                                )}
                            </button>
                        ))}
                    </div>
                </div>
            ) : (
                <p className="text-sm text-stone-400 italic">No variants available.</p>
            )}

            {/* Qty stepper */}
            {selectedVariant && (
                <div className="flex items-center gap-3">
                    <span className="text-sm font-semibold text-stone-700">Qty</span>
                    <div className="flex items-center border border-stone-200 rounded-sm overflow-hidden">
                        <button
                            onClick={() => setQty(Math.max(1, qty - 1))}
                            className="px-3 py-2 hover:bg-stone-100 transition-colors text-stone-600"
                        >−</button>
                        <span className="px-4 py-2 font-medium text-stone-800
                                         min-w-[3rem] text-center">
                            {qty}
                        </span>
                        <button
                            onClick={() => setQty(Math.min(selectedVariant.stock_qty, qty + 1))}
                            className="px-3 py-2 hover:bg-stone-100 transition-colors text-stone-600"
                        >+</button>
                    </div>
                </div>
            )}

            {/* Error */}
            {error && (
                <p className="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{error}</p>
            )}

            {/* CTA buttons */}
            <div className="flex flex-col gap-3">
                <button
                    onClick={addToCart}
                    disabled={adding || !selectedVariant?.in_stock}
                    className={`w-full flex items-center justify-center gap-2 py-4 px-6
                                rounded-full font-semibold text-base transition-all ${added
                            ? 'bg-green-600 text-white'
                            : selectedVariant?.in_stock
                                ? 'bg-stone-800 hover:bg-stone-700 text-white'
                                : 'bg-stone-200 text-stone-400 cursor-not-allowed'
                        }`}
                >
                    {added ? (
                        <><Check className="w-5 h-5" /> Added to cart!</>
                    ) : (
                        <><ShoppingCart className="w-5 h-5" />
                            {selectedVariant?.in_stock ? 'Add to Cart' : 'Out of Stock'}
                        </>
                    )}
                </button>

                <a
                    href={waVariantUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="w-full flex items-center justify-center gap-2 py-3.5 px-6
                               rounded-full font-semibold text-base bg-green-600
                               hover:bg-green-500 text-white transition-colors"
                >
                    <MessageCircle className="w-5 h-5" /> Order via WhatsApp
                </a>
            </div>

            {/* Dimensions */}
            {product.dimensions && Object.keys(product.dimensions).length > 0 && (
                <div className="border border-stone-200 rounded-sm p-4">
                    <h3 className="text-sm font-semibold text-stone-700 mb-3">Dimensions</h3>
                    <dl className="grid grid-cols-2 gap-x-4 gap-y-2">
                        {Object.entries(product.dimensions).map(([k, v]) => (
                            <div key={k} className="flex justify-between text-sm">
                                <dt className="text-stone-500 capitalize">{k}</dt>
                                <dd className="font-medium text-stone-700">{v}</dd>
                            </div>
                        ))}
                    </dl>
                </div>
            )}

            {/* Care instructions */}
            {(product.care_instructions ?? []).length > 0 && (
                <div className="border border-stone-200 rounded-xl overflow-hidden">
                    <button
                        className="w-full flex items-center justify-between px-4 py-3
                                   text-sm font-semibold text-stone-700 hover:bg-stone-50
                                   transition-colors"
                        onClick={() => setCareOpen(!careOpen)}
                    >
                        <span>Care instructions</span>
                        {careOpen
                            ? <ChevronUp className="w-4 h-4 text-stone-400" />
                            : <ChevronDown className="w-4 h-4 text-stone-400" />
                        }
                    </button>
                    {careOpen && (
                        <ul className="px-4 pb-4 space-y-2 border-t border-stone-100">
                            {product.care_instructions!.map((tip, i) => (
                                <li key={i} className="text-sm text-stone-600 flex gap-2 pt-2">
                                    <span className="text-amber-600 shrink-0">•</span>
                                    {tip}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}
        </div>
    )
}

// ── Main component ────────────────────────────────────────────────────────────
export default function ProductShow({ product, whatsapp_url, related_products }: Props) {
    const firstVariant = product.variants?.[0] ?? null
    const [selectedVariant, setSelectedVariant] = useState<Variant | null>(firstVariant)
    const [qty, setQty] = useState(1)
    const [activeImage, setActiveImage] = useState(0)
    const [adding, setAdding] = useState(false)
    const [added, setAdded] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [careOpen, setCareOpen] = useState(false)

    const images = (product.images ?? []).filter(img => img.url)

    async function addToCart() {
        if (!selectedVariant) return
        setAdding(true)
        setError(null)
        try {
            await axios.post(route('cart.store'), {
                variant_id: selectedVariant.id, qty,
            })
            setAdded(true)
            setTimeout(() => setAdded(false), 2500)
            router.reload({ only: ['cart'] })
        } catch (err: any) {
            setError(err.response?.data?.message ?? 'Could not add to cart.')
        } finally {
            setAdding(false)
        }
    }

    const waNumber = whatsapp_url.split('wa.me/')[1]?.split('?')[0] ?? ''
    const waVariantUrl = selectedVariant
        ? `https://wa.me/${waNumber}?text=${encodeURIComponent(
            `Hi! I'd like to order:\n\n*${product.name}* – ${selectedVariant.name}\nPrice: ${formatPrice(selectedVariant.price)}\n\nPlease confirm availability.`
        )}`
        : whatsapp_url

    const panelProps = {
        product, selectedVariant, setSelectedVariant,
        qty, setQty, adding, added, error,
        careOpen, setCareOpen, addToCart, waVariantUrl,
    }

    return (
        <AppLayout>
            <SeoHead
                title={product.name}
                description={
                    product.description ??
                    `${product.name} — handcrafted ${product.wood_type ?? ''} chopping board made in Kenya. ${product.finish ? product.finish + ' finish.' : ''} Order online with M-Pesa, Stripe or WhatsApp.`
                }
                image={images[0]?.url}
                url={`${SITE_URL}/products/${product.slug}`}
                type="product"
                breadcrumbs={[
                    { name: 'Home', url: SITE_URL },
                    { name: 'Shop', url: `${SITE_URL}/products` },
                    ...(product.wood_type
                        ? [{ name: product.wood_type, url: `${SITE_URL}/products?wood_type=${product.wood_type}` }]
                        : []
                    ),
                    { name: product.name, url: `${SITE_URL}/products/${product.slug}` },
                ]}
                product={{
                    price: selectedVariant?.price ?? product.min_price,
                    currency: 'KES',
                    availability: (selectedVariant?.in_stock ?? product.in_stock) ? 'InStock' : 'OutOfStock',
                    sku: selectedVariant?.sku ?? product.variants[0]?.sku,
                    brand: 'ChopChop Craft',
                }}
            />

            {/* Breadcrumbs + SKU bar */}
            <Breadcrumbs product={product} />

            <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                {/* ── Mobile layout (< lg) ─────────────────────────────────── */}
                <div className="lg:hidden space-y-8">
                    <MobileGallery
                        images={images} name={product.name}
                        activeImage={activeImage} setActiveImage={setActiveImage}
                    />
                    <DetailPanel {...panelProps} />
                </div>

                {/* ── Desktop layout (>= lg) ───────────────────────────────── */}
                <div className="hidden lg:grid lg:grid-cols-[60%_40%] gap-3 items-start">
                    {/* Image stack — full height, scrolls normally */}
                    <DesktopGallery images={images} name={product.name} />

                    {/* Detail panel — sticks under navbar until images run out */}
                    <div className="sticky top-[65px] max-h-[calc(100vh-80px)] overflow-y-auto
                                    scrollbar-thin scrollbar-thumb-stone-200 scrollbar-track-transparent
                                    pr-1">
                        <DetailPanel {...panelProps} />
                    </div>
                </div>

                {/* Long description */}
                {product.long_description && (
                    <div className="mt-16 max-w-3xl">
                        <h2 className="text-2xl font-bold text-stone-800 mb-4">About this board</h2>
                        <div
                            className="prose prose-stone"
                            dangerouslySetInnerHTML={{ __html: product.long_description }}
                        />
                    </div>
                )}
            </div>

            {/* ── Related products ─────────────────────────────────────────── */}
            {related_products.length > 0 && (
                <section className="border-t border-stone-100 bg-stone-50">
                    <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                        <div className="flex items-end justify-between mb-8">
                            <div>
                                <h2 className="text-2xl font-bold text-stone-800">
                                    You might also like
                                </h2>
                                <p className="text-stone-500 text-sm mt-1">
                                    More {product.wood_type} boards
                                </p>
                            </div>
                            <Link
                                href={route('products.index') +
                                    (product.wood_type ? '?wood_type=' + product.wood_type : '')}
                                className="text-amber-700 hover:text-amber-600 font-medium text-sm"
                            >
                                View all →
                            </Link>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            {related_products.map((p) => (
                                <ProductCard key={p.id} product={p} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </AppLayout>
    )
}
