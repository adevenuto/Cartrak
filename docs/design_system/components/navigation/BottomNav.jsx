import React from 'react';

/**
 * Bottom tab bar with a raised center FAB (the crimson scan button).
 * items: [{key, label, icon}] — 4 items, the FAB is injected in the middle.
 * Pass the FAB glyph via `fabIcon` and its handler via `onFab`.
 */
export function BottomNav({
  items = [],
  active,
  onChange = () => {},
  fabIcon = null,
  onFab = () => {},
  style = {},
  ...rest
}) {
  const left = items.slice(0, Math.ceil(items.length / 2));
  const right = items.slice(Math.ceil(items.length / 2));

  const Tab = ({ item }) => {
    const on = item.key === active;
    return (
      <button
        onClick={() => onChange(item.key)}
        style={{
          display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px',
          background: 'none', border: 'none', cursor: 'pointer', flex: 1, padding: '4px 0',
          color: on ? 'var(--brand)' : 'var(--ink-400)',
        }}
      >
        <span style={{ display: 'flex' }}>{item.icon}</span>
        <span style={{ font: `${on ? 'var(--fw-bold)' : 'var(--fw-medium)'} var(--fs-xs)/1 var(--font-sans)` }}>{item.label}</span>
      </button>
    );
  };

  return (
    <div
      style={{
        position: 'relative',
        background: 'var(--white)',
        boxShadow: '0 -8px 24px rgba(26,26,26,0.06)',
        borderTopLeftRadius: 'var(--radius-xl)',
        borderTopRightRadius: 'var(--radius-xl)',
        padding: '14px 22px 20px',
        display: 'flex',
        alignItems: 'flex-start',
        ...style,
      }}
      {...rest}
    >
      <div style={{ display: 'flex', flex: 1, gap: '4px' }}>{left.map((it) => <Tab key={it.key} item={it} />)}</div>
      <div style={{ width: 78, flexShrink: 0 }} />
      <div style={{ display: 'flex', flex: 1, gap: '4px' }}>{right.map((it) => <Tab key={it.key} item={it} />)}</div>
      <button
        onClick={onFab}
        style={{
          position: 'absolute', top: -22, left: '50%', transform: 'translateX(-50%)',
          width: 62, height: 62, borderRadius: 'var(--radius-circle)', border: '4px solid var(--white)',
          background: 'var(--brand)', color: '#fff', cursor: 'pointer',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          boxShadow: 'var(--shadow-brand)',
        }}
      >
        {fabIcon}
      </button>
    </div>
  );
}
