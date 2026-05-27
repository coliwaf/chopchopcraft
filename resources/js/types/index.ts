export interface User {
    id: number
    name: string
    email: string
}

export interface Product {
    id: number
    name: string
    slug: string
    description: string | null
    long_description: string | null
    wood_type: string | null
    finish: string | null
    min_price: number
    in_stock: boolean
    is_featured: boolean
    image?: string
    images?: { url: string; thumb: string; alt: string }[]
    variants: Variant[]
    care_instructions?: string[]
    dimensions?: Record<string, string>
}

export interface Variant {
    id: number
    name: string
    sku: string
    size: string | null
    price: number
    stock_qty: number
    in_stock: boolean
    low_stock: boolean
}

export interface CartItem {
    variant_id: number
    product_name: string
    variant_name: string
    sku: string
    price: number
    qty: number
    image: string | null
}

export interface Cart {
    items: CartItem[]
    count: number
    subtotal: number
}

export interface Order {
    id: number
    order_number: string
    status: string
    payment_status: string
    payment_method: string
    subtotal: number
    discount_amount: number
    shipping_amount: number
    total: number
    tracking_number: string | null
    created_at: string
    item_count?: number
    items?: OrderItem[]
    shipping?: {
        name: string
        phone: string
        address: string
    }
    discount_code?: string | null
}

export interface OrderItem {
    product_name: string
    variant_name: string
    sku: string
    qty: number
    unit_price: number
    line_total: number
}

export interface PageProps extends Record<string, unknown> {
    auth: { user: User | null }
    cart: Cart
    flash: { success?: string; error?: string }
    app: { name: string; wa_number: string }
}

export interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}