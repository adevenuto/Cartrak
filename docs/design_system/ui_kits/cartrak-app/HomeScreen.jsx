/* Home dashboard — vehicle score, add report, popular cars. */
function HomeScreen() {
  const { IconButton, Button, ScoreGauge, VehicleCard, Card } = window.CarTrakDesignSystem_588b8a;
  const cars = [
    { name: 'S 500 Sedan', specs: ['Automatic', '5 seats', 'Diesel'] },
    { name: 'GLA 250 SUV', specs: ['Automatic', '5 seats', 'Petrol'] },
  ];
  return (
    <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 120 }}>
      <StatusBar />
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '8px 20px 4px' }}>
        <IconButton tone="light" size={44}><Icon n="Calendar" s={20} /></IconButton>
        <span style={{ font: '700 16px var(--font-sans)', color: 'var(--ink-900)' }}>20 Aug 2026</span>
        <IconButton tone="light" size={44}><Icon n="Bell" s={20} /></IconButton>
      </div>
      <div style={{ display: 'flex', justifyContent: 'center', padding: '10px 20px 0' }}>
        <ScoreGauge value={85} max={100} size={250} label="Vehicle Score" sublabel="Target Score 100" icon={<Icon n="Zap" s={22} color="var(--brand)" />} />
      </div>
      <div style={{ padding: '4px 20px 0' }}>
        <Button variant="dark" block size="md" leftIcon={<Icon n="Plus" s={18} />} style={{ background: '#fff', color: 'var(--ink-900)', border: '1.5px solid transparent', boxShadow: 'var(--shadow-md)' }}>Add Vehicle Report</Button>
      </div>
      <div style={{ padding: '22px 20px 0' }}>
        <div style={{ font: '800 22px var(--font-sans)', color: 'var(--ink-900)', marginBottom: 14 }}>Popular Cars</div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          {cars.map((c) => (
            <VehicleCard key={c.name} name={c.name} specs={c.specs}
              image={<Icon n="Car" s={56} strokeWidth={1.2} color="var(--ink-800)" />}
              primaryAction={<Button size="sm">Rent Now</Button>}
              secondaryAction={<Button size="sm" variant="secondary">Detail</Button>} />
          ))}
        </div>
      </div>
    </div>
  );
}
window.HomeScreen = HomeScreen;
