import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

/*
 * tailwind-merge has to be told about the design system's type scale.
 *
 * app.css registers these as font sizes, but tailwind-merge only knows
 * Tailwind's stock keys. Faced with `text-h3` it assumes a text COLOUR, so it
 * treats `text-primary-foreground text-h3` as a conflict and drops the colour —
 * which silently rendered every size="lg" button as black text on its fill.
 *
 * Registering them under font-size makes the two independent again.
 *
 * KEEP IN SYNC with the --text-* keys in resources/css/app.css. Every non-stock
 * key must appear here; `sm` and `xs` are Tailwind's own and must not. Nothing
 * checks this — not vp check, not vue-tsc — and the failure is silent at
 * runtime, so a missing entry surfaces as a colour that mysteriously does not
 * apply.
 */
export const DS_FONT_SIZES = [
    'meta',
    'body',
    'lede',
    'title',
    'h1',
    'h2',
    'h3',
    'metric',
    'readout',
] as const;

const twMerge = extendTailwindMerge({
    extend: {
        classGroups: {
            'font-size': [{ text: [...DS_FONT_SIZES] }],
        },
    },
});

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
