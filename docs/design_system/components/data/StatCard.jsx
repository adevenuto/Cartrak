import React from 'react';

/**
 * Compact metric card — an icon, a label, and a big value. The "Engine Health 92%"
 * tiles on the hero. Sits on white by default; use tone="glass" over imagery.
 */
export function StatCard({
  icon = null,
  label,
  value,
  tone = 'card',
  style = {},
  ...rest
}) {
  const tones = {
    card: { background: 'var(--white)', boxShadow: 'var(--shadow-md)' },
    glass: { background: 'rgba(255,255,255,0.92)', boxShadow: 'var(--shadow-md)', backdropFilter: 'blur(6px)' },
    sunken: { background: 'var(--surface-sunken)', boxShadow: 'none' },
  };
  const t = tones[tone] || tones.card;
  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        gap: '8px',
        padding: '13px 15px',
        borderRadius: 'var(--radius-md)',
        minWidth: 0,
        ...t,
        ...style,
      }}
      {...rest}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
        {icon && <span style={{ display: 'flex', color: 'var(--ink-900)' }}>{icon}</span>}
        <span style={{ font: 'var(--fw-medium) var(--fs-sm)/1.2 var(--font-sans)', color: 'var(--ink-600)' }}>{label}</span>
      </div>
      <span style={{ font: 'var(--fw-extrabold) var(--fs-h2)/1 var(--font-sans)', color: 'var(--ink-900)' }}>{value}</span>
    </div>
  );
}
