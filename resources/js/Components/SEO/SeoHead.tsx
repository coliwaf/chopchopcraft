import { Head, usePage } from '@inertiajs/react'
import { PageProps } from '@/types'

interface SeoHeadProps {
	title: string
	description: string
	image?: string        // absolute URL
	url?: string        // canonical URL — defaults to current page
	type?: 'website' | 'product' | 'article'
	noindex?: boolean       // true for filtered/paginated pages
	schema?: object        // JSON-LD structured data object
	breadcrumbs?: { name: string; url: string }[]
	product?: {
		price: number
		currency: string
		availability: 'InStock' | 'OutOfStock'
		sku?: string
		brand?: string
	}
}

const SITE_NAME = 'ChopChop Craft'
const SITE_URL = import.meta.env.VITE_APP_URL ?? 'https://chopchopcraft.ke'
const DEFAULT_OG = `${SITE_URL}/images/og-default.jpg`

export default function SeoHead({
	title,
	description,
	image,
	url,
	type = 'website',
	noindex = false,
	schema,
	breadcrumbs,
	product,
}: SeoHeadProps) {
	const { app } = usePage<PageProps>().props

	const fullTitle = `${title} | ${SITE_NAME}`
	const ogImage = image ?? DEFAULT_OG
	const canonical = url ?? (typeof window !== 'undefined' ? window.location.href : SITE_URL)

	// ── Build JSON-LD schemas ──────────────────────────────────────────────────
	const schemas: object[] = []

	// Always include Organization
	schemas.push({
		'@context': 'https://schema.org',
		'@type': 'Organization',
		name: SITE_NAME,
		url: SITE_URL,
		contactPoint: {
			'@type': 'ContactPoint',
			contactType: 'customer service',
			availableLanguage: 'English',
		},
	})

	// Breadcrumbs
	if (breadcrumbs && breadcrumbs.length > 0) {
		schemas.push({
			'@context': 'https://schema.org',
			'@type': 'BreadcrumbList',
			itemListElement: breadcrumbs.map((crumb, i) => ({
				'@type': 'ListItem',
				position: i + 1,
				name: crumb.name,
				item: crumb.url,
			})),
		})
	}

	// Product schema
	if (product) {
		schemas.push({
			'@context': 'https://schema.org',
			'@type': 'Product',
			name: title,
			description: description,
			image: ogImage,
			brand: {
				'@type': 'Brand',
				name: product.brand ?? SITE_NAME,
			},
			sku: product.sku,
			offers: {
				'@type': 'Offer',
				price: product.price,
				priceCurrency: product.currency,
				availability: `https://schema.org/${product.availability}`,
				seller: {
					'@type': 'Organization',
					name: SITE_NAME,
				},
			},
		})
	}

	// Custom schema override
	if (schema) schemas.push(schema)

	return (
		<Head>
			<title>{fullTitle}</title>
			<meta name="description" content={description} />
			<link rel="canonical" href={canonical} />
			{noindex && <meta name="robots" content="noindex,follow" />}

			<meta property="og:type" content={type === 'product' ? 'product' : 'website'} />
			<meta property="og:title" content={fullTitle} />
			<meta property="og:description" content={description} />
			<meta property="og:image" content={ogImage} />
			<meta property="og:image:width" content="1200" />
			<meta property="og:image:height" content="630" />
			<meta property="og:url" content={canonical} />
			<meta property="og:site_name" content={SITE_NAME} />
			<meta property="og:locale" content="en_KE" />

			<meta name="twitter:card" content="summary_large_image" />
			<meta name="twitter:title" content={fullTitle} />
			<meta name="twitter:description" content={description} />
			<meta name="twitter:image" content={ogImage} />

			{/* ── Product-specific OG ─────────────────────────────────────── */}
			{product && (
				<>
					<meta property="product:price:amount" content={String(product.price)} />
					<meta property="product:price:currency" content={product.currency} />
				</>
			)}

			{/* ── JSON-LD structured data ──────────────────────────────────── */}
			{schemas.map((s, i) => (
				<script
					key={i}
					type="application/ld+json"
					dangerouslySetInnerHTML={{ __html: JSON.stringify(s) }}
				/>
			))}
		</Head>
	)
}
