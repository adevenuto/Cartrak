/* Desktop top bar — greeting, search, date, notifications, profile. */
function TopBar() {
  const { Input, IconButton } = window.CarTrakDesignSystem_588b8a;
  return (
    <header style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 24, padding: '22px 32px', borderBottom: '1px solid var(--border-subtle)', background: 'var(--surface-page)' }}>
      <div>
        <div style={{ font: '800 24px var(--font-sans)', color: 'var(--ink-900)' }}>Good morning, Alex</div>
        <div style={{ font: '400 14px var(--font-serif-body)', color: 'var(--ink-500)', marginTop: 2 }}>Here's how your garage is doing — 20 Aug 2026.</div>
      </div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
        <div style={{ width: 300 }}><Input placeholder="Search services, cars…" leftIcon={<Icon n="Search" s={18} />} /></div>
        <IconButton tone="light" size={46}><Icon n="Bell" s={20} /></IconButton>
        <div style={{ width: 46, height: 46, borderRadius: '50%', background: 'var(--grad-brand)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', font: '700 16px var(--font-sans)', boxShadow: 'var(--shadow-sm)' }}>A</div>
      </div>
    </header>
  );
}
window.TopBar = TopBar;
