import React from 'react';

/**
 * Small pill label / tag. Used for spec chips (Automatic, 5 seats), counts (+32),
 * durations (45 min) and status. tone: 'neutral' | 'brand' | 'brand-soft' | 'dark'.
 */
export function Badge({
  children,
  tone = 'neutral',
  size = 'md',
  leftIcon = null,
  style = {},
  ...rest
}) {
  const tones = {
    neutral: { background: 'var(--ink-100)', color: 'var(--ink-700)' },
    brand: { background: 'var(--brand)', color: '#fff' },
    'brand-soft': { background: 'var(--red-50)', color: 'var(--brand)' },
    dark: { background: 'var(--ink-900)', color: '#fff' },
    outline: { background: 'transparent', color: 'var(--ink-600)', boxShadow: 'inset 0 0 0 1px var(--border-subtle)' },
  };
  const sizes = {
    sm: { padding: '3px 9px', font: 'var(--fs-xs)' },
    md: { padding: '5px 12px', font: 'var(--fs-sm)' },
  };
  const t = tones[tone] || tones.neutral;
  const s = sizes[size] || sizes.md;
  return (
    <span
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '5px',
        padding: s.padding,
        font: `var(--fw-semibold) ${s.font}/1 var(--font-sans)`,
        borderRadius: 'var(--radius-pill)',
        whiteSpace: 'nowrap',
        ...t,
        ...style,
      }}
      {...rest}
    >
      {leftIcon}
      {children}
    </span>
  );
}
