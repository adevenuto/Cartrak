# CarTrak Design System

CarTrak is an application that helps vehicle owners track mileage, maintenance, service intervals, diagnostics, and more. It is a desktop-capable product but **mobile-first** — every foundation, component, and screen in this system is designed for a phone and scales up from there.

This design system is the single source of truth for CarTrak's visual language: tokens, typography, color, reusable React components, and a full mobile UI kit.

## Sources
- `uploads/Screenshot 2026-09-03 at 11.36.13 AM.png` — a three-screen mobile mockup (onboarding hero, home dashboard, services/brands) supplied by the user as the style reference. **This screenshot is the only source.** There was no codebase, Figma file, or brand kit; all tokens and components were derived from it. Values are close visual matches, not extracted production values — treat them as a strong starting point to refine against real specs.

## Brand at a glance
- **Personality:** confident, premium-automotive, a little sporty. Crimson red + near-black on soft light gray, with occasional editorial serif for marketing moments.
- **Primary color:** crimson `#C1122F` (`--brand`).
- **Type:** Plus Jakarta Sans (UI) + Playfair Display / Lora (serif, for hero & marketing copy).
- **Logo:** **none was provided.** The brand name is set in type wherever a mark would go (see thumbnail and app hero). Do not fabricate a CarTrak logo — request the real asset.

---

## CONTENT FUNDAMENTALS
How CarTrak writes.

- **Voice:** direct, benefit-led, lightly aspirational. Marketing copy leans warm and human ("Your Car, Powered By All"); UI copy is terse and functional ("Add Vehicle Report", "Search Services").
- **Person:** speaks to the owner as **"your" / "you"** ("Your Car…", "Scan your vehicle"). Never first-person.
- **Casing:** **Title Case** for buttons, section headers, and feature names ("Popular Cars", "Full Vehicle Inspection", "Get started" is the one sentence-case CTA seen). Labels are Title Case ("Engine Health", "Tire Condition").
- **Tone examples:**
  - Hero headline: *"Your Car, Powered By All"* — aspirational, serif, two-tone (black + crimson emphasis).
  - Hero body: *"From vehicle scanning to maintenance tracking, everything managed automatically."* — plain-spoken, feature-forward, set in a serif for editorial warmth.
  - Metrics: bare and quantified — *"Vehicle Score 85/100"*, *"Target Score 100"*, *"92%"*, *"45 min"*, *"+32"*.
- **Numbers:** front-and-center and unadorned. Scores as `value/max`, health as `%`, counts as `+N`, durations as `N min`. Big bold numerals are a signature.
- **Emoji:** **not used.** Meaning is carried by line icons, never emoji.
- **Vibe:** premium car-showroom-in-your-pocket — crisp, data-rich, but calm and uncluttered.

---

## VISUAL FOUNDATIONS
The look and feel, answered concretely.

- **Color:** one dominant hue — crimson red (`#C1122F`) — on a light warm-gray canvas (`#ECEAEB`) with white cards and near-black text (`#1A1A1A`). Red is used for primary actions, active states, emphasis numerals, and the scan FAB. The device backdrop carries a pale pink tint (`#FBE9EC`). Max 1–2 background colors per screen.
- **Type:** dual system. **Plus Jakarta Sans** for all UI — bold/extrabold for headings and metrics, medium for body/labels. **Playfair Display** (serif) for display moments — the oversized "Fast car" watermark and hero headlines. **Lora** (serif) for marketing body paragraphs, which gives the onboarding screen an editorial feel distinct from the utilitarian dashboards.
- **Backgrounds:** mostly flat light-gray or white. The one signature background is the **hero gradient** (`--grad-hero`) — crimson at the top fading through pink to white at the bottom. No textures, no patterns, no photographic full-bleed backdrops (product photos sit inside cards).
- **Gradients:** used sparingly and purposefully — the hero fade, the brand-fill gradient on emphasis surfaces, and the score-arc gradient (light crimson → deep crimson across the filled gauge).
- **Corner radii:** generous and soft. Cards `~22px` (`--radius-lg`), hero/large panels `~28px`, inputs & buttons fully **pill** (`999px`), small chips `12px`. The bottom nav has large top corners (`28px`).
- **Cards:** white fill, large radius, **soft low-contrast shadow** (`--shadow-md`: `0 8px 24px rgba(26,26,26,.07)`) — no borders, no colored left-accent stripes. Cards float on the gray canvas.
- **Shadows:** soft, diffuse, low-opacity, always neutral-dark — never colored except the **crimson glow** under the primary button and scan FAB (`--shadow-brand`). Elevation is subtle; the system reads flat-ish and clean.
- **Borders:** minimal. Hairlines (`#E5E3E4`) only for dividers (spec rows) and input focus (which turns crimson). Secondary buttons use a crimson 1.5px outline.
- **Buttons:** fully pill-shaped. Primary = solid crimson + soft red glow; secondary = crimson outline on white; plus ghost and dark (ink) variants. All shrink slightly on press (`scale(0.96)`).
- **Hover / press:** hover darkens the brand (`--brand-hover` `#A70F29`); press darkens further (`--brand-press` `#880C21`) and applies the `--press-scale` shrink. On a mobile-first product, press is the primary feedback.
- **Iconography:** line icons, ~2px stroke, black or crimson. See ICONOGRAPHY.
- **Transparency & blur:** used for the **glass StatCards** floating over the hero gradient (`rgba(255,255,255,.92)` + light backdrop blur). Otherwise surfaces are opaque.
- **Animation:** restrained. Standard ease `cubic-bezier(.4,0,.2,1)` for color/opacity; a gentle spring for playful moments; fast (120ms) press feedback. No bouncy or flashy motion — the brand feels precise, like instrumentation.
- **Layout:** fixed 20px side gutters, ~430px mobile content width. Fixed header row up top; fixed bottom tab bar with a raised center FAB. Content scrolls between them. Vertical rhythm on the 4px spacing grid.
- **Imagery vibe:** product photography is clean, neutral-lit, on light/gray backgrounds — cars presented like showroom hero shots. (No real imagery is bundled; see caveats.)

---

## ICONOGRAPHY
- The source uses **outline (line) icons** with a consistent ~2px stroke, in black (default) or crimson (active/emphasis). Examples seen: calendar, bell, home, chart/reports, heart (saved), gear (settings), search, chevron, clock, plus, scan, and metric glyphs (engine/gauge, tire, battery, fuel).
- **No icon font or SVG set was provided.** This system substitutes **[Lucide](https://lucide.dev)** (v0.454, via CDN) — an outline set whose ~2px stroke and rounded joins match the reference closely. **This is a substitution; flag to the user and swap for CarTrak's real icon set if one exists.**
- Cards and UI-kit screens load Lucide from CDN and render via a small `Icon` helper (`ui_kits/cartrak-app/kit.jsx`). Components accept icon nodes as props rather than hard-coding a set, so any icon library can be dropped in.
- **Emoji:** never used as icons. **Unicode glyphs:** not used. Meaning is always carried by line icons.
- **Brand logos** (Mercedes, BMW, Porsche, etc.) in the source are third-party trademarks and are **not** bundled or recreated — they render as initial chips in the kit.

---

## Foundations → tokens
All tokens live under `tokens/`, imported by the root `styles.css` (the one file consumers link).
- `tokens/colors.css` — crimson scale, ink/neutral scale, surfaces, semantic aliases, status, gradients.
- `tokens/typography.css` — font families, weights, size scale, line-heights, letter-spacing, semantic type roles.
- `tokens/spacing.css` — 4px spacing scale + layout tokens (gutters, tap target, content width).
- `tokens/radius.css` — corner radii (xs→xl, pill, circle).
- `tokens/shadows.css` — soft elevation scale + crimson glow.
- `tokens/motion.css` — easings, durations, press-scale.
- `tokens/fonts.css` — webfont imports (Google Fonts, see FONT SUBSTITUTIONS).

### FONT SUBSTITUTIONS
No font files were provided. Nearest Google Fonts matches are used and should be confirmed:
- UI sans → **Plus Jakarta Sans**
- Display serif ("Fast car", hero headlines) → **Playfair Display**
- Marketing body serif → **Lora**

**Please share the real CarTrak font files if the brand uses specific licensed faces.**

---

## Components
Reusable React primitives in `components/<group>/`. Each has a `.jsx`, a `.d.ts` props contract, a `.prompt.md`, and a `@dsCard` demo.
- **actions/** — `Button` (pill; primary/secondary/ghost/dark), `IconButton` (circular; light/brand/ghost/dark).
- **forms/** — `Input` (pill/rounded search & text field with focus + icon slots).
- **surfaces/** — `Card` (white rounded surface, elevation scale).
- **data/** — `Badge` (spec/count/status pill), `StatCard` (metric tile, glass variant), `ScoreGauge` (segmented vehicle-score dial), `VehicleCard` (listing card), `BrandChip` (brand carousel tile).
- **navigation/** — `BottomNav` (tab bar with raised crimson scan FAB).

## UI kits
- **ui_kits/cartrak-app/** — the mobile app (primary surface): onboarding hero, home dashboard, services/brands. Interactive click-through in `index.html`. See its `README.md`.
- **ui_kits/cartrak-desktop/** — the desktop app: sidebar + top bar + two-column vehicle dashboard, same components/tokens as mobile. See its `README.md`.

## Vue
This product is built in **Vue**. `styles.css` (all tokens) is framework-agnostic — link it directly. The React components are the canonical spec for the DS tab/bundle; `guidelines/vue-usage.md` explains consuming tokens in Vue with worked SFC ports (`guidelines/Button.vue`, `guidelines/Badge.vue`).

## Guidelines (specimen cards)
`guidelines/*.card.html` — foundation cards rendered on the Design System tab (Colors, Type, Spacing).

---

## Index / manifest (root)
- `styles.css` — global entry point (@import list only). Consumers link this.
- `tokens/` — token CSS (see Foundations).
- `components/` — reusable primitives (see Components).
- `ui_kits/cartrak-app/` — mobile app UI kit.
- `guidelines/` — foundation specimen cards.
- `assets/` — logos/imagery. **Currently empty** — no logo or photography was provided.
- `thumbnail.html` — homepage tile.
- `SKILL.md` — Agent-Skill entry point.
- `readme.md` — this file.

## Intentional additions
Since no source defined a component inventory, a standard mobile set was authored, sized to what the reference screens actually use (score gauge, stat/vehicle/brand cards, bottom nav with FAB). `ScoreGauge`, `VehicleCard`, `BrandChip`, and `StatCard` are CarTrak-specific compositions drawn directly from the mockup.

## Caveats
- Built from **one screenshot** — values are visual approximations, not production-extracted.
- **No logo, no fonts, no real imagery, no icon set** were provided; brand name is set in type, Google Fonts + Lucide are substituted, imagery is placeholder. All flagged above — please supply the real assets to finish.
