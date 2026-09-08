import React from 'react';

/**
 * Horizontal vehicle card — name, spec badges, image, and actions. The "Popular Cars"
 * row (S 500 Sedan / GLA 250 SUV). Pass an <img> via `image`, spec strings via `specs`.
 */
export function VehicleCard({
  name,
  specs = [],
  image = null,
  price = null,
  primaryAction = null,
  secondaryAction = null,
  style = {},
  ...rest
}) {
  return (
    <div
      style={{
        background: 'var(--surface-card)',
        borderRadius: 'var(--radius-lg)',
        boxShadow: 'var(--shadow-md)',
        padding: '18px',
        display: 'flex',
        flexDirection: 'column',
        gap: '14px',
        ...style,
      }}
      {...rest}
    >
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '12px' }}>
        <div style={{ minWidth: 0 }}>
          <div style={{ font: 'var(--fw-bold) var(--fs-h3)/1.2 var(--font-sans)', color: 'var(--ink-900)' }}>{name}</div>
          {price && <div style={{ font: 'var(--fw-semibold) var(--fs-body)/1 var(--font-sans)', color: 'var(--brand)', marginTop: 4 }}>{price}</div>}
        </div>
        {image && (
          <div style={{ flexShrink: 0, width: 150, height: 74, display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
            {image}
          </div>
        )}
      </div>
      {specs.length > 0 && (
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', font: 'var(--fw-medium) var(--fs-sm)/1 var(--font-sans)', color: 'var(--ink-500)' }}>
          {specs.map((s, i) => (
            <React.Fragment key={i}>
              {i > 0 && <span style={{ width: 1, height: 12, background: 'var(--border-subtle)' }} />}
              <span>{s}</span>
            </React.Fragment>
          ))}
        </div>
      )}
      {(primaryAction || secondaryAction) && (
        <div style={{ display: 'flex', gap: '10px' }}>
          {primaryAction}
          {secondaryAction}
        </div>
      )}
    </div>
  );
}
