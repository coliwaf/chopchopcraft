// 5-column mosaic matching the Recraft layout:
//
//  [outer-L] [inner-L-top ] [       ] [inner-R-top ] [outer-R]
//  [        ] [inner-L-bot ] [ CENTER] [inner-R-bot ] [       ]
//
// - Section is a fixed height band (~520px)
// - Outer columns bleed slightly off-screen (overflow hidden on section)
// - Center column is widest, single image spanning full height
// - Inner columns each have 2 equal-height stacked images
// - Outer columns each have 2 equal-height stacked images
// - Mobile: only center + 1 inner column each side (3 cols), outer hidden
// - Tablet: outer columns hidden, 4 cols visible
// - Desktop: all 5 columns

interface MosaicImage { src: string; alt: string }

interface Props {
    images:    MosaicImage[]  // needs 11 images: [0-1 outer-L] [2-3 inner-L] [4 center] [5-6 inner-R] [7-8 outer-R] + [9-10 spare]
    title?:    string
    subtitle?: string
}

const PH = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 4 3%22%3E%3Crect fill=%22%2344403c%22 width=%224%22 height=%223%22/%3E%3C%2Fsvg%3E'

function pad(arr: MosaicImage[], n: number): MosaicImage[] {
    const out = [...arr]
    while (out.length < n) out.push({ src: PH, alt: '' })
    return out
}

function Img({ src, alt }: MosaicImage) {
    return (
        <img
            src={src} alt={alt} loading="lazy"
            className="w-full object-cover transition-transform
                       duration-500 hover:scale-105"
        />
    )
}

export default function MosaicGallery({ images = [], title, subtitle }: Props) {
    const imgs = pad(images, 9)

    // image index map:
    // 0,1 → outer-left  top/bottom
    // 2,3 → inner-left  top/bottom
    // 4   → center
    // 5,6 → inner-right top/bottom
    // 7,8 → outer-right top/bottom

    return (
        <section className="py-14 md:py-20 overflow-hidden">

            {/* Optional heading */}
            {(title || subtitle) && (
                <div className="text-center mb-10 px-4">
                    {title    && <h2 className="text-3xl md:text-4xl font-bold mb-3">{title}</h2>}
                    {subtitle && <p className="max-w-md mx-auto text-sm">{subtitle}</p>}
                </div>
            )}

            {/*
                The mosaic band — fixed height, 5 columns.
                Outer columns use negative margin to bleed ~30px off each edge.
                gap-1 between all columns.
            */}
            <div
                className="flex gap-1"
                style={{
                    height: 'clamp(320px, 42vw, 560px)',
                    marginLeft:  '-2%',
                    marginRight: '-2%',
                }}
            >

                {/* ── Outer left — 2 rows, bleeds off left edge ─────────────── */}
                <div className="hidden lg:flex flex-col gap-1 flex-[1.1] shrink-0">
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[0]} />
                    </div>
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[1]} />
                    </div>
                </div>

                {/* ── Inner left — 2 rows ───────────────────────────────────── */}
                <div className="flex flex-col gap-1 flex-[1.2] shrink-0">
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[2]} />
                    </div>
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[3]} />
                    </div>
                </div>

                {/* ── Center — single tall image, widest column ─────────────── */}
                <div className="flex-[1.3] overflow-hidden rounded-sm shrink-0 align-middle">
                    <Img {...imgs[4]} />
                </div>

                {/* ── Inner right — 2 rows ──────────────────────────────────── */}
                <div className="flex flex-col gap-1 flex-[1.2] shrink-0">
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[5]} />
                    </div>
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[6]} />
                    </div>
                </div>

                {/* ── Outer right — 2 rows, bleeds off right edge ──────────── */}
                <div className="hidden lg:flex flex-col gap-1 flex-[1.1] shrink-0">
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[7]} />
                    </div>
                    <div className="flex-1 overflow-hidden rounded-sm">
                        <Img {...imgs[8]} />
                    </div>
                </div>

            </div>
        </section>
    )
}
