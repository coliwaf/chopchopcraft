import React from 'react'
import { Head, useForm } from '@inertiajs/react'
import AppLayout from '@/Components/Layout/AppLayout'
import { Button, Input, Flash } from '@/Components/UI'
import { PageProps } from '@/types'
import AccountLayout from './AccountLayout'

interface Props extends PageProps {
    profile: {
        name: string; email: string; phone: string | null
        address_line1: string | null; address_line2: string | null
        city: string | null; county: string | null; postal_code: string | null
    }
}

export default function Profile({ profile, flash }: Props) {
    const nameParts = profile.name?.split(' ') ?? ['', '']

    const profileForm = useForm({
        first_name:    nameParts[0] ?? '',
        last_name:     nameParts.slice(1).join(' ') ?? '',
        phone:         profile.phone ?? '',
        address_line1: profile.address_line1 ?? '',
        address_line2: profile.address_line2 ?? '',
        city:          profile.city ?? '',
        county:        profile.county ?? '',
        postal_code:   profile.postal_code ?? '',
    })

    const passwordForm = useForm({
        current_password: '',
        password:         '',
        password_confirmation: '',
    })

    return (
        <AppLayout>
            <Head title="My Profile" />
            <AccountLayout active="profile">
                <h2 className="font-display text-2xl mb-6">Profile & address</h2>

                {flash?.success && <Flash message={flash.success} type="success" className="mb-6" />}
                {flash?.error   && <Flash message={flash.error}   type="error"   className="mb-6" />}

                {/* Profile form */}
                <form
                    onSubmit={e => { e.preventDefault(); profileForm.put('/account/profile') }}
                    className="card p-6 mb-6"
                >
                    <h3 className="font-medium text-stone-900 mb-5">Personal information</h3>
                    <div className="grid grid-cols-2 gap-4">
                        <Input label="First name" value={profileForm.data.first_name} onChange={e => profileForm.setData('first_name', e.target.value)} error={profileForm.errors.first_name} required />
                        <Input label="Last name"  value={profileForm.data.last_name}  onChange={e => profileForm.setData('last_name',  e.target.value)} error={profileForm.errors.last_name}  required />
                        <Input label="Phone" type="tel" value={profileForm.data.phone} onChange={e => profileForm.setData('phone', e.target.value)} error={profileForm.errors.phone} className="col-span-2" />
                        <Input label="Address" value={profileForm.data.address_line1} onChange={e => profileForm.setData('address_line1', e.target.value)} error={profileForm.errors.address_line1} className="col-span-2" />
                        <Input label="Apartment / floor" value={profileForm.data.address_line2} onChange={e => profileForm.setData('address_line2', e.target.value)} className="col-span-2" />
                        <Input label="City" value={profileForm.data.city} onChange={e => profileForm.setData('city', e.target.value)} />
                        <Input label="County" value={profileForm.data.county} onChange={e => profileForm.setData('county', e.target.value)} />
                        <Input label="Postal code" value={profileForm.data.postal_code} onChange={e => profileForm.setData('postal_code', e.target.value)} />
                    </div>
                    <div className="mt-5">
                        <Button type="submit" loading={profileForm.processing}>Save changes</Button>
                    </div>
                </form>

                {/* Password form */}
                <form
                    onSubmit={e => { e.preventDefault(); passwordForm.put('/account/password', { onSuccess: () => passwordForm.reset() }) }}
                    className="card p-6"
                >
                    <h3 className="font-medium text-stone-900 mb-5">Change password</h3>
                    <div className="space-y-4 max-w-sm">
                        <Input label="Current password" type="password" value={passwordForm.data.current_password} onChange={e => passwordForm.setData('current_password', e.target.value)} error={passwordForm.errors.current_password} required />
                        <Input label="New password" type="password" value={passwordForm.data.password} onChange={e => passwordForm.setData('password', e.target.value)} error={passwordForm.errors.password} hint="Min 8 characters, mixed case + numbers" required />
                        <Input label="Confirm new password" type="password" value={passwordForm.data.password_confirmation} onChange={e => passwordForm.setData('password_confirmation', e.target.value)} error={passwordForm.errors.password_confirmation} required />
                    </div>
                    <div className="mt-5">
                        <Button type="submit" loading={passwordForm.processing}>Update password</Button>
                    </div>
                </form>
            </AccountLayout>
        </AppLayout>
    )
}
