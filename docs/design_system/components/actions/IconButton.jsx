import React from 'react';

/**
 * Circular icon button — the round bell / calendar / heart controls in headers.
 * tone: 'light' (white on gray), 'brand' (red fill), 'ghost' (transparent).
 */
export function IconButton({
  children,
  tone = 'light',
  size = 44,
  disabled = false,
  style = {},
  ...rest
}) {
  const tones = {
    light: { background: 'var(--white)', color: 'var(--ink-900)', boxShadow: 'var(--shadow-sm)' },
    brand: { background: 'var(--brand)', color: '#fff', boxShadow: 'var(--shadow-brand)' },
    ghost: { background: 'transparent', color: 'var(--ink-700)', boxShadow: 'none' },
    dark: { background: 'var(--ink-900)', color: '#fff', boxShadow: 'var(--shadow-sm)' },
  };
  const t = tones[tone] || tones.light;
  return (
    <button
      disabled={disabled}
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: size,
        height: size,
        borderRadius: 'var(--radius-circle)',
        border: 'none',
        cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.45 : 1,
        transition: 'transform var(--dur-fast) var(--ease-standard), filter var(--dur-base) var(--ease-standard)',
        ...t,
        ...style,
      }}
      onMouseDown={(e) => { if (!disabled) e.currentTarget.style.transform = 'scale(var(--press-scale))'; }}
      onMouseUp={(e) => { e.currentTarget.style.transform = 'scale(1)'; }}
      onMouseLeave={(e) => { e.currentTarget.style.transform = 'scale(1)'; }}
      {...rest}
    >
      {children}
    </button>
  );
}
