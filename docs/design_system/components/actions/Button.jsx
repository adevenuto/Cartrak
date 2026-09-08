import React from 'react';

/**
 * CarTrak primary action button. Pill-shaped, red fill by default.
 * Variants: primary (red fill), secondary (red outline), ghost (text only), dark (ink fill).
 */
export function Button({
  children,
  variant = 'primary',
  size = 'md',
  block = false,
  disabled = false,
  leftIcon = null,
  rightIcon = null,
  style = {},
  ...rest
}) {
  const sizes = {
    sm: { padding: '9px 16px', font: 'var(--fs-sm)', gap: '6px' },
    md: { padding: '14px 22px', font: 'var(--fs-title)', gap: '8px' },
    lg: { padding: '17px 26px', font: 'var(--fs-h3)', gap: '10px' },
  };
  const s = sizes[size] || sizes.md;

  const variants = {
    primary: { background: 'var(--brand)', color: 'var(--on-brand)', border: '1.5px solid var(--brand)', boxShadow: 'var(--shadow-brand)' },
    secondary: { background: 'transparent', color: 'var(--brand)', border: '1.5px solid var(--brand)', boxShadow: 'none' },
    ghost: { background: 'transparent', color: 'var(--brand)', border: '1.5px solid transparent', boxShadow: 'none' },
    dark: { background: 'var(--ink-900)', color: '#fff', border: '1.5px solid var(--ink-900)', boxShadow: 'var(--shadow-sm)' },
  };
  const v = variants[variant] || variants.primary;

  return (
    <button
      disabled={disabled}
      style={{
        display: block ? 'flex' : 'inline-flex',
        width: block ? '100%' : 'auto',
        alignItems: 'center',
        justifyContent: 'center',
        gap: s.gap,
        padding: s.padding,
        font: `var(--fw-bold) ${s.font}/1 var(--font-sans)`,
        letterSpacing: 'var(--ls-normal)',
        borderRadius: 'var(--radius-pill)',
        cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.45 : 1,
        transition: 'transform var(--dur-fast) var(--ease-standard), background var(--dur-base) var(--ease-standard), filter var(--dur-base) var(--ease-standard)',
        ...v,
        ...style,
      }}
      onMouseDown={(e) => { if (!disabled) e.currentTarget.style.transform = 'scale(var(--press-scale))'; }}
      onMouseUp={(e) => { e.currentTarget.style.transform = 'scale(1)'; }}
      onMouseLeave={(e) => { e.currentTarget.style.transform = 'scale(1)'; }}
      {...rest}
    >
      {leftIcon}
      {children}
      {rightIcon}
    </button>
  );
}
