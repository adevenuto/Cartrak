import React from 'react';

/**
 * Brand tile — a rounded white square holding a brand logo with a name and count below.
 * The "Brands" carousel (Mercedes +32, BMW +12). Pass the logo via `logo`.
 */
export function BrandChip({
  name,
  logo = null,
  count = null,
  active = false,
  style = {},
  ...rest
}) {
  return (
    <button
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: '7px',
        background: 'transparent',
        border: 'none',
        cursor: 'pointer',
        padding: 0,
        ...style,
      }}
      {...rest}
    >
      <div
        style={{
          width: 74,
          height: 74,
          borderRadius: 'var(--radius-md)',
          background: 'var(--white)',
          boxShadow: active ? 'var(--shadow-brand)' : 'var(--shadow-md)',
          border: active ? '1.5px solid var(--brand)' : '1.5px solid transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          transition: 'box-shadow var(--dur-base) var(--ease-standard)',
        }}
      >
        {logo}
      </div>
      <span style={{ font: 'var(--fw-bold) var(--fs-sm)/1 var(--font-sans)', color: 'var(--ink-900)' }}>{name}</span>
      {count != null && (
        <span style={{ font: 'var(--fw-bold) var(--fs-xs)/1 var(--font-sans)', color: 'var(--brand)' }}>+{count}</span>
      )}
    </button>
  );
}
