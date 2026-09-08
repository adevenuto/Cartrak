/* Desktop left sidebar — brand wordmark, nav, scan CTA. */
function Sidebar({ active, onChange, onScan }) {
  const nav = [
    { key: 'home', label: 'Dashboard', icon: 'House' },
    { key: 'reports', label: 'Reports', icon: 'ChartColumn' },
    { key: 'services', label: 'Services', icon: 'Wrench' },
    { key: 'saved', label: 'Saved', icon: 'Heart' },
    { key: 'settings', label: 'Settings', icon: 'Settings' },
  ];
  return (
    <aside style={{ width: 248, flexShrink: 0, background: '#fff', borderRight: '1px solid var(--border-subtle)', display: 'flex', flexDirection: 'column', padding: '26px 18px' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '0 10px 26px' }}>
        <div style={{ width: 34, height: 34, borderRadius: 10, background: 'var(--brand)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: 'var(--shadow-brand)' }}>
          <Icon n="Car" s={20} color="#fff" strokeWidth={2.2} />
        </div>
        <span style={{ font: '800 20px var(--font-sans)', color: 'var(--ink-900)', letterSpacing: '-0.02em' }}>CarTrak</span>
      </div>
      <nav style={{ display: 'flex', flexDirection: 'column', gap: 4, flex: 1 }}>
        {nav.map((n) => {
          const on = n.key === active;
          return (
            <button key={n.key} onClick={() => onChange(n.key)} style={{
              display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px', borderRadius: 'var(--radius-md)',
              border: 'none', cursor: 'pointer', textAlign: 'left', width: '100%',
              background: on ? 'var(--red-50)' : 'transparent',
              color: on ? 'var(--brand)' : 'var(--ink-600)',
              font: `${on ? '700' : '500'} 15px var(--font-sans)`,
            }}>
              <Icon n={n.icon} s={20} color={on ? 'var(--brand)' : 'var(--ink-500)'} />
              {n.label}
            </button>
          );
        })}
      </nav>
      <button onClick={onScan} style={{
        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, padding: '15px', borderRadius: 'var(--radius-pill)',
        border: 'none', cursor: 'pointer', background: 'var(--brand)', color: '#fff', font: '700 15px var(--font-sans)', boxShadow: 'var(--shadow-brand)',
      }}>
        <Icon n="ScanLine" s={20} color="#fff" /> Scan Vehicle
      </button>
    </aside>
  );
}
window.Sidebar = Sidebar;
