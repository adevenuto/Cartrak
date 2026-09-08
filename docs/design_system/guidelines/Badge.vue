<script setup>
// Mirrors components/data/Badge.jsx — small pill label.
defineProps({
  tone: { type: String, default: 'neutral' }, // neutral | brand | brand-soft | dark | outline
  size: { type: String, default: 'md' },       // sm | md
})
const tones = {
  neutral:      { background: 'var(--ink-100)', color: 'var(--ink-700)' },
  brand:        { background: 'var(--brand)', color: '#fff' },
  'brand-soft': { background: 'var(--red-50)', color: 'var(--brand)' },
  dark:         { background: 'var(--ink-900)', color: '#fff' },
  outline:      { background: 'transparent', color: 'var(--ink-600)', boxShadow: 'inset 0 0 0 1px var(--border-subtle)' },
}
const sizes = {
  sm: { padding: '3px 9px', font: 'var(--fs-xs)' },
  md: { padding: '5px 12px', font: 'var(--fs-sm)' },
}
</script>

<template>
  <span
    :style="{
      display:'inline-flex', alignItems:'center', gap:'5px', whiteSpace:'nowrap',
      padding: sizes[size].padding,
      font: `var(--fw-semibold) ${sizes[size].font}/1 var(--font-sans)`,
      borderRadius:'var(--radius-pill)',
      ...tones[tone],
    }">
    <slot name="left" />
    <slot />
  </span>
</template>
