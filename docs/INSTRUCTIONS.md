# Build brief: Ignition Index (vehicle maintenance & ownership tracker)

> The product was called **CarTrak**. It is now **Ignition Index** — two words, never
> "IgnitionIndex", never "Ignition-Index", never "CarTrak". Repo:
> `git@github.com:adevenuto/IgnitionIndex.git`.

## Current state — read this first

**Phases 0–3 are built and on `development`.** A full visual rebrand lives on
`concept_redesign` (17 commits ahead, pushed). 185 tests green.

Gate everything with `composer ci:check` — Pest, PHPStan level 7, Pint, `vp check`
(type-aware, warnings denied) and `vue-tsc`. It must be green before any commit.

### What the redesign covers
Tokens, fonts, the navy shell, the blueprint frame, the 240° gauge dial, the vehicle
detail screen, the landing page, and the garage, history, settings and notifications
screens.

**Insights and the auth screens are the last two unmigrated areas.** Insights is the
harder of the two: it is not in the design drop at all, and §2 forbids tinting charts,
so its palette is a real decision (likely semantic steel/amber/rust rather than
categorical) rather than a transcription.

Remaining cosmetic debt: a handful of `components/ui/*` primitives still write
`rounded-full`. They already render square via the utilities-layer override in
`app.css`, so this is tidying, not a defect — pay it off per primitive when one is
next touched.

### Known gaps (none are bugs)
- No **pinning UI**. `position` and `is_pinned` exist, are backfilled and render, but
  nothing lets a user change which three gauges are pinned. Suggested home for the
  toggle: the Intervals modal.
- The top-bar **search** and the rail's **Upgrade to Fleet** cell are inert — both are
  in the design, neither has a backend. Search deliberately swallows Enter.
- The landing **hero has no photo**. The design calls for a garage/engine-bay shot and
  no such asset exists in the drop, so the frame carries a large dial instead. Swapping
  in an `<img>` is a one-line change; see the comment in `Welcome.vue`.
- **Local host.** Valet derives the domain from the directory name. Once the folder is
  renamed to `ignitionindex`, `ignitionindex.test` resolves and `.env`'s `APP_URL`
  becomes correct on its own; run `php artisan config:clear` afterwards. Until then the
  app answers on `cartrak.test` and reminder emails would deep-link to a dead host.
  `DB_DATABASE=cartrak` is fine left alone — renaming it buys nothing visible.

## Design system (authoritative)
`AGENTS.md` is binding and points at `docs/design_system/IGNITION-INDEX-DESIGN-SYSTEM.md`.
**Read both before writing any UI.** The design doc wins over existing code, and
components predating it are legacy, not precedent. Do not reintroduce the CarTrak name,
the red accent, rounded corners, pill shapes, soft shadows, white floating cards or
filled icons. Take every colour, font and space from the tokens; never hard-code a hex.

The two `.dc.html` artboards are the design at full fidelity and are worth reading
directly — they carry exact values the prose does not. `ignitionindex.png` and
`ignitionindexlanding.png` are the reference renders. Where the doc and the artboard
disagree, the artboard has won every time so far.

**If a design decision is not covered, ask rather than inventing one.**

## Traps that cost real time (all fail silently, gate stays green)

Every one of these was hit during the redesign. None are caught by any check.

- **A component's `class` must be a declared prop merged with `cn()`**, never a
  fallthrough attribute. Vue *appends* a fallthrough class to the element's own, so
  the component's hard-coded utility wins and the caller's is ignored. This shipped
  once: `GaugeDial` hard-coded `h-full w-full` and every dial ignored its size.
  Follow `Card.vue` / `BlueprintFrame.vue`.
- **Every non-stock font size must be listed in `DS_FONT_SIZES`** in
  `resources/js/lib/utils.ts`. tailwind-merge reads an unknown `text-*` as a colour
  and silently drops the real colour beside it. Guarded by
  `tests/Unit/DesignTokenSyncTest.php` — keep that passing.
- **`accent` means steel, the brand.** shadcn's generic hover surface was renamed to
  `--hover-surface` because Industry uses `--color-accent` for the brand. Do not
  reintroduce `bg-accent` as a hover state.
- **`rounded-full` is not covered by the radius scale.** Tailwind has no
  `--radius-full`; it is a static `calc(infinity * 1px)`, overridden in a utilities
  layer in `app.css`.
- **Removing a token does not break anything loudly** — the class just stops
  painting. After removing any token, grep for it. This bit three times
  (`--success`, a raw `text-green-500`, `brand-subtle` across eight files).
- **`vue-tsc` does not catch a deleted `.vue` import.** After deleting a component,
  grep for its name; the type check will stay green while the build breaks.
- **`php artisan wayfinder:generate` needs `--with-form`.** The Vite plugin sets
  `formVariants: true`; the CLI does not infer it, and regenerating without the flag
  silently strips every `.form()` and breaks ~19 files.
- **`defineOptions` is compiled and cannot see setup bindings.** Breadcrumbs that
  need a prop (a vehicle name) must use Inertia's `setLayoutProps`, which resets on
  navigation. A one-entry breadcrumb renders as the current page — dead text, no
  link back.
- **A Vue `{{ }}` inside an SVG `<text>` paints nothing** (it compiles to an
  SVG-namespaced `<span>`). Gauge readouts are always sibling elements.
- **String edits against formatted files often no-op.** `vp fmt` splits long
  attributes across lines, so a single-line search string silently matches nothing.
  After any scripted edit, verify the *specific change*, not that the element exists.

## Invariants worth not relitigating

- **`GaugeStatus` keeps five cases.** `Due` vs `Overdue` drives reminder cadence
  (`SendDueReminders` transitions on `last_reminded_status`). The interface draws
  three; `GaugeStatus::display()` collapses at the presentation boundary and the
  frontend speaks only `GaugeDisplayStatus`.
- **Gauges render in `position` order, never by urgency** (design §6). A gauge that
  moves as its status changes moves under the pointer.
- **`percent` is uncapped** — 112% is a real reading. Only the arc and needle clamp.
- **The soon threshold is `config('vehicles.gauges.soon')` = 0.75.** The calibrate
  screen mirrors it in `lib/gauge.ts` because it previews an unsaved answer; the two
  are guarded by `tests/Feature/Gauges/ThresholdSyncTest.php`.
- **The app fills the viewport; only `<main>` scrolls.** The shell is `h-dvh` with
  `overflow-hidden`, the row carries `min-h-0`, and the rail is `min-h-full`. The
  1400px cap is on the vehicle page; the 1200px cap belongs only to the landing page.

## Your role
You are building a mobile-first web application that helps people track the past and present maintenance of their vehicles. Work through the phased plan at the bottom **one phase at a time**. At the end of each phase, summarize what you built, show me how to run/verify it, and wait for my go-ahead before starting the next phase. Ask clarifying questions rather than guessing. Write tests as you go.

## Tech stack (fixed)
- Backend: **Laravel** (latest LTS-ish stable)
- Frontend: **Inertia.js + Vue 3** (SPA-style, server-driven routing)
- Styling: **follow the design system at `docs/design_system`** (see above); use its tokens/components rather than ad-hoc styles. Mobile-first and responsive is a hard requirement.
- Billing: **Laravel Cashier (Stripe)** when we reach monetization
- Queue/scheduler: use Laravel's queue + scheduler for periodic jobs (recall checks, reminder dispatch)

## Product in one paragraph
A private, single-owner "garage" of vehicles. Each vehicle shows a cluster of gauges that count down toward the next service along **two axes at once — time and mileage — surfacing whichever comes first**. The user keeps it fed with fast, mobile-friendly logging (services as multi-line shop visits, fuel-ups, expenses). Reminders arrive by email + push and deep-link back into one-tap logging. Riding the same core: recall alerts, warranty countdowns, an exportable service record, and a real cost-of-ownership picture.

## Core architecture principle (read this twice)
Almost everything the user does is **an event that carries an odometer reading and a date**. Services, fuel-ups, expenses, and bare "here's my mileage" check-ins are all events on this one spine. Some events also advance a maintenance interval (and thus drive reminders); others are purely financial. But all of them feed exactly two derived things: a **mileage estimate** and a **cost ledger**. Keep this core tiny; everything else is a view onto it.

External APIs are **enrichment, never a hard dependency**. The app must be fully functional (logging, gauges, reminders via default schedules) even if every external API is down. Decode/enrich once and cache on our own records.

## Data model (guide, not gospel)
- **User** — owner; a vehicle belongs to exactly one user. No sharing/multi-user.
- **Vehicle** — belongs to User. Fields: nickname, `vin` (nullable), year, make, model, trim, engine, `decoded_specs` (cached JSON from VIN decode), and mileage-estimate fields: `last_odometer`, `last_odometer_at`, `avg_miles_per_day`.
- **ServiceType** — global catalog (oil change, tire rotation, brakes, cabin filter, registration, etc.) each with **default intervals**: optional time interval (months) and/or mileage interval. "Whichever comes first" is expressed by an item having both.
- **VehicleInterval** — per-vehicle schedule row: service type + effective time/mileage interval + `source` (`default` | `vin` | `user_override`) + `last_done_at` / `last_done_odometer`. This is where user overrides live.
- **Event** — belongs to Vehicle. `type` (`visit` | `fuel` | `expense` | `odometer`), `odometer`, `occurred_on`, `cost` (nullable), `notes`, `location`, `photo_path`.
  - **Visit** events have many **LineItems** (service type + optional cost). Saving a visit updates `last_done_*` on each referenced VehicleInterval — one save can reset several gauges.
  - **Fuel** events add `gallons`, `total_cost`, `full_tank` (bool); compute and store `mpg` from the odometer delta since the previous full-tank fuel event.
  - **Expense** events add a `category`.
- **Recall** — cached per vehicle from NHTSA (campaign, component, summary, remedy, status).
- **Subscription/plan** — later (Cashier).

### Two pieces of logic to get right
1. **Mileage estimation.** Every event with an odometer is a reading. From the two most recent readings (and date span) derive `avg_miles_per_day`; project the current odometer forward as `last_odometer + avg_miles_per_day * days_since`. Gauges use the *projected* value so they stay live between entries. Prompt for a confirmation reading only when a gauge nears a threshold.
2. **Two-axis "binding constraint."** For each active VehicleInterval: `time_progress = days_since_last_done / (time_interval_days)`; `mile_progress = (est_odometer - last_done_odometer) / mile_interval`. The gauge shows `max(time_progress, mile_progress)` (capped for display), labels which axis is binding, and is "due" when that max ≥ 1. Handle intervals that only have one axis.
Guardrail: odometer readings must be monotonic (reject a reading lower than the last; flag implausibly large jumps for confirmation), since the estimate depends on clean data.

## Design & UX principles
- **Mobile-first, thumb-friendly, fast.** The make-or-break screen is logging. The common case must be ~2–3 taps.
- **Garage grid** as the home: a card per vehicle showing its most-urgent item; tap in for the full gauge wall.
- **Gauge wall** per vehicle: each service a 240° dial counting up toward "due," coloured steel / amber / rust, sub-labeled with the binding constraint (e.g. "1,450 mi to go" or "Overdue by 1.4 mo"). Three pinned dials, the rest as a two-column list. Green is forbidden.
- **Quick-add flow:** "Log entry" in the top bar — the app's one primary action, in the chrome, not floating in the content (design §5; the old FAB and bottom nav are gone) → sheet → segmented lane (Fuel / Service / Expense / Miles). The **odometer field is pre-filled with the running estimate** ("≈47,320 — tap to adjust"); date defaults to today; cost/shop/notes/photo optional and collapsed. Fuel needs only odometer + gallons + total cost, auto-computing MPG and $/gal.
- **Service = a visit with line items:** set date/odometer/shop once, add multiple service line items.
- **Any assisted capture (later: OCR, voice) pre-fills the confirm sheet — never silently commits.**
- **Reminders deep-link into the pre-filled log flow** ("Oil due → tap → Save").
- **Onboarding populates the gauges.** Adding a car asks one required field (current odometer) then a short, skippable "quick calibrate" (roughly when did you last do oil / tires / brakes / registration), each answer visibly filling a ring. Setup *is* the wow.
- Add-a-car offers three methods: scan VIN (later phase), type VIN, or pick year/make/model. YMM-only means no engine/trim → oil interval is a best-guess default until first logged service.

## External APIs (all US; free unless noted)
- **NHTSA vPIC** — VIN decode. Free, no key. `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/{VIN}?format=json`. ~140 fields. **It's slow (2–5s)** → call only at add-time and cache; never in a hot path. Gives identity/specs, **not** maintenance schedules.
- **NHTSA recalls** — free, no key, at `api.nhtsa.gov` (by VIN, or make/model/year). Poll periodically per vehicle for recall alerts.
- **EPA FuelEconomy.gov** — free, no key, JSON/XML. MPG city/hwy/combined + annual fuel cost, back to 1984. Use to benchmark the user's real computed MPG against the sticker.
- **CarMD** (PAID, later/premium) — OEM maintenance schedules by VIN/mileage (due mileage, is_oem, recurring cycle, typical cost estimates) + warranty/TSB. This is the only paywalled data source; use it to *upgrade* our free default schedules and to power cost-anomaly flags. Has a free test tier for dev.
- Gas prices: no free real-time station-level API; not needed (fuel price comes from the user's receipt). Skip.
Historical service records don't exist in any API — "past" history is manual entry, so the backfill flow matters.

## Monetization / tiering (build in the monetization phase)
Free tier owns the habit loop; Pro sells amplifiers (which are also the features that cost us money to serve).
- **Free:** 1–2 vehicles (lean generous early), full unlimited logging, gauges, garage, reminders (email + push), default schedules + overrides, recall alerts, MPG tracking, basic cost (spend, cost-per-mile). Never paywall history length or number of log entries. Keep recall alerts free (best word-of-mouth hook).
- **Pro:** unlimited vehicles (primary entry lever), receipt OCR, voice entry, OEM-accurate CarMD schedules + cost-anomaly flags, exportable PDF service record, upcoming-cost forecast + deeper analytics.
- Consider offering the **PDF service record as a one-off purchase too** (the "I'm selling the car" moment is one-time, not subscription-shaped).

## Non-goals / explicit constraints
- No heavy dependence on VIN-driven OEM schedule automation — default templates + user override are the spine.
- No multi-user / household sharing. A vehicle has one owner.
- No purchase-price or depreciation modeling — "running costs only."
- Assume US data initially (the free APIs are US-only).

## Phased build plan
Build in this order. Each phase must be independently usable and verifiable.

**Phase 0 — Foundation.** ✅ *Done.* Laravel + Inertia + Vue scaffold. Auth (single-owner). Base mobile-first responsive layout/shell wired to the design system. *Done when:* I can register, log in, and see an empty garage shell on mobile and desktop.

**Phase 1 — Core spine: vehicles + manual logging.** ✅ *Done* (plus vehicle photos, colour identity). Data model (Vehicle, Event + line items, ServiceType, VehicleInterval). Add-a-car via **manual** entry (type VIN or pick year/make/model — no external calls yet, hardcode a small make/model list or free-text). Quick-add bottom sheet for all four event types, including visit-with-line-items. Garage grid + per-vehicle detail listing history. *Done when:* I can add cars and log services/fuel/expenses, and see them listed.

**Phase 2 — Reminder engine + gauges.** ✅ *Done.* Default schedule templates + per-vehicle overrides. Mileage estimation. Two-axis binding-constraint computation. Gauge/ring dashboard + garage overall-health rings. Onboarding "quick calibrate" so gauges start populated. Monotonic-odometer guardrails. *Done when:* gauges reflect logged data and correctly show the binding axis and "due" states.

**Phase 3 — Free API enrichment + notifications.** ✅ *Done* (NHTSA decode + recalls, EPA benchmark, queued reminders on the scheduler). NHTSA vPIC decode at add-time (cached), NHTSA recall polling + alerts, EPA MPG benchmark. Email + push notifications for due/overdue items, deep-linking into the pre-filled log flow. *Done when:* adding a real VIN auto-fills specs, recalls surface, and reminders fire and deep-link.

**Phase 4 — Cost ledger + insights.** ⬅️ *Next.* Aggregate the ledger: cost-per-mile (fuel vs maintenance split), category breakdown, MPG trend (with the diagnostic angle), and upcoming-cost forecast from the schedule. A dedicated Insights surface. *Done when:* the Insights view shows accurate numbers from logged data.

**Phase 5 — Monetization + premium gating.** Cashier/Stripe, Free vs Pro plans, vehicle-count gate, feature gating per the tiering section. Exportable PDF service record (and evaluate a one-off purchase path). *Done when:* I can subscribe, the vehicle gate and premium gates enforce correctly, and I can export a PDF record.

**Phase 6 — Advanced capture (premium polish).** VIN barcode scan (door-jamb sticker) and windshield OCR for add-a-car; receipt OCR and voice entry that **pre-fill the confirm sheet**; share-sheet entry point. CarMD integration to upgrade schedules + cost-anomaly flags. *Done when:* assisted capture pre-fills logs behind the Pro gate.

## How to work
Work one phase at a time. Before writing code for a phase, briefly restate the plan and
flag any decisions you need from me. Keep the core event-spine clean; resist
over-engineering. Prefer clarity over cleverness.

`composer ci:check` must be green before every commit. Never add Co-Authored-By or any
Claude attribution to a commit or PR.

### Picking up from here
Phases 0–3 are done; Phase 4 (Insights) is next on the product plan. Running in
parallel is the **design migration**: `concept_redesign` rebuilt the shell, the gauge
dial, the vehicle detail screen and the landing page, and the remaining screens —
Garage, History, Insights, Settings, auth — still carry old accents.

Three sensible next moves, in any order:
1. **Build the pinning UI** so the three pinned gauges can be chosen — the one piece of
   the gauge wall the schema supports but the interface does not expose.
2. **Migrate the auth screens**, which should be as mechanical as garage and settings
   were. Grep each file for `rounded-full`, `max-w-6xl`, `brand-`, `success`, `warning`
   and raw Tailwind colours as you go — dead tokens fail silently.
3. **Start Phase 4 (Insights)**, designing before building for the reason above.

A useful pattern from the migrations already done: most screens turned out to be ~80%
migrated already, because zeroing the radius and shadow scales and mapping the tokens
did the heavy lifting globally. What is left on any given screen is usually just the
places asking for a shape the system does not have — circles, pills — plus type roles
and width caps. Audit with a grep before assuming a screen needs real work.

Phase 4 is worth designing before building: the Insights surface is not in the design
drop, so it will need decisions rather than transcription — and §2 forbids tinting
charts, so the chart palette is an open question (likely semantic steel/amber/rust
rather than categorical).