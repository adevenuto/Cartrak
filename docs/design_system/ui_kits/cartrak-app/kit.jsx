/* Shared kit helpers — Icon (Lucide), StatusBar, PhoneFrame. Exported to window. */
function Icon({ n, s = 22, color, strokeWidth = 2, style = {} }) {
  const ref = React.useRef();
  React.useEffect(() => {
    if (ref.current && window.lucide && lucide[n]) {
      ref.current.innerHTML = '';
      const el = lucide.createElement(lucide[n]);
      el.setAttribute('width', s); el.setAttribute('height', s);
      el.setAttribute('stroke-width', strokeWidth);
      ref.current.appendChild(el);
    }
  }, [n, s, strokeWidth]);
  return <span ref={ref} style={{ display: 'inline-flex', color, ...style }} />;
}

function StatusBar({ dark = false }) {
  const c = dark ? '#fff' : 'var(--ink-900)';
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 26px 4px', color: c }}>
      <span style={{ font: '700 15px var(--font-sans)' }}>9:41</span>
      <div style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
        <Icon n="SignalHigh" s={17} color={c} />
        <Icon n="Wifi" s={16} color={c} />
        <Icon n="BatteryFull" s={20} color={c} />
      </div>
    </div>
  );
}

/* iPhone-ish frame, 390x844 content area with home indicator */
function PhoneFrame({ children, bg = 'var(--surface-page)' }) {
  return (
    <div style={{ width: 390, height: 844, borderRadius: 46, background: '#000', padding: 5, boxShadow: '0 40px 90px rgba(26,26,26,0.28)', flexShrink: 0 }}>
      <div style={{ width: '100%', height: '100%', borderRadius: 42, background: bg, overflow: 'hidden', position: 'relative', display: 'flex', flexDirection: 'column' }}>
        {children}
      </div>
    </div>
  );
}

Object.assign(window, { Icon, StatusBar, PhoneFrame });
