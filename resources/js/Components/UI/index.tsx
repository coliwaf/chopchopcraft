// resources/js/Components/UI/index.tsx
// Lightweight reusable primitives — no external lib needed

import React from 'react'
import { clsx } from 'clsx'

// ─── Button ──────────────────────────────────────────────────────────────────
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'whatsapp' | 'ghost' | 'danger'
    size?: 'sm' | 'md' | 'lg'
    loading?: boolean
}

export function Button({
    variant = 'primary',
    size = 'md',
    loading,
    children,
    className,
    disabled,
    ...props
}: ButtonProps) {
    const base = 'inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed'
    const variants = {
        primary:   'bg-stone-900 text-stone-50 hover:bg-amber-800',
        secondary: 'border border-stone-300 text-stone-700 hover:border-stone-500 hover:text-stone-900',
        whatsapp:  'bg-green-600 text-white hover:bg-green-700',
        ghost:     'text-stone-600 hover:text-stone-900 hover:bg-stone-100',
        danger:    'bg-red-600 text-white hover:bg-red-700',
    }
    const sizes = { sm: 'px-4 py-2 text-xs', md: 'px-6 py-3 text-sm', lg: 'px-8 py-4 text-base' }

    return (
        <button
            className={clsx(base, variants[variant], sizes[size], className)}
            disabled={disabled || loading}
            {...props}
        >
            {loading && (
                <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            )}
            {children}
        </button>
    )
}

// ─── Input ───────────────────────────────────────────────────────────────────
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label?: string
    error?: string
    hint?: string
}

export function Input({ label, error, hint, className, id, ...props }: InputProps) {
    const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-')
    return (
        <div className="w-full">
            {label && <label htmlFor={inputId} className="label">{label}</label>}
            <input
                id={inputId}
                className={clsx('input', error && 'input-error', className)}
                {...props}
            />
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
            {hint && !error && <p className="mt-1 text-xs text-stone-400">{hint}</p>}
        </div>
    )
}

// ─── Select ──────────────────────────────────────────────────────────────────
interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
    label?: string
    error?: string
    options: { value: string; label: string }[]
}

export function Select({ label, error, options, className, id, ...props }: SelectProps) {
    const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-')
    return (
        <div className="w-full">
            {label && <label htmlFor={inputId} className="label">{label}</label>}
            <select
                id={inputId}
                className={clsx('input appearance-none', error && 'input-error', className)}
                {...props}
            >
                {options.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </div>
    )
}

// ─── Badge ───────────────────────────────────────────────────────────────────
interface BadgeProps {
    children: React.ReactNode
    variant?: 'wood' | 'stock' | 'low-stock' | 'out' | 'default'
    className?: string
}

export function Badge({ children, variant = 'default', className }: BadgeProps) {
    const variants = {
        wood:        'bg-amber-100 text-amber-800',
        stock:       'bg-green-100 text-green-800',
        'low-stock': 'bg-orange-100 text-orange-800',
        out:         'bg-red-100 text-red-700',
        default:     'bg-stone-100 text-stone-700',
    }
    return (
        <span className={clsx('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', variants[variant], className)}>
            {children}
        </span>
    )
}

// ─── Price ───────────────────────────────────────────────────────────────────
export function Price({ amount, className }: { amount: number; className?: string }) {
    return (
        <span className={className}>
            KES {amount.toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}
        </span>
    )
}

// ─── Divider ─────────────────────────────────────────────────────────────────
export function Divider({ className }: { className?: string }) {
    return <hr className={clsx('border-stone-100', className)} />
}

// ─── Alert flash ─────────────────────────────────────────────────────────────
export function Flash({ message, type = 'success' }: { message: string; type?: 'success' | 'error' }) {
    const styles = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error:   'bg-red-50 border-red-200 text-red-800',
    }
    return (
        <div className={clsx('border rounded-xl px-4 py-3 text-sm', styles[type])}>
            {message}
        </div>
    )
}
