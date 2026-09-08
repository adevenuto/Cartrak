One-line: The app's bottom tab bar — 4 tabs split around a raised crimson scan FAB.

```jsx
<BottomNav
  active="home"
  onChange={setTab}
  fabIcon={<ScanIcon/>}
  onFab={openScan}
  items={[
    {key:'home', label:'Home', icon:<HomeIcon/>},
    {key:'reports', label:'Reports', icon:<ReportIcon/>},
    {key:'saved', label:'Saved', icon:<HeartIcon/>},
    {key:'settings', label:'Settings', icon:<GearIcon/>},
  ]}
/>
```

Give exactly 4 items — they split two-and-two around the FAB. Active tab turns crimson; inactive tabs are muted gray.
