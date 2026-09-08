import React from 'react';

/**
 * Base surface — a white rounded card with soft shadow. The core container of the app.
 * pad: 'sm' | 'md' | 'lg'. elevation: 'flat' | 'sm' | 'md' | 'lg'.
 */
export function Card({
  children,
  pad = 'md',
  elevation = 'md',
  radius = 'lg',
  style = {},
  ...rest
}) {
  const pads = { none: '0', sm: '14px', md: '18px', lg: '22px' };
  const shadows = {
    flat: 'none',
    sm: 'var(--shadow-sm)',
    md: 'var(--shadow-md)',
    lg: 'var(--shadow-lg)',
  };
  const radii = { sm: 'var(--radius-sm)', md: 'var(--radius-md)', lg: 'var(--radius-lg)', xl: 'var(--radius-xl)' };
  return (
    <div
      style={{
        background: 'var(--surface-card)',
        borderRadius: radii[radius] || radii.lg,
        padding: pads[pad],
        boxShadow: shadows[elevation],
        ...style,
      }}
      {...rest}
    >
      {children}
    </div>
  );
}
