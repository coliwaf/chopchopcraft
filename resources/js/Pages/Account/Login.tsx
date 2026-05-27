import React, { useState } from 'react'
import { Head, Link, useForm } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Input, Flash } from '@/Components/UI'
import { PageProps } from '@/types'

interface Props extends PageProps {}

export default function Login({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email:    '',
        password: '',
        remember: false,
    })

    function submit(e: React.FormEvent) {
        e.preventDefault()
        post('/login')
    }

    return (
        <AppLayout>
            <Head title="Sign in" />

            <div className="min-h-[70vh] flex items-center justify-center px-4 py-16">
                <div className="w-full max-w-md">
                    <div className="text-center mb-8">
                        <h1 className="font-display text-4xl text-stone-900 mb-2">Welcome back</h1>
                        <p className="text-stone-500">Sign in to track your orders and manage your account.</p>
                    </div>

                    <div className="card p-8">
                        {flash?.success && <Flash message={flash.success} type="success" className="mb-6" />}
                        {flash?.error   && <Flash message={flash.error}   type="error"   className="mb-6" />}

                        <form onSubmit={submit} className="space-y-5">
                            <Input
                                label="Email address"
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                error={errors.email}
                                autoComplete="email"
                                required
                            />
                            <div>
                                <Input
                                    label="Password"
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    error={errors.password}
                                    autoComplete="current-password"
                                    required
                                />
                                <div className="mt-1.5 text-right">
                                    <a href="/forgot-password" className="text-xs text-stone-500 hover:text-stone-800">Forgot password?</a>
                                </div>
                            </div>

                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)} className="accent-amber-700" />
                                <span className="text-sm text-stone-600">Remember me</span>
                            </label>

                            <Button type="submit" className="w-full" size="lg" loading={processing}>
                                Sign in
                            </Button>
                        </form>
                    </div>

                    <p className="text-center text-sm text-stone-500 mt-6">
                        Don't have an account?{' '}
                        <Link href="/register" className="text-amber-800 hover:text-stone-900 font-medium">Create one</Link>
                    </p>
                </div>
            </div>
        </AppLayout>
    )
}
