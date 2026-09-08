# CarTrak — Desktop UI Kit

Desktop recreation of CarTrak. The product is mobile-first; this is the responsive-up view for the desktop app, built from the **same** design-system components and tokens as the mobile kit (no new visual language).

## Layout
- **Sidebar.jsx** — fixed 248px left rail: CarTrak wordmark + car mark, primary nav (Dashboard / Reports / Services / Saved / Settings), and a crimson "Scan Vehicle" CTA pinned to the bottom.
- **TopBar.jsx** — greeting + date, a pill `Input` search, notification `IconButton`, and a profile avatar.
- **Dashboard.jsx** — two-column content: left = the `ScoreGauge` hero card, four `StatCard` health metrics, and a "Popular Cars" grid of `VehicleCard`s; right = featured-service `Card` and a `Brands` grid of `BrandChip`s.
- **kit.jsx** — shared `Icon` (Lucide) helper.

## Interaction (index.html)
Sidebar nav switches the active item; the **Scan Vehicle** button opens a centered scan modal.

## Notes
- Mirrors the mobile kit's content and components exactly — desktop only changes the layout (sidebar + multi-column grid), not the styling.
- Vehicle/brand imagery are placeholders (Lucide `Car` glyph, initial chips) for the same trademark/asset reasons noted in the mobile kit. Drop in real photography/logos to finish.
