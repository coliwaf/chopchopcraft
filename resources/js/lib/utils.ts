import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function formatPrice(amount: number, currency = 'KES'): string {
    return `${currency} ${Number(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
}

export function formatOrderStatus(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ')
}

export const statusColors: Record<string, string> = {
    pending:    'bg-amber-100 text-amber-800',
    confirmed:  'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    shipped:    'bg-indigo-100 text-indigo-800',
    delivered:  'bg-green-100 text-green-800',
    cancelled:  'bg-red-100 text-red-800',
    refunded:   'bg-gray-100 text-gray-700',
    paid:       'bg-green-100 text-green-800',
    failed:     'bg-red-100 text-red-800',
}
