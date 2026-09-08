One-line: A horizontal vehicle listing card — name, divided spec row, image, and dual actions.

```jsx
<VehicleCard
  name="S 500 Sedan"
  specs={['Automatic','5 seats','Diesel']}
  image={<img src="…" alt="" style={{maxWidth:'100%'}}/>}
  primaryAction={<Button size="sm">Rent Now</Button>}
  secondaryAction={<Button size="sm" variant="secondary">Detail</Button>}
/>
```

Specs render as a gray row separated by hairline dividers. Compose the actions from `Button`.
