/* Desktop dashboard body — score, health metrics, popular cars, featured service. */
function Dashboard() {
  const { Card, ScoreGauge, StatCard, VehicleCard, Button, Badge, BrandChip } = window.CarTrakDesignSystem_588b8a;
  const stats = [
    { icon: 'Gauge', label: 'Engine Health', value: '92%' },
    { icon: 'Disc3', label: 'Tire Condition', value: '85%' },
    { icon: 'BatteryCharging', label: 'Battery Health', value: '78%' },
    { icon: 'Fuel', label: 'Fuel Efficiency', value: '88%' },
  ];
  const cars = [
    { name: 'S 500 Sedan', specs: ['Automatic', '5 seats', 'Diesel'] },
    { name: 'GLA 250 SUV', specs: ['Automatic', '5 seats', 'Petrol'] },
  ];
  const brands = [
    { name: 'Mercedes', count: 32 }, { name: 'BMW', count: 12 }, { name: 'Porsche', count: 8 }, { name: 'Audi', count: 9 },
  ];
  const brandLogo = (n) => (<div style={{ width: 40, height: 40, borderRadius: '50%', background: 'var(--surface-sunken)', display: 'flex', alignItems: 'center', justifyContent: 'center', font: '800 15px var(--font-sans)', color: 'var(--ink-700)' }}>{n[0]}</div>);

  return (
    <div style={{ flex: 1, overflowY: 'auto', padding: '28px 32px', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(340px, 1fr))', gap: 24, alignContent: 'start' }}>
      {/* Left column */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 24, minWidth: 0 }}>
        <Card elevation="md" style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 32, padding: 28 }}>
          <ScoreGauge value={85} max={100} size={230} label="Vehicle Score" sublabel="Target Score 100" icon={<Icon n="Zap" s={22} color="var(--brand)" />} />
          <div style={{ flex: 1, minWidth: 240 }}>
            <div style={{ font: '800 20px var(--font-sans)', color: 'var(--ink-900)', marginBottom: 6 }}>Your car is in great shape</div>
            <p style={{ font: '400 15px/1.6 var(--font-serif-body)', color: 'var(--ink-600)', margin: '0 0 18px' }}>Overall condition is strong. Next scheduled service is due in 1,200 miles. Run a full scan to update these numbers.</p>
            <Button variant="primary" leftIcon={<Icon n="Plus" s={18} />}>Add Vehicle Report</Button>
          </div>
        </Card>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16 }}>
          {stats.map((s) => <StatCard key={s.label} icon={<Icon n={s.icon} s={18} />} label={s.label} value={s.value} />)}
        </div>

        <div>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
            <span style={{ font: '800 20px var(--font-sans)', color: 'var(--ink-900)' }}>Popular Cars</span>
            <span style={{ font: '700 13px var(--font-sans)', color: 'var(--brand)', display: 'flex', alignItems: 'center', gap: 2, cursor: 'pointer' }}>See All <Icon n="ChevronRight" s={15} color="var(--brand)" /></span>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
            {cars.map((c) => (
              <VehicleCard key={c.name} name={c.name} specs={c.specs}
                image={<Icon n="Car" s={52} strokeWidth={1.2} color="var(--ink-800)" />}
                primaryAction={<Button size="sm">Rent Now</Button>}
                secondaryAction={<Button size="sm" variant="secondary">Detail</Button>} />
            ))}
          </div>
        </div>
      </div>

      {/* Right column */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 24, minWidth: 0 }}>
        <Card pad="none" elevation="md" style={{ overflow: 'hidden' }}>
          <div style={{ position: 'relative', height: 176, background: 'var(--surface-sunken)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Icon n="Car" s={96} strokeWidth={1} color="var(--ink-400)" />
            <div style={{ position: 'absolute', top: 14, right: 14, width: 36, height: 36, borderRadius: '50%', background: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: 'var(--shadow-sm)' }}>
              <Icon n="Heart" s={17} color="var(--brand)" />
            </div>
          </div>
          <div style={{ padding: 20 }}>
            <div style={{ font: '700 18px var(--font-sans)', color: 'var(--ink-900)' }}>Full Vehicle Inspection</div>
            <p style={{ font: '400 14px/1.5 var(--font-serif-body)', color: 'var(--ink-600)', margin: '6px 0 14px' }}>A complete 45-minute diagnostic across every major system, from engine to brakes.</p>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <Badge tone="brand" leftIcon={<Icon n="Clock" s={13} color="#fff" />}>45 min</Badge>
              <Button size="sm">Book now</Button>
            </div>
          </div>
        </Card>

        <Card elevation="md" style={{ padding: 22 }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
            <span style={{ font: '800 18px var(--font-sans)', color: 'var(--ink-900)' }}>Brands</span>
            <span style={{ font: '700 13px var(--font-sans)', color: 'var(--brand)', cursor: 'pointer' }}>See All</span>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 12, justifyItems: 'center' }}>
            {brands.map((b, i) => <BrandChip key={b.name} name={b.name} count={b.count} logo={brandLogo(b.name)} active={i === 0} />)}
          </div>
        </Card>
      </div>
    </div>
  );
}
window.Dashboard = Dashboard;
