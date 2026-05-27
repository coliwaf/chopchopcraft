import { PropsWithChildren } from 'react'
import { usePage } from '@inertiajs/react'
import Navbar from './Navbar'
import Footer from './Footer'
import { PageProps } from '@/types'


interface Props extends PropsWithChildren {
    // Pass a hero section as a ReactNode — it renders INSIDE the background
    // wrapper, directly below the transparent navbar, so the bg image spans both.
    hero?: React.ReactNode

    // Optional background image URL for the hero wrapper.
    // If not provided, the navbar renders in its normal solid white style.
    heroBg?: string
}


export default function AppLayout({ children, hero, heroBg }: Props) {
    const { flash } = usePage<PageProps>().props

    return (
        <div className="min-h-screen flex flex-col">
            {/* ── Hero wrapper — contains navbar + hero section over one bg image ── */}
            {heroBg ? (
                <div
                    className="relative bg-cover bg-center bg-no-repeat"
                    style={{ backgroundImage: `url('${heroBg}')` }}
                >
                    {/* Dark overlay so text stays readable over any photo */}
                    <div className="absolute inset-0 bg-black/40" />

                    {/* Navbar sits inside — transparent over the image */}
                    <div className="relative z-10">
                        <Navbar transparent />
                    </div>

                    {/* Hero content */}
                    {hero && (
                        <div className="relative z-10">
                            {hero}
                        </div>
                    )}
                </div>
            ) : (
                /* Normal solid navbar when no hero background */
                <Navbar />
            )}


            {/* Flash messages */}
            {(flash?.success || flash?.error) && (
                <div className={`px-4 py-3 text-sm text-center font-medium ${
                    flash.success ? 'bg-green-50 text-green-800 border-b border-green-200'
                                  : 'bg-red-50 text-red-800 border-b border-red-200'
                }`}>
                    {flash.success || flash.error}
                </div>
            )}

            <main className="flex-1">
                {children}
            </main>

            <Footer />
        </div>
    )
}
