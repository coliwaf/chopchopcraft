// resources/js/Components/Shop/MosaicGallery.tsx
//
// Image index map (13 total):
//  L1–L7  : left column   (0–6)
//  C1     : center        (7)
//  R1–R7  : right column  (8–14)
//
// Alignment rules:
//  Top rows    → images bottom-aligned (object-position: bottom)
//  Bottom rows → images top-aligned    (object-position: top)
//  Center      → vertically centered, natural aspect ratio, full width

interface MosaicImage { src: string; alt: string }
interface Props {
    images:    MosaicImage[]
    title?:    string
    subtitle?: string
}

const PH = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 4 3%22%3E%3Crect fill=%22%2344403c%22 width=%224%22 height=%223%22/%3E%3C%2Fsvg%3E'

function pad(a: MosaicImage[], n: number): MosaicImage[] {
    const o = [...a]
    while (o.length < n) o.push({ src: PH, alt: '' })
    return o
}

// pos: 'top' | 'bottom' | 'center'
function Img({ src, alt, pos = 'center' }: MosaicImage & { pos?: 'top' | 'bottom' | 'center' }) {
    const position = pos === 'top' ? 'top' : pos === 'bottom' ? 'bottom' : 'center'
    return (
        <img
            src={src} alt={alt} loading="lazy"
            className="w-full h-full object-cover hover:scale-105
                       transition-transform duration-500"
            style={{ objectPosition: position }}
        />
    )
}

// A cell that fills its parent flex space
function Cell({
    img, pos, className = '', style = {},
}: {
    img: MosaicImage
    pos?: 'top' | 'bottom' | 'center'
    className?: string
    style?: React.CSSProperties
}) {
    return (
        <div
            className={`overflow-hidden rounded-lg bg-stone-800 ${className}`}
            style={style}
        >
            <Img {...img} pos={pos} />
        </div>
    )
}

export default function MosaicGallery({ images = [], title, subtitle }: Props) {
    const imgs = pad(images, 15)

    // Named slots for readability
    const [L1,L2,L3,L4,L5,L6,L7, C1, R1,R2,R3,R4,R5,R6,R7] = imgs

    // Section height — fixed, scales with viewport
    const H = 'clamp(580px, 65vw, 780px)'

    return (
        <section className="overflow-hidden py-14 md:py-20">

            {/* {(title || subtitle) && (
                <div className="text-center mb-10 px-4">
                    {title    && <h2 className="text-3xl md:text-4xl font-bold text-white mb-3">{title}</h2>}
                    {subtitle && <p className="text-stone-400 max-w-md mx-auto text-sm leading-relaxed">{subtitle}</p>}
                </div>
            )} */}

            <div className="flex gap-2 px-3 md:px-6" style={{ height: H }}>

                {/* ══════════════════════════════════════════════════════
                    LEFT COLUMN  flex:4
                    ══════════════════════════════════════════════════════ */}
                <div className="flex flex-col gap-2" style={{ flex: 4 }}>

                    {/* Top row — 1/3 height — 3 cols 40/35/25
                        Images: bottom-aligned (they "sit" at the bottom of the row) */}
                    <div className="flex gap-2" style={{ flex: 1 }}>
                        <Cell img={L1} pos="bottom" style={{ flex: 4 }} />
                        <Cell img={L2} pos="bottom" style={{ flex: 3.5 }} />
                        <Cell img={L3} pos="bottom" style={{ flex: 2.5 }} />
                    </div>

                    {/* Bottom row — 2/3 height — 3 cols 25/35/40, middle=2 stacked
                        Images: top-aligned */}
                    <div className="flex gap-2" style={{ flex: 2 }}>
                        <Cell img={L4} pos="top" style={{ flex: 2.5 }} />

                        {/* Middle col — 2 images stacked */}
                        <div className="flex flex-col gap-2" style={{ flex: 3.5 }}>
                            <Cell img={L5} pos="top" className="flex-1" />
                            <Cell img={L6} pos="top" className="flex-1" />
                        </div>

                        <Cell img={L7} pos="top" style={{ flex: 4 }} />
                    </div>
                </div>

                {/* ══════════════════════════════════════════════════════
                    CENTER COLUMN  flex:1.8
                    Single image — full width, natural aspect ratio,
                    vertically centered in the column.
                    ══════════════════════════════════════════════════════ */}
                <div
                    className="flex items-center justify-center"
                    style={{ flex: 1.8 }}
                >
                    <div className="w-full rounded-xl overflow-hidden bg-stone-800">
                        <img
                            src={C1.src}
                            alt={C1.alt}
                            loading="eager"
                            className="w-full h-auto block hover:scale-105
                                       transition-transform duration-500"
                            style={{ objectFit: 'cover' }}
                        />
                    </div>
                </div>

                {/* ══════════════════════════════════════════════════════
                    RIGHT COLUMN  flex:4
                    Two child columns: left 30%, right 70%
                    ══════════════════════════════════════════════════════ */}
                <div className="hidden md:flex gap-1 flex-[1.1] shrink-0" style={{ flex: 4 }}>

                    {/* Left child col — 30% width
                        Two stacked images: top 40% height, bottom 60% height */}
                    <div className="flex flex-col gap-2" style={{ flex: 3 }}>
                        <Cell img={R1} style={{ flex: 4 }} />
                        <Cell img={R2} style={{ flex: 6 }} />
                    </div>

                    {/* Right child col — 70% width
                        Two rows */}
                    <div className="flex flex-col gap-2" style={{ flex: 7 }}>

                        {/* Top row — 40% + 60% cols
                            Col1: single image, bottom-aligned
                            Col2: 2 stacked images, both bottom-aligned */}
                        <div className="flex gap-2" style={{ flex: 1 }}>
                            <Cell img={R3} pos="bottom" style={{ flex: 4 }} />

                            {/* 2 stacked images */}
                            <div className="flex flex-col gap-2" style={{ flex: 6 }}>
                                <Cell img={R4} pos="bottom" className="flex-1" />
                                <Cell img={R5} pos="bottom" className="flex-1" />
                            </div>
                        </div>

                        {/* Bottom row — 30% + 70% cols
                            Both images top-aligned, natural height (don't stretch) */}
                        <div className="flex gap-2 items-start" style={{ flex: 1 }}>
                            <Cell
                                img={R6}
                                pos="top"
                                style={{ flex: 3, alignSelf: 'flex-start', height: '80%' }}
                            />
                            <Cell
                                img={R7}
                                pos="top"
                                style={{ flex: 7, alignSelf: 'flex-start', height: '80%' }}
                            />
                        </div>

                    </div>
                </div>

            </div>
        </section>
    )
}
