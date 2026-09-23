# beforeafter

Frontend for **beforeafter**, an Italian nightlife/events collective. Next.js (App Router) + TypeScript + Tailwind v4 + Framer Motion + Three.js / React Three Fiber + Lenis.

```bash
npm install
npm run dev
```

## Brand

Palette and typography follow the client's real event materials (tickets, wristbands, signage, merch — see `public/brand/`): black, cream and chrome, warmed only by photography — no flat red/neon accent. Headlines and labels use **Jost** (light weight, wide uppercase tracking); the logo mark keeps its own hand-lettered "BE4FTER" geometry, rendered in a chrome gradient by default.

## Structure

- `src/components/logo` — the official mark. `BeforeAfterMark` inlines the real vector logo (`public/brand/beforeafter-logo.svg`, hand-lettered "BE4FTER" wordmark, the dripping "4" fusing before into after). `variant="chrome"` (default) fills it with the brushed-metal gradient matching the client's real materials; `variant="solid"` falls back to `currentColor` for contexts needing guaranteed contrast (e.g. the small nav mark inside a mix-blend-difference container). `BeforeAfterLogo` is the single entry point every consumer renders.
- `src/components/webgl` — real WebGL via Three.js/R3F. `GeneratedArtworkGL` replaces flat CSS gradients on posters/gallery tiles with a procedural shader (same color language) that ripples from the pointer on hover and settles at rest; falls back to `components/media/GeneratedArtwork` (CSS) when WebGL is unsupported or `prefers-reduced-motion` is set. Each Canvas lazy-mounts near the viewport to keep concurrent WebGL contexts low.
- `src/components/motion` — reusable motion primitives (`SplitLines`, `AnimatedWords`, `RevealImage`, `MagneticElement`) that implement the site's entrance/reveal vocabulary. All triggers are scroll- or pointer-driven and settle to a stable state — no ambient infinite-loop animation.
- `src/components/system` — cross-cutting runtime: `SmoothScrollProvider` (Lenis, disabled under `prefers-reduced-motion` and on coarse/touch pointers), `Cursor` (contextual, velocity-reactive, desktop-only), `DiscoveryBadge` (light, scoped-down take on gamified content-unlock — see `lib/discovery.ts`).
- `src/components/sections` — the homepage chapters (`Hero`, `NextEvent`, `MemoryGallery`, `EventArchive`, `Manifesto`, `Footer`).
- `src/lib/data/events.ts` — structured placeholder event records. Swap for real data from a future CMS/events API without touching the components that render them.

## Notes

- All content in `src/lib/data` is placeholder/structural, not real event information.
