import { router } from '@inertiajs/react'
import { useState } from 'react'
import { route } from 'ziggy-js'
import AppLayout from '@/Components/Layout/AppLayout'
import ProductCard from '@/Components/Shop/ProductCard'
import { Product, PaginatedData, PageProps } from '@/types'
import { SlidersHorizontal, X, ChevronDown, ChevronUp } from 'lucide-react'
import SeoHead from '@/Components/SEO/SeoHead'

const SITE_URL = import.meta.env.VITE_APP_URL ?? 'https://chopchopcraft.ke'

interface Props {
    products: PaginatedData<Product>
    filters: { wood_type?: string; size?: string; sort?: string }
    wood_types: string[]
    sizes: string[]
}

const SORT_OPTIONS = [
    { value: 'featured', label: 'Featured' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'newest', label: 'Newest' },
]
// ── USP data ──────────────────────────────────────────────────────────────────
const USPS = [
    {
        image: '/images/usp/2up-endgrain_left_003.jpg',
        icon: '🔨',
        title: 'Handcrafted',
        desc: 'Every board is shaped and finished by hand in our Nairobi workshop. No two are exactly alike.',
    },
    {
        image: '/images/usp/3up-shopdropdown-collections-left_003.jpg',
        icon: '🌿',
        title: 'Sustainably sourced',
        desc: 'We use only FSC-certified hardwoods — Acacia, Walnut, Olive, and Teak — from managed forests.',
    },
    {
        image: '/images/usp/shopdropdown-middle_002.jpg',
        icon: '🚚',
        title: 'Fast delivery',
        desc: 'Nationwide delivery across Kenya in 2–4 business days, with WhatsApp tracking updates.',
    },
]

// ── Hero ──────────────────────────────────────────────────────────────────────
function ShopHero({ total }: { total: number }) {
    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
            <h1 className="text-4xl md:text-6xl font-bold text-white tracking-wide drop-shadow-lg mb-4">
                Artisanal. Cutting & Culinary
            </h1>
            <p className="text-white/75 text-lg max-w-lg mx-auto drop-shadow">
                Shop all {total} handcrafted, durable, beautiful boards.
            </p>
        </div>
    )
}

// ── USP grid ─────────────────────────────────────────────────────────────────
function UspGrid() {
    return (
        <section className="bg-stone-50 border-b border-stone-200">
            <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {USPS.map((usp) => (
                        <div key={usp.title} className="flex flex-col items-center text-center group bg-white rounded-sm border border-stone-100 transition-all duration-200 hover:-translate-y-0.5">
                            {/* Image */}
                            <div className="w-full aspect-3/4 overflow-hidden mb-5 bg-stone-200">
                                <img
                                    src={usp.image}
                                    alt={usp.title}
                                    className="w-full h-full object-cover group-hover:scale-105
                                               transition-transform duration-500"
                                    onError={(e) => {
                                        // Fallback if image not found — show emoji placeholder
                                        const t = e.currentTarget
                                        t.style.display = 'none'
                                        t.parentElement!.innerHTML =
                                            `<div class="w-full h-full flex items-center justify-center text-6xl bg-amber-50">${usp.icon}</div>`
                                    }}
                                />
                            </div>
                            {/* Text */}
                            <h2 className="text-3xl font-bold text-stone-800 mb-1">{usp.title}</h2>
                            <p className="text-stone-500 text-sm leading-relaxed max-w-xs">{usp.desc}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    )
}

// ── Filter bar ────────────────────────────────────────────────────────────────
interface FilterBarProps {
    filters: Props['filters']
    wood_types: string[]
    sizes: string[]
    total: number
    onApply: (key: string, value: string | null) => void
    onClear: () => void
}

function FilterBar({ filters, wood_types, sizes, total, onApply, onClear }: FilterBarProps) {
    const [open, setOpen] = useState(false)
    const hasFilters = !!(filters.wood_type || filters.size)
    const activeCount = [filters.wood_type, filters.size].filter(Boolean).length

    return (
        <div className="border-b border-stone-200 bg-white sticky top-0 z-40">
            {/* Bar row */}
            <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-14 gap-4">

                    {/* Left — toggle + active badges */}
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setOpen(!open)}
                            className="flex items-center gap-2 text-sm font-medium text-stone-700
                                       hover:text-stone-900 transition-colors"
                        >
                            <SlidersHorizontal className="w-4 h-4" />
                            Filters
                            {activeCount > 0 && (
                                <span className="bg-amber-600 text-white text-xs font-bold
                                                 rounded-full w-5 h-5 flex items-center justify-center">
                                    {activeCount}
                                </span>
                            )}
                            {open
                                ? <ChevronUp className="w-3.5 h-3.5 text-stone-400" />
                                : <ChevronDown className="w-3.5 h-3.5 text-stone-400" />
                            }
                        </button>

                        {/* Active filter chips */}
                        {filters.wood_type && (
                            <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-800
                                             text-xs font-medium px-2.5 py-1 rounded-full">
                                {filters.wood_type}
                                <button onClick={() => onApply('wood_type', null)} className="hover:text-amber-600">
                                    <X className="w-3 h-3" />
                                </button>
                            </span>
                        )}
                        {filters.size && (
                            <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-800
                                             text-xs font-medium px-2.5 py-1 rounded-full">
                                {filters.size}
                                <button onClick={() => onApply('size', null)} className="hover:text-amber-600">
                                    <X className="w-3 h-3" />
                                </button>
                            </span>
                        )}
                        {hasFilters && (
                            <button onClick={onClear}
                                className="text-xs text-stone-400 hover:text-red-500 transition-colors">
                                Clear all
                            </button>
                        )}
                    </div>

                    {/* Right — count + sort */}
                    <div className="flex items-center gap-3">
                        <span className="text-sm text-stone-400 hidden sm:block">
                            {total} {total === 1 ? 'board' : 'boards'}
                        </span>
                        <select
                            value={filters.sort ?? 'featured'}
                            onChange={(e) => onApply('sort', e.target.value)}
                            className="text-sm border border-stone-200 rounded-lg px-3 py-1.5
                                       bg-white text-stone-700 focus:outline-none focus:ring-2
                                       focus:ring-amber-500 focus:border-amber-500"
                        >
                            {SORT_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>{o.label}</option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            {/* Collapsible filter panel */}
            {open && (
                <div className="border-t border-stone-100 bg-stone-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                        <div className="flex flex-wrap gap-10">

                            {/* Wood type */}
                            <div>
                                <h3 className="text-xs font-semibold text-stone-500 uppercase
                                               tracking-widest mb-3">
                                    Wood type
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {wood_types.map((wt) => (
                                        <button
                                            key={wt}
                                            onClick={() => onApply('wood_type',
                                                filters.wood_type === wt ? null : wt)}
                                            className={`px-3 py-1.5 rounded-lg text-sm font-medium
                                                        border transition-all ${filters.wood_type === wt
                                                    ? 'bg-amber-600 border-amber-600 text-white shadow-sm'
                                                    : 'border-stone-200 bg-white text-stone-600 hover:border-amber-400'
                                                }`}
                                        >
                                            {wt}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Size */}
                            <div>
                                <h3 className="text-xs font-semibold text-stone-500 uppercase
                                               tracking-widest mb-3">
                                    Size
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {sizes.map((s) => (
                                        <button
                                            key={s}
                                            onClick={() => onApply('size',
                                                filters.size === s ? null : s)}
                                            className={`px-3 py-1.5 rounded-lg text-sm font-medium
                                                        border transition-all ${filters.size === s
                                                    ? 'bg-amber-600 border-amber-600 text-white shadow-sm'
                                                    : 'border-stone-200 bg-white text-stone-600 hover:border-amber-400'
                                                }`}
                                        >
                                            {s}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    )
}

// ── Main page ─────────────────────────────────────────────────────────────────
export default function ProductsIndex({ products, filters, wood_types, sizes }: Props) {

    function applyFilter(key: string, value: string | null) {
        const params: Record<string, string> = { ...filters }
        if (value) params[key] = value
        else delete params[key]
        router.get(route('products.index'), params, { preserveScroll: true, replace: true })
    }

    function clearFilters() {
        router.get(route('products.index'), {}, { replace: true })
    }

    return (
        <AppLayout
            heroBg="/images/hero_6sykep6sykep6syk-thin.jpg"
            hero={<ShopHero total={products.total} />}
        >
            <SeoHead
                title={
                    filters.wood_type
                        ? `${filters.wood_type} Chopping Boards — Shop Online`
                        : 'Shop All Handcrafted Chopping Boards'
                }
                description={
                    filters.wood_type
                        ? `Browse our ${filters.wood_type} chopping boards, handcrafted in Kenya. ${products.total} boards available. Fast delivery nationwide.`
                        : `Shop ${products.total} handcrafted chopping boards — Acacia, Walnut, Olive, Teak and more. Made in Kenya. Order via M-Pesa, or WhatsApp.`
                }
                url={`${SITE_URL}/products${filters.wood_type ? '?wood_type=' + filters.wood_type : ''}`}
                // Noindex filtered/paginated pages to avoid duplicate content
                noindex={
                    !!(filters.wood_type || filters.size || filters.sort) ||
                    products.current_page > 1
                }
                breadcrumbs={[
                    { name: 'Home', url: SITE_URL },
                    { name: 'Shop', url: `${SITE_URL}/products` },
                    ...(filters.wood_type ? [{ name: filters.wood_type, url: `${SITE_URL}/products?wood_type=${filters.wood_type}` }] : []),
                ]}
            />

            {/* 3-column USP image grid */}
            <UspGrid />

            {/* Sticky horizontal filter bar */}
            <FilterBar
                filters={filters}
                wood_types={wood_types}
                sizes={sizes}
                total={products.total}
                onApply={applyFilter}
                onClear={clearFilters}
            />

            {/* Product grid */}
            <div className="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                {products.data.length === 0 ? (
                    <div className="text-center py-24">
                        <div className="text-5xl mb-4">🔍</div>
                        <p className="font-medium text-stone-600 mb-2">No boards match your filters.</p>
                        <button onClick={clearFilters}
                            className="text-amber-600 hover:underline text-sm">
                            Clear filters
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {products.data.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {products.last_page > 1 && (
                    <div className="flex justify-center gap-2 mt-12">
                        {products.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url)}
                                className={`px-4 py-2 rounded-xl text-sm font-medium border transition-colors ${link.active
                                        ? 'bg-amber-600 border-amber-600 text-white'
                                        : link.url
                                            ? 'border-stone-200 text-stone-600 hover:bg-stone-50'
                                            : 'border-stone-100 text-stone-300 cursor-not-allowed'
                                    }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    )
}

