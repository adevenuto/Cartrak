# Using CarTrak in Vue

The **design foundation is framework-agnostic**. Everything that defines the CarTrak look — colors, type, spacing, radius, shadows, motion — lives in `styles.css` as CSS custom properties. Link that one file and you have the full token system in any Vue app:

```js
// main.js
import '@cartrak/design-system/styles.css'
```

```vue
<button class="ct-btn">Rent Now</button>
<style>
.ct-btn{
  font: var(--fw-bold) var(--fs-title)/1 var(--font-sans);
  color: var(--on-brand);
  background: var(--brand);
  border: 1.5px solid var(--brand);
  border-radius: var(--radius-pill);
  padding: 14px 22px;
  box-shadow: var(--shadow-brand);
}
.ct-btn:hover{ background: var(--brand-hover); }
.ct-btn:active{ background: var(--brand-press); transform: scale(var(--press-scale)); }
</style>
```

## Components
The `.jsx` components under `components/` are the **canonical spec** (props, variants, behavior — see each `*.prompt.md` / `*.d.ts`). They are React because the Design System tab compiles a React bundle, but each one is a thin, token-driven wrapper that ports to a Vue SFC almost line-for-line. Two worked ports live alongside this guide:

- `Button.vue` — mirrors `components/actions/Button.jsx`
- `Badge.vue` — mirrors `components/data/Badge.jsx`

Use them as the pattern for the rest. The prop names, variants, and token references match the React versions exactly, so a design built against the React cards translates directly.

## Porting checklist (React → Vue)
- `style={{ }}` object → `:style="{ }"` (same camelCased keys) or a scoped `<style>` block with the same `var(--…)` references.
- `variant` / `size` / `tone` props → Vue `props` with the same string unions; map to a token lookup object exactly as the JSX does.
- `leftIcon` / `rightIcon` / `children` → named + default `<slot>`s.
- Press feedback (`scale(0.96)`) → `:active` in a `<style>` block (cleaner than JS handlers in Vue).
- Icons: the React kit uses **Lucide** via CDN; in Vue use [`lucide-vue-next`](https://lucide.dev/guide/packages/lucide-vue-next) — same glyph names.

Want the complete Vue SFC set (all 10 components) generated? Say the word and I'll add a `vue/` folder mirroring `components/`.
