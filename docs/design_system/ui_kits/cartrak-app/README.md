# CarTrak — Mobile App UI Kit

High-fidelity recreation of the CarTrak vehicle-tracking app (mobile, the primary surface). Built entirely from the design-system components; screens compose primitives, they don't reimplement them.

## Screens
- **HeroScreen.jsx** — onboarding/marketing hero. Crimson `--grad-hero`, oversized "Fast car" serif watermark, four glass `StatCard` health tiles, a white bottom sheet with the serif marketing headline and a `Get started` CTA.
- **HomeScreen.jsx** — dashboard. Header with calendar/bell `IconButton`s, the `ScoreGauge` vehicle dial, an "Add Vehicle Report" action, and a "Popular Cars" list of `VehicleCard`s.
- **ServicesScreen.jsx** — discover. Pill `Input` search, `BrandChip` carousel, and a featured-service `Card` with `Badge` metadata.
- **kit.jsx** — shared helpers: `Icon` (Lucide), `StatusBar`, `PhoneFrame`.

## Interaction (index.html)
Opens on the onboarding hero → **Get started** enters the app → bottom `BottomNav` switches Home / Reports(Services) → the center crimson **scan FAB** opens the scan bottom sheet.

## Notes / substitutions
- **Vehicle & brand imagery are placeholders.** The source shows photographic cars and third-party brand logos (Mercedes, BMW, Porsche…). Those are trademarked and can't be reproduced from memory, so cars render as a neutral Lucide `Car` glyph and brands as initial chips. Drop real photography/logos in to finish.
- Icons are **Lucide** (CDN) — see the root readme ICONOGRAPHY section.
