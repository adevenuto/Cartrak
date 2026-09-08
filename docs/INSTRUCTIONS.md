# Build brief: vehicle maintenance & ownership tracker

## Design system (authoritative)
A Claude-generated design system lives at `docs/design_system`. **Read it before writing any UI, and follow it as the source of truth** for colors, typography, spacing, components, and interaction patterns. Where it conflicts with any styling guidance elsewhere in this brief, the design system wins. Build screens by composing its existing components/tokens rather than introducing new ad-hoc styles.

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
- **Garage grid** as the home: a card per vehicle showing one overall-health ring + its most-urgent item; tap in for the full gauge cluster.
- **Gauge cluster** per vehicle: each service a ring filling toward "due," colored by status (healthy/soon/overdue), sub-labeled with the binding constraint (e.g. "3,900 mi left" or "due in 8 days"). Warranty is just another ring.
- **Quick-add flow:** floating "+" → bottom sheet → segmented lane (Fuel / Service / Expense / Miles). The **odometer field is pre-filled with the running estimate** ("≈47,320 — tap to adjust"); date defaults to today; cost/shop/notes/photo optional and collapsed. Fuel needs only odometer + gallons + total cost, auto-computing MPG and $/gal.
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

**Phase 0 — Foundation.** Laravel + Inertia + Vue scaffold. Auth (single-owner). Base mobile-first responsive layout/shell wired to the design system. *Done when:* I can register, log in, and see an empty garage shell on mobile and desktop.

**Phase 1 — Core spine: vehicles + manual logging.** Data model (Vehicle, Event + line items, ServiceType, VehicleInterval). Add-a-car via **manual** entry (type VIN or pick year/make/model — no external calls yet, hardcode a small make/model list or free-text). Quick-add bottom sheet for all four event types, including visit-with-line-items. Garage grid + per-vehicle detail listing history. *Done when:* I can add cars and log services/fuel/expenses, and see them listed.

**Phase 2 — Reminder engine + gauges.** Default schedule templates + per-vehicle overrides. Mileage estimation. Two-axis binding-constraint computation. Gauge/ring dashboard + garage overall-health rings. Onboarding "quick calibrate" so gauges start populated. Monotonic-odometer guardrails. *Done when:* gauges reflect logged data and correctly show the binding axis and "due" states.

**Phase 3 — Free API enrichment + notifications.** NHTSA vPIC decode at add-time (cached), NHTSA recall polling + alerts, EPA MPG benchmark. Email + push notifications for due/overdue items, deep-linking into the pre-filled log flow. *Done when:* adding a real VIN auto-fills specs, recalls surface, and reminders fire and deep-link.

**Phase 4 — Cost ledger + insights.** Aggregate the ledger: cost-per-mile (fuel vs maintenance split), category breakdown, MPG trend (with the diagnostic angle), and upcoming-cost forecast from the schedule. A dedicated Insights surface. *Done when:* the Insights view shows accurate numbers from logged data.

**Phase 5 — Monetization + premium gating.** Cashier/Stripe, Free vs Pro plans, vehicle-count gate, feature gating per the tiering section. Exportable PDF service record (and evaluate a one-off purchase path). *Done when:* I can subscribe, the vehicle gate and premium gates enforce correctly, and I can export a PDF record.

**Phase 6 — Advanced capture (premium polish).** VIN barcode scan (door-jamb sticker) and windshield OCR for add-a-car; receipt OCR and voice entry that **pre-fill the confirm sheet**; share-sheet entry point. CarMD integration to upgrade schedules + cost-anomaly flags. *Done when:* assisted capture pre-fills logs behind the Pro gate.

## How to work
Start with Phase 0. Before writing code for each phase, briefly restate your plan for that phase and flag any decisions you need from me. Keep the core event-spine clean; resist over-engineering. Prefer clarity over cleverness.