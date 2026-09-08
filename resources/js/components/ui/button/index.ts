import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

/*
 * LOCAL PATCH — restyled to the CarTrak design system (docs/design_system).
 *
 * Every shadcn variant NAME is preserved so no consuming page changes; each maps
 * onto a design-system variant instead:
 *
 *   default   -> DS "primary"   (crimson fill + brand glow)
 *   outline   -> DS "secondary" (crimson outline)
 *   ghost     -> DS "ghost"
 *   secondary -> DS "dark"      (ink fill)
 *   surface   -> DS IconButton tone="light"  (added)
 *   link      -> no DS equivalent; left as shadcn shipped it
 *
 * The DS spec is entirely visual (pill, crimson, glow, press-scale), so it fits
 * cva cleanly; the behavioural parts shadcn provides (as-child, focus-visible,
 * aria-invalid, svg auto-sizing) are deliberately kept.
 *
 * Two DS-mandated changes worth knowing about:
 *   - every button is fully pill (--radius-pill), not rounded-md
 *   - default height is 44px (--tap-min), up from 36px, which re-rhythms forms
 *
 * A future `npx shadcn-vue add button` will revert this file.
 */
export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full text-title font-bold transition-all duration-[var(--dur-fast)] ease-standard active:scale-[var(--press-scale)] disabled:pointer-events-none disabled:opacity-45 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground shadow-brand hover:bg-[var(--brand-hover)] active:bg-[var(--brand-press)]",
        destructive:
          "bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40",
        outline:
          "border-[1.5px] border-primary bg-transparent text-primary hover:bg-brand-subtle",
        secondary:
          "bg-[var(--ink-900)] text-white shadow-sm hover:bg-[var(--ink-800)] dark:bg-[var(--surface-raised)] dark:hover:bg-[var(--ink-800)]",
        ghost:
          "text-brand-on-subtle hover:bg-brand-subtle",
        surface:
          "bg-card text-foreground shadow-sm hover:bg-accent",
        link: "text-brand-on-subtle underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-11 px-[22px] has-[>svg]:px-4",
        "sm": "h-9 gap-1.5 px-4 text-sm has-[>svg]:px-3",
        "lg": "h-14 px-[26px] text-h3 has-[>svg]:px-5",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
        "icon-tap": "size-11",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
