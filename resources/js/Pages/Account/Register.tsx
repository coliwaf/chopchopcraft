import React from 'react'
import { Head, Link, useForm } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Input } from '@/Components/UI'
import { PageProps } from '@/types'

export default function Register({}: PageProps) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name:  '',
        email:      '',
        phone:      '',
        password:   '',
        password_confirmation: '',
    })

    function submit(e: React.FormEvent) {
        e.preventDefault()
        post('/register')
    }

    return (
        <AppLayout>
            <Head title="Create account" />

            <div className="min-h-[70vh] flex items-center justify-center px-4 py-16">
                <div className="w-full max-w-md">
                    <div className="text-center mb-8">
                        <h1 className="font-display text-4xl text-stone-900 mb-2">Create account</h1>
                        <p className="text-stone-500">Join us to track orders and get exclusive deals.</p>
                    </div>

                    <div className="card p-8">
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <Input label="First name" value={data.first_name} onChange={e => setData('first_name', e.target.value)} error={errors.first_name} required />
                                <Input label="Last name"  value={data.last_name}  onChange={e => setData('last_name',  e.target.value)} error={errors.last_name}  required />
                            </div>
                            <Input label="Email" type="email" value={data.email} onChange={e => setData('email', e.target.value)} error={errors.email} autoComplete="email" required />
                            <Input label="Phone number" type="tel" placeholder="+254712345678" value={data.phone} onChange={e => setData('phone', e.target.value)} error={errors.phone} hint="Used for delivery updates via WhatsApp" />
                            <Input label="Password" type="password" value={data.password} onChange={e => setData('password', e.target.value)} error={errors.password} hint="Min 8 characters, with numbers and mixed case" required />
                            <Input label="Confirm password" type="password" value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} required />

                            <Button type="submit" className="w-full mt-2" size="lg" loading={processing}>
                                Create account
                            </Button>
                        </form>
                    </div>

                    <p className="text-center text-sm text-stone-500 mt-6">
                        Already have an account?{' '}
                        <Link href="/login" className="text-amber-800 hover:text-stone-900 font-medium">Sign in</Link>
                    </p>
                </div>
            </div>
        </AppLayout>
    )
}
