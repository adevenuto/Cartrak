import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

/*
 * tailwind-merge has to be told about the design system's type scale.
 *
 * app.css registers --text-body / --text-title / --text-h1..h3 as font sizes,
 * but tailwind-merge only knows Tailwind's stock keys. Faced with `text-h3` it
 * assumes a text COLOUR, so it treats `text-primary-foreground text-h3` as a
 * conflict and drops the colour — which silently rendered every size="lg"
 * button as black text on crimson.
 *
 * Registering them under font-size makes the two independent again.
 */
const twMerge = extendTailwindMerge({
    extend: {
        classGroups: {
            'font-size': [{ text: ['body', 'title', 'h1', 'h2', 'h3'] }],
        },
    },
});

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
