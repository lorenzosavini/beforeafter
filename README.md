# beforeafter

Frontend for **beforeafter**, an Italian nightlife/events collective. Next.js (App Router) + TypeScript + Tailwind v4 + Framer Motion + Lenis.

```bash
npm install
npm run dev
```

## Structure

- `src/components/logo` — the official mark. `BeforeAfterMark` inlines the real vector logo (`public/brand/beforeafter-logo.svg`, hand-lettered "BE4FTER" wordmark) with `fill="currentColor"` so it themes via CSS and stays available for path-level animation. `BeforeAfterLogo` (static) and `BeforeAfterLogoReveal` (hero entrance wipe) both render it — every consumer goes through these two rather than touching the SVG directly.
- `src/components/motion` — reusable motion primitives (`SplitLines`, `AnimatedWords`, `RevealImage`, `MagneticElement`) that implement the site's entrance/reveal vocabulary. All triggers are scroll- or pointer-driven and settle to a stable state — no ambient infinite-loop animation.
- `src/components/system` — cross-cutting runtime: `SmoothScrollProvider` (Lenis, disabled under `prefers-reduced-motion` and on coarse/touch pointers) and `Cursor` (contextual desktop-only cursor, disabled on touch/reduced motion).
- `src/components/sections` — the homepage chapters (`Hero`, `NextEvent`, `MemoryGallery`, `EventArchive`, `Manifesto`, `Footer`), each owning its own background tone as the page moves from day (paper) to night and back.
- `src/lib/data/events.ts` — structured placeholder event records. Swap for real data from a future CMS/events API without touching the components that render them.
- `src/components/media/GeneratedArtwork.tsx` — deterministic, seed-driven generated compositions standing in for real event photography until real assets exist.

## Notes

- All content in `src/lib/data` is placeholder/structural, not real event information.
