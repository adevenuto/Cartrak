/* Services / discover — search, brands carousel, featured service. */
function ServicesScreen() {
  const { Input, IconButton, Badge, BrandChip, Card } = window.CarTrakDesignSystem_588b8a;
  const brands = [
    { name: 'Mercedes', count: 32 }, { name: 'BMW', count: 12 },
    { name: 'Porsche', count: 8 }, { name: 'Renault', count: 5 }, { name: 'Audi', count: 9 },
  ];
  const brandLogo = (n) => (
    <div style={{ width: 42, height: 42, borderRadius: '50%', background: 'var(--surface-sunken)', display: 'flex', alignItems: 'center', justifyContent: 'center', font: '800 16px var(--font-sans)', color: 'var(--ink-700)' }}>{n[0]}</div>
  );
  return (
    <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 120 }}>
      <StatusBar />
      <div style={{ display: 'flex', gap: 12, alignItems: 'center', padding: '10px 20px 4px' }}>
        <div style={{ flex: 1 }}><Input placeholder="Search Services" leftIcon={<Icon n="Search" s={18} />} /></div>
        <IconButton tone="brand" size={48}><Icon n="Bell" s={20} /></IconButton>
      </div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '18px 20px 12px' }}>
        <span style={{ font: '800 22px var(--font-sans)', color: 'var(--ink-900)' }}>Brands</span>
        <span style={{ font: '700 13px var(--font-sans)', color: 'var(--brand)', display: 'flex', alignItems: 'center', gap: 2 }}>See All <Icon n="ChevronRight" s={15} color="var(--brand)" /></span>
      </div>
      <div style={{ display: 'flex', gap: 16, padding: '0 20px 6px', overflowX: 'auto' }}>
        {brands.map((b, i) => <BrandChip key={b.name} name={b.name} count={b.count} logo={brandLogo(b.name)} active={i === 0} />)}
      </div>
      <div style={{ padding: '18px 20px 0' }}>
        <Card pad="none" elevation="md" style={{ overflow: 'hidden' }}>
          <div style={{ position: 'relative', height: 168, background: 'var(--surface-sunken)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Icon n="Car" s={92} strokeWidth={1} color="var(--ink-400)" />
            <div style={{ position: 'absolute', top: 12, right: 12, width: 34, height: 34, borderRadius: '50%', background: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: 'var(--shadow-sm)' }}>
              <Icon n="Heart" s={17} color="var(--brand)" />
            </div>
          </div>
          <div style={{ padding: 18 }}>
            <div style={{ font: '700 18px var(--font-sans)', color: 'var(--ink-900)' }}>Full Vehicle Inspection</div>
            <p style={{ font: '400 14px/1.5 var(--font-serif-body)', color: 'var(--ink-600)', margin: '6px 0 14px' }}>The Mercedes SL 63 AMG is a sports car created using advanced diagnostics.</p>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <Badge tone="brand" leftIcon={<Icon n="Clock" s={13} color="#fff" />}>45 min</Badge>
              <span style={{ font: '700 13px var(--font-sans)', color: 'var(--ink-900)', display: 'flex', alignItems: 'center', gap: 6 }}>100 Speed <Icon n="Signal" s={16} color="var(--brand)" /></span>
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
}
window.ServicesScreen = ServicesScreen;
