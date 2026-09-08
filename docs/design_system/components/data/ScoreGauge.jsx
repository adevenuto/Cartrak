import React from 'react';

/**
 * Semicircular "Vehicle Score" gauge. Segmented arc that fills with the brand
 * gradient up to `value`/`max`. Center shows the score. Pass a center glyph via `icon`.
 */
export function ScoreGauge({
  value = 85,
  max = 100,
  segments = 20,
  label = 'Vehicle Score',
  sublabel = null,
  icon = null,
  size = 260,
  style = {},
  ...rest
}) {
  const pct = Math.max(0, Math.min(1, value / max));
  const filled = Math.round(pct * segments);
  const cx = size / 2;
  const cy = size / 2;
  const rOuter = size / 2 - 6;
  const rInner = rOuter - 16;
  const gap = 0.05; // radians between segments
  const span = Math.PI; // half circle
  const seg = span / segments;

  const arcs = [];
  for (let i = 0; i < segments; i++) {
    const a0 = Math.PI + i * seg + gap / 2;
    const a1 = Math.PI + (i + 1) * seg - gap / 2;
    const p = (r, a) => [cx + r * Math.cos(a), cy + r * Math.sin(a)];
    const [x0o, y0o] = p(rOuter, a0);
    const [x1o, y1o] = p(rOuter, a1);
    const [x1i, y1i] = p(rInner, a1);
    const [x0i, y0i] = p(rInner, a0);
    const d = `M ${x0o} ${y0o} A ${rOuter} ${rOuter} 0 0 1 ${x1o} ${y1o} L ${x1i} ${y1i} A ${rInner} ${rInner} 0 0 0 ${x0i} ${y0i} Z`;
    const on = i < filled;
    const t = filled > 1 ? i / (filled - 1) : 0;
    const color = on
      ? `color-mix(in oklab, var(--red-300) ${Math.round((1 - t) * 100)}%, var(--red-700))`
      : 'var(--ink-200)';
    arcs.push(<path key={i} d={d} fill={color} />);
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', ...style }} {...rest}>
      <div style={{ position: 'relative', width: size, height: size / 2 + 8 }}>
        <svg width={size} height={size / 2 + 8} viewBox={`0 0 ${size} ${size / 2 + 8}`} style={{ display: 'block' }}>
          {arcs}
        </svg>
        <div style={{ position: 'absolute', top: '38%', left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
          {icon || <span style={{ fontSize: 22, color: 'var(--brand)' }}>⚡</span>}
        </div>
      </div>
      <span style={{ font: 'var(--fw-medium) var(--fs-body)/1 var(--font-sans)', color: 'var(--ink-700)', marginTop: 6 }}>{label}</span>
      <div style={{ marginTop: 6 }}>
        <span style={{ font: 'var(--fw-extrabold) var(--fs-score)/1 var(--font-sans)', color: 'var(--ink-900)' }}>{value}</span>
        <span style={{ font: 'var(--fw-medium) var(--fs-h2)/1 var(--font-sans)', color: 'var(--ink-400)' }}>/ {max}</span>
      </div>
      {sublabel && <span style={{ font: 'var(--fw-semibold) var(--fs-sm)/1 var(--font-sans)', color: 'var(--brand)', marginTop: 6 }}>{sublabel}</span>}
    </div>
  );
}
