/* Onboarding / marketing hero — the crimson gradient "Fast car" screen. */
function HeroScreen({ onStart }) {
  const { StatCard, Button } = window.CarTrakDesignSystem_588b8a;
  const stats = [
    { icon: 'Gauge', label: 'Engine Health', value: '92%' },
    { icon: 'Disc3', label: 'Tire Condition', value: '85%' },
    { icon: 'BatteryCharging', label: 'Battery Health', value: '78%' },
    { icon: 'Fuel', label: 'Fuel Efficiency', value: '88%' },
  ];
  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%', background: 'var(--grad-hero)' }}>
      <StatusBar dark />
      <div style={{ position: 'relative', flex: 1, display: 'flex', flexDirection: 'column' }}>
        <div style={{ font: '800 62px var(--font-serif)', color: 'rgba(255,255,255,0.22)', textAlign: 'center', letterSpacing: '-0.02em', marginTop: 6, lineHeight: 1 }}>Fast car</div>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, padding: '0 22px', marginTop: -34 }}>
          {stats.map((s) => (
            <StatCard key={s.label} tone="glass" icon={<Icon n={s.icon} s={18} />} label={s.label} value={s.value} />
          ))}
        </div>
        <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(120,20,40,0.5)' }}>
          <Icon n="Car" s={132} strokeWidth={1.1} color="rgba(90,15,30,0.55)" />
        </div>
        <div style={{ background: '#fff', borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: '26px 26px 30px', textAlign: 'center' }}>
          <div style={{ font: '800 26px/1.2 var(--font-sans)', color: 'var(--ink-900)' }}>Your Car,</div>
          <div style={{ font: '800 26px/1.2 var(--font-sans)', color: 'var(--brand)', marginBottom: 12 }}>Powered By All</div>
          <p style={{ font: '400 15px/1.5 var(--font-serif-body)', color: 'var(--ink-600)', margin: '0 0 20px', maxWidth: 300, marginInline: 'auto' }}>
            From vehicle scanning to maintenance tracking, everything managed automatically.
          </p>
          <Button variant="primary" block size="lg" onClick={onStart}>Get started</Button>
        </div>
      </div>
    </div>
  );
}
window.HeroScreen = HeroScreen;
