import { Link } from '@inertiajs/react'
import { Product } from '@/types'
import { formatPrice } from '@/lib/utils'
import { route } from 'ziggy-js'

interface Props {
    product: Product
}

export default function ProductCard({ product }: Props) {
    // Defensive fallback — variants may be absent on lightweight product maps
    const variants = product.variants ?? []

    return (
        <Link href={route('products.show', product.slug)}
            className="group bg-white rounded-sm overflow-hidden
                         border border-stone-100 transition-all duration-200 hover:-translate-y-0.5">

            {/* Image */}
            <div className="aspect-square bg-stone-100 overflow-hidden relative">
                {product.image ? (
                    <img src={product.image} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-5xl">🪵</div>
                )}

                {/* Badges */}
                <div className="absolute top-2 left-2 flex gap-1">
                    {product.is_featured && (
                        <span className="bg-amber-600 text-white text-xs font-semibold px-2 py-0.5 rounded-md">
                            Popular
                        </span>
                    )}
                    {!product.in_stock && (
                        <span className="bg-stone-600 text-white text-xs font-semibold px-2 py-0.5 rounded-md">
                            Sold out
                        </span>
                    )}
                </div>
            </div>

            {/* Info */}
            <div className="p-4 pt-3">
                <h2 className="uppercase text-lg font-extrabold text-stone-800 group-hover:text-amber-700 transition-colors leading-snug tracking-wide mb-2">
                    {product.name}
                </h2>
                <div className="flex items-start justify-between gap-2">
                    <span className="text-md font-normal text-stone-800 whitespace-nowrap">
                        {formatPrice(product.min_price)}
                    </span>
                    {product.wood_type && (
                        <span className="text-xs text-stone-400 mt-0.5 block">{product.wood_type}</span>
                    )}
                </div>

                {/* {product.description && (
                    <p className="text-stone-500 text-sm mt-2 line-clamp-2">{product.description}</p>
                )} */}

                {/* Variant size pills */}
                {/* {product.variants.length > 0 && (
                    <div className="flex gap-1.5 mt-3 flex-wrap">
                        {product.variants.slice(0, 4).map((v) => (
                            <span key={v.id}
                                className="text-xs border border-stone-200 rounded-md px-1.5 py-0.5 text-stone-500">
                                {v.size ?? v.name}
                            </span>
                        ))}
                        {product.variants.length > 4 && (
                            <span className="text-xs text-stone-400">+{product.variants.length - 4} more</span>
                        )}
                    </div>
                )} */}
            </div>
        </Link>
    )
}
