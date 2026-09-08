<script setup>
// Mirrors components/actions/Button.jsx — CarTrak pill button.
const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | ghost | dark
  size: { type: String, default: 'md' },          // sm | md | lg
  block: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const sizes = {
  sm: { padding: '9px 16px', font: 'var(--fs-sm)', gap: '6px' },
  md: { padding: '14px 22px', font: 'var(--fs-title)', gap: '8px' },
  lg: { padding: '17px 26px', font: 'var(--fs-h3)', gap: '10px' },
}
const variants = {
  primary:   { background: 'var(--brand)', color: 'var(--on-brand)', border: '1.5px solid var(--brand)', boxShadow: 'var(--shadow-brand)' },
  secondary: { background: 'transparent', color: 'var(--brand)', border: '1.5px solid var(--brand)', boxShadow: 'none' },
  ghost:     { background: 'transparent', color: 'var(--brand)', border: '1.5px solid transparent', boxShadow: 'none' },
  dark:      { background: 'var(--ink-900)', color: '#fff', border: '1.5px solid var(--ink-900)', boxShadow: 'var(--shadow-sm)' },
}
</script>

<template>
  <button
    class="ct-btn"
    :disabled="disabled"
    :style="{
      display: block ? 'flex' : 'inline-flex',
      width: block ? '100%' : 'auto',
      gap: sizes[size].gap,
      padding: sizes[size].padding,
      font: `var(--fw-bold) ${sizes[size].font}/1 var(--font-sans)`,
      opacity: disabled ? 0.45 : 1,
      ...variants[variant],
    }">
    <slot name="left" />
    <slot />
    <slot name="right" />
  </button>
</template>

<style scoped>
.ct-btn{
  align-items:center; justify-content:center;
  border-radius:var(--radius-pill); cursor:pointer;
  transition:transform var(--dur-fast) var(--ease-standard),
             background var(--dur-base) var(--ease-standard),
             filter var(--dur-base) var(--ease-standard);
}
.ct-btn:disabled{ cursor:not-allowed; }
.ct-btn:not(:disabled):active{ transform:scale(var(--press-scale)); }
</style>
