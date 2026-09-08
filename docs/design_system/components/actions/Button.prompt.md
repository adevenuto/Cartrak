One-line: The primary call-to-action — a pill button in CarTrak crimson; use `primary` for the main action on a screen, `secondary` (outline) as its companion.

```jsx
<Button variant="primary" size="md">Rent Now</Button>
<Button variant="secondary">Detail</Button>
<Button variant="primary" block leftIcon={<PlusIcon/>}>Add Vehicle Report</Button>
```

Variants: `primary` (red fill + soft glow), `secondary` (red outline), `ghost` (text only), `dark` (ink fill). Sizes: `sm | md | lg`. `block` makes it full-width. All buttons are fully pill-rounded and shrink slightly on press.
