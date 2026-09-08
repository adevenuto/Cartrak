/* @ds-bundle: {"format":4,"namespace":"CarTrakDesignSystem_588b8a","components":[{"name":"Button","sourcePath":"components/actions/Button.jsx"},{"name":"IconButton","sourcePath":"components/actions/IconButton.jsx"},{"name":"Badge","sourcePath":"components/data/Badge.jsx"},{"name":"BrandChip","sourcePath":"components/data/BrandChip.jsx"},{"name":"ScoreGauge","sourcePath":"components/data/ScoreGauge.jsx"},{"name":"StatCard","sourcePath":"components/data/StatCard.jsx"},{"name":"VehicleCard","sourcePath":"components/data/VehicleCard.jsx"},{"name":"Input","sourcePath":"components/forms/Input.jsx"},{"name":"BottomNav","sourcePath":"components/navigation/BottomNav.jsx"},{"name":"Card","sourcePath":"components/surfaces/Card.jsx"}],"sourceHashes":{"components/actions/Button.jsx":"968f54bf52b8","components/actions/IconButton.jsx":"f9ab65919b21","components/data/Badge.jsx":"0822caf1304a","components/data/BrandChip.jsx":"dc322301819b","components/data/ScoreGauge.jsx":"fee93c4b516b","components/data/StatCard.jsx":"ef7643230fa5","components/data/VehicleCard.jsx":"9da8032eab47","components/forms/Input.jsx":"bafe04a5e4c1","components/navigation/BottomNav.jsx":"282b3ba9e908","components/surfaces/Card.jsx":"93551fec306e","ui_kits/cartrak-app/HeroScreen.jsx":"43627b3d92c2","ui_kits/cartrak-app/HomeScreen.jsx":"566af030575f","ui_kits/cartrak-app/ServicesScreen.jsx":"0150846b9ce6","ui_kits/cartrak-app/kit.jsx":"89d60a6553f3","ui_kits/cartrak-desktop/Dashboard.jsx":"500784ec219d","ui_kits/cartrak-desktop/Sidebar.jsx":"9b4ab48a5378","ui_kits/cartrak-desktop/TopBar.jsx":"08825156fb2a","ui_kits/cartrak-desktop/kit.jsx":"170b77f399f3"},"inlinedExternals":[],"unexposedExports":[]} */

(() => {

const __ds_ns = (window.CarTrakDesignSystem_588b8a = window.CarTrakDesignSystem_588b8a || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/actions/Button.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * CarTrak primary action button. Pill-shaped, red fill by default.
 * Variants: primary (red fill), secondary (red outline), ghost (text only), dark (ink fill).
 */
function Button({
  children,
  variant = 'primary',
  size = 'md',
  block = false,
  disabled = false,
  leftIcon = null,
  rightIcon = null,
  style = {},
  ...rest
}) {
  const sizes = {
    sm: {
      padding: '9px 16px',
      font: 'var(--fs-sm)',
      gap: '6px'
    },
    md: {
      padding: '14px 22px',
      font: 'var(--fs-title)',
      gap: '8px'
    },
    lg: {
      padding: '17px 26px',
      font: 'var(--fs-h3)',
      gap: '10px'
    }
  };
  const s = sizes[size] || sizes.md;
  const variants = {
    primary: {
      background: 'var(--brand)',
      color: 'var(--on-brand)',
      border: '1.5px solid var(--brand)',
      boxShadow: 'var(--shadow-brand)'
    },
    secondary: {
      background: 'transparent',
      color: 'var(--brand)',
      border: '1.5px solid var(--brand)',
      boxShadow: 'none'
    },
    ghost: {
      background: 'transparent',
      color: 'var(--brand)',
      border: '1.5px solid transparent',
      boxShadow: 'none'
    },
    dark: {
      background: 'var(--ink-900)',
      color: '#fff',
      border: '1.5px solid var(--ink-900)',
      boxShadow: 'var(--shadow-sm)'
    }
  };
  const v = variants[variant] || variants.primary;
  return /*#__PURE__*/React.createElement("button", _extends({
    disabled: disabled,
    style: {
      display: block ? 'flex' : 'inline-flex',
      width: block ? '100%' : 'auto',
      alignItems: 'center',
      justifyContent: 'center',
      gap: s.gap,
      padding: s.padding,
      font: `var(--fw-bold) ${s.font}/1 var(--font-sans)`,
      letterSpacing: 'var(--ls-normal)',
      borderRadius: 'var(--radius-pill)',
      cursor: disabled ? 'not-allowed' : 'pointer',
      opacity: disabled ? 0.45 : 1,
      transition: 'transform var(--dur-fast) var(--ease-standard), background var(--dur-base) var(--ease-standard), filter var(--dur-base) var(--ease-standard)',
      ...v,
      ...style
    },
    onMouseDown: e => {
      if (!disabled) e.currentTarget.style.transform = 'scale(var(--press-scale))';
    },
    onMouseUp: e => {
      e.currentTarget.style.transform = 'scale(1)';
    },
    onMouseLeave: e => {
      e.currentTarget.style.transform = 'scale(1)';
    }
  }, rest), leftIcon, children, rightIcon);
}
Object.assign(__ds_scope, { Button });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/actions/Button.jsx", error: String((e && e.message) || e) }); }

// components/actions/IconButton.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Circular icon button — the round bell / calendar / heart controls in headers.
 * tone: 'light' (white on gray), 'brand' (red fill), 'ghost' (transparent).
 */
function IconButton({
  children,
  tone = 'light',
  size = 44,
  disabled = false,
  style = {},
  ...rest
}) {
  const tones = {
    light: {
      background: 'var(--white)',
      color: 'var(--ink-900)',
      boxShadow: 'var(--shadow-sm)'
    },
    brand: {
      background: 'var(--brand)',
      color: '#fff',
      boxShadow: 'var(--shadow-brand)'
    },
    ghost: {
      background: 'transparent',
      color: 'var(--ink-700)',
      boxShadow: 'none'
    },
    dark: {
      background: 'var(--ink-900)',
      color: '#fff',
      boxShadow: 'var(--shadow-sm)'
    }
  };
  const t = tones[tone] || tones.light;
  return /*#__PURE__*/React.createElement("button", _extends({
    disabled: disabled,
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      justifyContent: 'center',
      width: size,
      height: size,
      borderRadius: 'var(--radius-circle)',
      border: 'none',
      cursor: disabled ? 'not-allowed' : 'pointer',
      opacity: disabled ? 0.45 : 1,
      transition: 'transform var(--dur-fast) var(--ease-standard), filter var(--dur-base) var(--ease-standard)',
      ...t,
      ...style
    },
    onMouseDown: e => {
      if (!disabled) e.currentTarget.style.transform = 'scale(var(--press-scale))';
    },
    onMouseUp: e => {
      e.currentTarget.style.transform = 'scale(1)';
    },
    onMouseLeave: e => {
      e.currentTarget.style.transform = 'scale(1)';
    }
  }, rest), children);
}
Object.assign(__ds_scope, { IconButton });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/actions/IconButton.jsx", error: String((e && e.message) || e) }); }

// components/data/Badge.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Small pill label / tag. Used for spec chips (Automatic, 5 seats), counts (+32),
 * durations (45 min) and status. tone: 'neutral' | 'brand' | 'brand-soft' | 'dark'.
 */
function Badge({
  children,
  tone = 'neutral',
  size = 'md',
  leftIcon = null,
  style = {},
  ...rest
}) {
  const tones = {
    neutral: {
      background: 'var(--ink-100)',
      color: 'var(--ink-700)'
    },
    brand: {
      background: 'var(--brand)',
      color: '#fff'
    },
    'brand-soft': {
      background: 'var(--red-50)',
      color: 'var(--brand)'
    },
    dark: {
      background: 'var(--ink-900)',
      color: '#fff'
    },
    outline: {
      background: 'transparent',
      color: 'var(--ink-600)',
      boxShadow: 'inset 0 0 0 1px var(--border-subtle)'
    }
  };
  const sizes = {
    sm: {
      padding: '3px 9px',
      font: 'var(--fs-xs)'
    },
    md: {
      padding: '5px 12px',
      font: 'var(--fs-sm)'
    }
  };
  const t = tones[tone] || tones.neutral;
  const s = sizes[size] || sizes.md;
  return /*#__PURE__*/React.createElement("span", _extends({
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      gap: '5px',
      padding: s.padding,
      font: `var(--fw-semibold) ${s.font}/1 var(--font-sans)`,
      borderRadius: 'var(--radius-pill)',
      whiteSpace: 'nowrap',
      ...t,
      ...style
    }
  }, rest), leftIcon, children);
}
Object.assign(__ds_scope, { Badge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/Badge.jsx", error: String((e && e.message) || e) }); }

// components/data/BrandChip.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Brand tile — a rounded white square holding a brand logo with a name and count below.
 * The "Brands" carousel (Mercedes +32, BMW +12). Pass the logo via `logo`.
 */
function BrandChip({
  name,
  logo = null,
  count = null,
  active = false,
  style = {},
  ...rest
}) {
  return /*#__PURE__*/React.createElement("button", _extends({
    style: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      gap: '7px',
      background: 'transparent',
      border: 'none',
      cursor: 'pointer',
      padding: 0,
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("div", {
    style: {
      width: 74,
      height: 74,
      borderRadius: 'var(--radius-md)',
      background: 'var(--white)',
      boxShadow: active ? 'var(--shadow-brand)' : 'var(--shadow-md)',
      border: active ? '1.5px solid var(--brand)' : '1.5px solid transparent',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      transition: 'box-shadow var(--dur-base) var(--ease-standard)'
    }
  }, logo), /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-bold) var(--fs-sm)/1 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, name), count != null && /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-bold) var(--fs-xs)/1 var(--font-sans)',
      color: 'var(--brand)'
    }
  }, "+", count));
}
Object.assign(__ds_scope, { BrandChip });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/BrandChip.jsx", error: String((e && e.message) || e) }); }

// components/data/ScoreGauge.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Semicircular "Vehicle Score" gauge. Segmented arc that fills with the brand
 * gradient up to `value`/`max`. Center shows the score. Pass a center glyph via `icon`.
 */
function ScoreGauge({
  value = 85,
  max = 100,
  segments = 20,
  label = 'Vehicle Score',
  sublabel = null,
  icon = null,
  size = 260,
  style = {},
  ...rest
}) {
  const pct = Math.max(0, Math.min(1, value / max));
  const filled = Math.round(pct * segments);
  const cx = size / 2;
  const cy = size / 2;
  const rOuter = size / 2 - 6;
  const rInner = rOuter - 16;
  const gap = 0.05; // radians between segments
  const span = Math.PI; // half circle
  const seg = span / segments;
  const arcs = [];
  for (let i = 0; i < segments; i++) {
    const a0 = Math.PI + i * seg + gap / 2;
    const a1 = Math.PI + (i + 1) * seg - gap / 2;
    const p = (r, a) => [cx + r * Math.cos(a), cy + r * Math.sin(a)];
    const [x0o, y0o] = p(rOuter, a0);
    const [x1o, y1o] = p(rOuter, a1);
    const [x1i, y1i] = p(rInner, a1);
    const [x0i, y0i] = p(rInner, a0);
    const d = `M ${x0o} ${y0o} A ${rOuter} ${rOuter} 0 0 1 ${x1o} ${y1o} L ${x1i} ${y1i} A ${rInner} ${rInner} 0 0 0 ${x0i} ${y0i} Z`;
    const on = i < filled;
    const t = filled > 1 ? i / (filled - 1) : 0;
    const color = on ? `color-mix(in oklab, var(--red-300) ${Math.round((1 - t) * 100)}%, var(--red-700))` : 'var(--ink-200)';
    arcs.push(/*#__PURE__*/React.createElement("path", {
      key: i,
      d: d,
      fill: color
    }));
  }
  return /*#__PURE__*/React.createElement("div", _extends({
    style: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      width: size,
      height: size / 2 + 8
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: size,
    height: size / 2 + 8,
    viewBox: `0 0 ${size} ${size / 2 + 8}`,
    style: {
      display: 'block'
    }
  }, arcs), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: '38%',
      left: 0,
      right: 0,
      display: 'flex',
      justifyContent: 'center'
    }
  }, icon || /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 22,
      color: 'var(--brand)'
    }
  }, "\u26A1"))), /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-medium) var(--fs-body)/1 var(--font-sans)',
      color: 'var(--ink-700)',
      marginTop: 6
    }
  }, label), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-extrabold) var(--fs-score)/1 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, value), /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-medium) var(--fs-h2)/1 var(--font-sans)',
      color: 'var(--ink-400)'
    }
  }, "/ ", max)), sublabel && /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-semibold) var(--fs-sm)/1 var(--font-sans)',
      color: 'var(--brand)',
      marginTop: 6
    }
  }, sublabel));
}
Object.assign(__ds_scope, { ScoreGauge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/ScoreGauge.jsx", error: String((e && e.message) || e) }); }

// components/data/StatCard.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Compact metric card — an icon, a label, and a big value. The "Engine Health 92%"
 * tiles on the hero. Sits on white by default; use tone="glass" over imagery.
 */
function StatCard({
  icon = null,
  label,
  value,
  tone = 'card',
  style = {},
  ...rest
}) {
  const tones = {
    card: {
      background: 'var(--white)',
      boxShadow: 'var(--shadow-md)'
    },
    glass: {
      background: 'rgba(255,255,255,0.92)',
      boxShadow: 'var(--shadow-md)',
      backdropFilter: 'blur(6px)'
    },
    sunken: {
      background: 'var(--surface-sunken)',
      boxShadow: 'none'
    }
  };
  const t = tones[tone] || tones.card;
  return /*#__PURE__*/React.createElement("div", _extends({
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: '8px',
      padding: '13px 15px',
      borderRadius: 'var(--radius-md)',
      minWidth: 0,
      ...t,
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '8px'
    }
  }, icon && /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'flex',
      color: 'var(--ink-900)'
    }
  }, icon), /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-medium) var(--fs-sm)/1.2 var(--font-sans)',
      color: 'var(--ink-600)'
    }
  }, label)), /*#__PURE__*/React.createElement("span", {
    style: {
      font: 'var(--fw-extrabold) var(--fs-h2)/1 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, value));
}
Object.assign(__ds_scope, { StatCard });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/StatCard.jsx", error: String((e && e.message) || e) }); }

// components/data/VehicleCard.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Horizontal vehicle card — name, spec badges, image, and actions. The "Popular Cars"
 * row (S 500 Sedan / GLA 250 SUV). Pass an <img> via `image`, spec strings via `specs`.
 */
function VehicleCard({
  name,
  specs = [],
  image = null,
  price = null,
  primaryAction = null,
  secondaryAction = null,
  style = {},
  ...rest
}) {
  return /*#__PURE__*/React.createElement("div", _extends({
    style: {
      background: 'var(--surface-card)',
      borderRadius: 'var(--radius-lg)',
      boxShadow: 'var(--shadow-md)',
      padding: '18px',
      display: 'flex',
      flexDirection: 'column',
      gap: '14px',
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: '12px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: 'var(--fw-bold) var(--fs-h3)/1.2 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, name), price && /*#__PURE__*/React.createElement("div", {
    style: {
      font: 'var(--fw-semibold) var(--fs-body)/1 var(--font-sans)',
      color: 'var(--brand)',
      marginTop: 4
    }
  }, price)), image && /*#__PURE__*/React.createElement("div", {
    style: {
      flexShrink: 0,
      width: 150,
      height: 74,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'flex-end'
    }
  }, image)), specs.length > 0 && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '10px',
      font: 'var(--fw-medium) var(--fs-sm)/1 var(--font-sans)',
      color: 'var(--ink-500)'
    }
  }, specs.map((s, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: i
  }, i > 0 && /*#__PURE__*/React.createElement("span", {
    style: {
      width: 1,
      height: 12,
      background: 'var(--border-subtle)'
    }
  }), /*#__PURE__*/React.createElement("span", null, s)))), (primaryAction || secondaryAction) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, primaryAction, secondaryAction));
}
Object.assign(__ds_scope, { VehicleCard });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/VehicleCard.jsx", error: String((e && e.message) || e) }); }

// components/forms/Input.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Text input / search field. Pill-shaped rounded field on white with soft shadow.
 * Pass leftIcon (e.g. a search glyph) for the "Search Services" pattern.
 */
function Input({
  value,
  onChange,
  placeholder = '',
  leftIcon = null,
  rightIcon = null,
  type = 'text',
  disabled = false,
  rounded = 'pill',
  style = {},
  ...rest
}) {
  const [focus, setFocus] = React.useState(false);
  const radius = rounded === 'pill' ? 'var(--radius-pill)' : 'var(--radius-md)';
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '10px',
      background: 'var(--white)',
      borderRadius: radius,
      padding: '13px 18px',
      border: `1.5px solid ${focus ? 'var(--brand)' : 'transparent'}`,
      boxShadow: 'var(--shadow-sm)',
      transition: 'border-color var(--dur-base) var(--ease-standard)',
      opacity: disabled ? 0.5 : 1,
      ...style
    }
  }, leftIcon && /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'flex',
      color: 'var(--ink-500)',
      flexShrink: 0
    }
  }, leftIcon), /*#__PURE__*/React.createElement("input", _extends({
    type: type,
    value: value,
    onChange: onChange,
    placeholder: placeholder,
    disabled: disabled,
    onFocus: () => setFocus(true),
    onBlur: () => setFocus(false),
    style: {
      flex: 1,
      minWidth: 0,
      border: 'none',
      outline: 'none',
      background: 'transparent',
      font: 'var(--fw-medium) var(--fs-body)/1.3 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, rest)), rightIcon && /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'flex',
      color: 'var(--ink-500)',
      flexShrink: 0
    }
  }, rightIcon));
}
Object.assign(__ds_scope, { Input });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Input.jsx", error: String((e && e.message) || e) }); }

// components/navigation/BottomNav.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Bottom tab bar with a raised center FAB (the crimson scan button).
 * items: [{key, label, icon}] — 4 items, the FAB is injected in the middle.
 * Pass the FAB glyph via `fabIcon` and its handler via `onFab`.
 */
function BottomNav({
  items = [],
  active,
  onChange = () => {},
  fabIcon = null,
  onFab = () => {},
  style = {},
  ...rest
}) {
  const left = items.slice(0, Math.ceil(items.length / 2));
  const right = items.slice(Math.ceil(items.length / 2));
  const Tab = ({
    item
  }) => {
    const on = item.key === active;
    return /*#__PURE__*/React.createElement("button", {
      onClick: () => onChange(item.key),
      style: {
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: '4px',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        flex: 1,
        padding: '4px 0',
        color: on ? 'var(--brand)' : 'var(--ink-400)'
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        display: 'flex'
      }
    }, item.icon), /*#__PURE__*/React.createElement("span", {
      style: {
        font: `${on ? 'var(--fw-bold)' : 'var(--fw-medium)'} var(--fs-xs)/1 var(--font-sans)`
      }
    }, item.label));
  };
  return /*#__PURE__*/React.createElement("div", _extends({
    style: {
      position: 'relative',
      background: 'var(--white)',
      boxShadow: '0 -8px 24px rgba(26,26,26,0.06)',
      borderTopLeftRadius: 'var(--radius-xl)',
      borderTopRightRadius: 'var(--radius-xl)',
      padding: '14px 22px 20px',
      display: 'flex',
      alignItems: 'flex-start',
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flex: 1,
      gap: '4px'
    }
  }, left.map(it => /*#__PURE__*/React.createElement(Tab, {
    key: it.key,
    item: it
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      width: 78,
      flexShrink: 0
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flex: 1,
      gap: '4px'
    }
  }, right.map(it => /*#__PURE__*/React.createElement(Tab, {
    key: it.key,
    item: it
  }))), /*#__PURE__*/React.createElement("button", {
    onClick: onFab,
    style: {
      position: 'absolute',
      top: -22,
      left: '50%',
      transform: 'translateX(-50%)',
      width: 62,
      height: 62,
      borderRadius: 'var(--radius-circle)',
      border: '4px solid var(--white)',
      background: 'var(--brand)',
      color: '#fff',
      cursor: 'pointer',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: 'var(--shadow-brand)'
    }
  }, fabIcon));
}
Object.assign(__ds_scope, { BottomNav });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/BottomNav.jsx", error: String((e && e.message) || e) }); }

// components/surfaces/Card.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Base surface — a white rounded card with soft shadow. The core container of the app.
 * pad: 'sm' | 'md' | 'lg'. elevation: 'flat' | 'sm' | 'md' | 'lg'.
 */
function Card({
  children,
  pad = 'md',
  elevation = 'md',
  radius = 'lg',
  style = {},
  ...rest
}) {
  const pads = {
    none: '0',
    sm: '14px',
    md: '18px',
    lg: '22px'
  };
  const shadows = {
    flat: 'none',
    sm: 'var(--shadow-sm)',
    md: 'var(--shadow-md)',
    lg: 'var(--shadow-lg)'
  };
  const radii = {
    sm: 'var(--radius-sm)',
    md: 'var(--radius-md)',
    lg: 'var(--radius-lg)',
    xl: 'var(--radius-xl)'
  };
  return /*#__PURE__*/React.createElement("div", _extends({
    style: {
      background: 'var(--surface-card)',
      borderRadius: radii[radius] || radii.lg,
      padding: pads[pad],
      boxShadow: shadows[elevation],
      ...style
    }
  }, rest), children);
}
Object.assign(__ds_scope, { Card });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/surfaces/Card.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-app/HeroScreen.jsx
try { (() => {
/* Onboarding / marketing hero — the crimson gradient "Fast car" screen. */
function HeroScreen({
  onStart
}) {
  const {
    StatCard,
    Button
  } = window.CarTrakDesignSystem_588b8a;
  const stats = [{
    icon: 'Gauge',
    label: 'Engine Health',
    value: '92%'
  }, {
    icon: 'Disc3',
    label: 'Tire Condition',
    value: '85%'
  }, {
    icon: 'BatteryCharging',
    label: 'Battery Health',
    value: '78%'
  }, {
    icon: 'Fuel',
    label: 'Fuel Efficiency',
    value: '88%'
  }];
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      height: '100%',
      background: 'var(--grad-hero)'
    }
  }, /*#__PURE__*/React.createElement(StatusBar, {
    dark: true
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      flex: 1,
      display: 'flex',
      flexDirection: 'column'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 62px var(--font-serif)',
      color: 'rgba(255,255,255,0.22)',
      textAlign: 'center',
      letterSpacing: '-0.02em',
      marginTop: 6,
      lineHeight: 1
    }
  }, "Fast car"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: 12,
      padding: '0 22px',
      marginTop: -34
    }
  }, stats.map(s => /*#__PURE__*/React.createElement(StatCard, {
    key: s.label,
    tone: "glass",
    icon: /*#__PURE__*/React.createElement(Icon, {
      n: s.icon,
      s: 18
    }),
    label: s.label,
    value: s.value
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: 'rgba(120,20,40,0.5)'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Car",
    s: 132,
    strokeWidth: 1.1,
    color: "rgba(90,15,30,0.55)"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      background: '#fff',
      borderTopLeftRadius: 30,
      borderTopRightRadius: 30,
      padding: '26px 26px 30px',
      textAlign: 'center'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 26px/1.2 var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Your Car,"), /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 26px/1.2 var(--font-sans)',
      color: 'var(--brand)',
      marginBottom: 12
    }
  }, "Powered By All"), /*#__PURE__*/React.createElement("p", {
    style: {
      font: '400 15px/1.5 var(--font-serif-body)',
      color: 'var(--ink-600)',
      margin: '0 0 20px',
      maxWidth: 300,
      marginInline: 'auto'
    }
  }, "From vehicle scanning to maintenance tracking, everything managed automatically."), /*#__PURE__*/React.createElement(Button, {
    variant: "primary",
    block: true,
    size: "lg",
    onClick: onStart
  }, "Get started"))));
}
window.HeroScreen = HeroScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-app/HeroScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-app/HomeScreen.jsx
try { (() => {
/* Home dashboard — vehicle score, add report, popular cars. */
function HomeScreen() {
  const {
    IconButton,
    Button,
    ScoreGauge,
    VehicleCard,
    Card
  } = window.CarTrakDesignSystem_588b8a;
  const cars = [{
    name: 'S 500 Sedan',
    specs: ['Automatic', '5 seats', 'Diesel']
  }, {
    name: 'GLA 250 SUV',
    specs: ['Automatic', '5 seats', 'Petrol']
  }];
  return /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      overflowY: 'auto',
      paddingBottom: 120
    }
  }, /*#__PURE__*/React.createElement(StatusBar, null), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '8px 20px 4px'
    }
  }, /*#__PURE__*/React.createElement(IconButton, {
    tone: "light",
    size: 44
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Calendar",
    s: 20
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 16px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "20 Aug 2026"), /*#__PURE__*/React.createElement(IconButton, {
    tone: "light",
    size: 44
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Bell",
    s: 20
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      padding: '10px 20px 0'
    }
  }, /*#__PURE__*/React.createElement(ScoreGauge, {
    value: 85,
    max: 100,
    size: 250,
    label: "Vehicle Score",
    sublabel: "Target Score 100",
    icon: /*#__PURE__*/React.createElement(Icon, {
      n: "Zap",
      s: 22,
      color: "var(--brand)"
    })
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '4px 20px 0'
    }
  }, /*#__PURE__*/React.createElement(Button, {
    variant: "dark",
    block: true,
    size: "md",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Plus",
      s: 18
    }),
    style: {
      background: '#fff',
      color: 'var(--ink-900)',
      border: '1.5px solid transparent',
      boxShadow: 'var(--shadow-md)'
    }
  }, "Add Vehicle Report")), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '22px 20px 0'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 22px var(--font-sans)',
      color: 'var(--ink-900)',
      marginBottom: 14
    }
  }, "Popular Cars"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 16
    }
  }, cars.map(c => /*#__PURE__*/React.createElement(VehicleCard, {
    key: c.name,
    name: c.name,
    specs: c.specs,
    image: /*#__PURE__*/React.createElement(Icon, {
      n: "Car",
      s: 56,
      strokeWidth: 1.2,
      color: "var(--ink-800)"
    }),
    primaryAction: /*#__PURE__*/React.createElement(Button, {
      size: "sm"
    }, "Rent Now"),
    secondaryAction: /*#__PURE__*/React.createElement(Button, {
      size: "sm",
      variant: "secondary"
    }, "Detail")
  })))));
}
window.HomeScreen = HomeScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-app/HomeScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-app/ServicesScreen.jsx
try { (() => {
/* Services / discover — search, brands carousel, featured service. */
function ServicesScreen() {
  const {
    Input,
    IconButton,
    Badge,
    BrandChip,
    Card
  } = window.CarTrakDesignSystem_588b8a;
  const brands = [{
    name: 'Mercedes',
    count: 32
  }, {
    name: 'BMW',
    count: 12
  }, {
    name: 'Porsche',
    count: 8
  }, {
    name: 'Renault',
    count: 5
  }, {
    name: 'Audi',
    count: 9
  }];
  const brandLogo = n => /*#__PURE__*/React.createElement("div", {
    style: {
      width: 42,
      height: 42,
      borderRadius: '50%',
      background: 'var(--surface-sunken)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      font: '800 16px var(--font-sans)',
      color: 'var(--ink-700)'
    }
  }, n[0]);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      overflowY: 'auto',
      paddingBottom: 120
    }
  }, /*#__PURE__*/React.createElement(StatusBar, null), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 12,
      alignItems: 'center',
      padding: '10px 20px 4px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement(Input, {
    placeholder: "Search Services",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Search",
      s: 18
    })
  })), /*#__PURE__*/React.createElement(IconButton, {
    tone: "brand",
    size: 48
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Bell",
    s: 20
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '18px 20px 12px'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      font: '800 22px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Brands"), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 13px var(--font-sans)',
      color: 'var(--brand)',
      display: 'flex',
      alignItems: 'center',
      gap: 2
    }
  }, "See All ", /*#__PURE__*/React.createElement(Icon, {
    n: "ChevronRight",
    s: 15,
    color: "var(--brand)"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 16,
      padding: '0 20px 6px',
      overflowX: 'auto'
    }
  }, brands.map((b, i) => /*#__PURE__*/React.createElement(BrandChip, {
    key: b.name,
    name: b.name,
    count: b.count,
    logo: brandLogo(b.name),
    active: i === 0
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '18px 20px 0'
    }
  }, /*#__PURE__*/React.createElement(Card, {
    pad: "none",
    elevation: "md",
    style: {
      overflow: 'hidden'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      height: 168,
      background: 'var(--surface-sunken)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Car",
    s: 92,
    strokeWidth: 1,
    color: "var(--ink-400)"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 12,
      right: 12,
      width: 34,
      height: 34,
      borderRadius: '50%',
      background: '#fff',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: 'var(--shadow-sm)'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Heart",
    s: 17,
    color: "var(--brand)"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 18
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '700 18px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Full Vehicle Inspection"), /*#__PURE__*/React.createElement("p", {
    style: {
      font: '400 14px/1.5 var(--font-serif-body)',
      color: 'var(--ink-600)',
      margin: '6px 0 14px'
    }
  }, "The Mercedes SL 63 AMG is a sports car created using advanced diagnostics."), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between'
    }
  }, /*#__PURE__*/React.createElement(Badge, {
    tone: "brand",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Clock",
      s: 13,
      color: "#fff"
    })
  }, "45 min"), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 13px var(--font-sans)',
      color: 'var(--ink-900)',
      display: 'flex',
      alignItems: 'center',
      gap: 6
    }
  }, "100 Speed ", /*#__PURE__*/React.createElement(Icon, {
    n: "Signal",
    s: 16,
    color: "var(--brand)"
  })))))));
}
window.ServicesScreen = ServicesScreen;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-app/ServicesScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-app/kit.jsx
try { (() => {
/* Shared kit helpers — Icon (Lucide), StatusBar, PhoneFrame. Exported to window. */
function Icon({
  n,
  s = 22,
  color,
  strokeWidth = 2,
  style = {}
}) {
  const ref = React.useRef();
  React.useEffect(() => {
    if (ref.current && window.lucide && lucide[n]) {
      ref.current.innerHTML = '';
      const el = lucide.createElement(lucide[n]);
      el.setAttribute('width', s);
      el.setAttribute('height', s);
      el.setAttribute('stroke-width', strokeWidth);
      ref.current.appendChild(el);
    }
  }, [n, s, strokeWidth]);
  return /*#__PURE__*/React.createElement("span", {
    ref: ref,
    style: {
      display: 'inline-flex',
      color,
      ...style
    }
  });
}
function StatusBar({
  dark = false
}) {
  const c = dark ? '#fff' : 'var(--ink-900)';
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '14px 26px 4px',
      color: c
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 15px var(--font-sans)'
    }
  }, "9:41"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 6,
      alignItems: 'center'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "SignalHigh",
    s: 17,
    color: c
  }), /*#__PURE__*/React.createElement(Icon, {
    n: "Wifi",
    s: 16,
    color: c
  }), /*#__PURE__*/React.createElement(Icon, {
    n: "BatteryFull",
    s: 20,
    color: c
  })));
}

/* iPhone-ish frame, 390x844 content area with home indicator */
function PhoneFrame({
  children,
  bg = 'var(--surface-page)'
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      width: 390,
      height: 844,
      borderRadius: 46,
      background: '#000',
      padding: 5,
      boxShadow: '0 40px 90px rgba(26,26,26,0.28)',
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      borderRadius: 42,
      background: bg,
      overflow: 'hidden',
      position: 'relative',
      display: 'flex',
      flexDirection: 'column'
    }
  }, children));
}
Object.assign(window, {
  Icon,
  StatusBar,
  PhoneFrame
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-app/kit.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-desktop/Dashboard.jsx
try { (() => {
/* Desktop dashboard body — score, health metrics, popular cars, featured service. */
function Dashboard() {
  const {
    Card,
    ScoreGauge,
    StatCard,
    VehicleCard,
    Button,
    Badge,
    BrandChip
  } = window.CarTrakDesignSystem_588b8a;
  const stats = [{
    icon: 'Gauge',
    label: 'Engine Health',
    value: '92%'
  }, {
    icon: 'Disc3',
    label: 'Tire Condition',
    value: '85%'
  }, {
    icon: 'BatteryCharging',
    label: 'Battery Health',
    value: '78%'
  }, {
    icon: 'Fuel',
    label: 'Fuel Efficiency',
    value: '88%'
  }];
  const cars = [{
    name: 'S 500 Sedan',
    specs: ['Automatic', '5 seats', 'Diesel']
  }, {
    name: 'GLA 250 SUV',
    specs: ['Automatic', '5 seats', 'Petrol']
  }];
  const brands = [{
    name: 'Mercedes',
    count: 32
  }, {
    name: 'BMW',
    count: 12
  }, {
    name: 'Porsche',
    count: 8
  }, {
    name: 'Audi',
    count: 9
  }];
  const brandLogo = n => /*#__PURE__*/React.createElement("div", {
    style: {
      width: 40,
      height: 40,
      borderRadius: '50%',
      background: 'var(--surface-sunken)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      font: '800 15px var(--font-sans)',
      color: 'var(--ink-700)'
    }
  }, n[0]);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      overflowY: 'auto',
      padding: '28px 32px',
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fit, minmax(340px, 1fr))',
      gap: 24,
      alignContent: 'start'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 24,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement(Card, {
    elevation: "md",
    style: {
      display: 'flex',
      flexWrap: 'wrap',
      alignItems: 'center',
      gap: 32,
      padding: 28
    }
  }, /*#__PURE__*/React.createElement(ScoreGauge, {
    value: 85,
    max: 100,
    size: 230,
    label: "Vehicle Score",
    sublabel: "Target Score 100",
    icon: /*#__PURE__*/React.createElement(Icon, {
      n: "Zap",
      s: 22,
      color: "var(--brand)"
    })
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 240
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 20px var(--font-sans)',
      color: 'var(--ink-900)',
      marginBottom: 6
    }
  }, "Your car is in great shape"), /*#__PURE__*/React.createElement("p", {
    style: {
      font: '400 15px/1.6 var(--font-serif-body)',
      color: 'var(--ink-600)',
      margin: '0 0 18px'
    }
  }, "Overall condition is strong. Next scheduled service is due in 1,200 miles. Run a full scan to update these numbers."), /*#__PURE__*/React.createElement(Button, {
    variant: "primary",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Plus",
      s: 18
    })
  }, "Add Vehicle Report"))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(4,1fr)',
      gap: 16
    }
  }, stats.map(s => /*#__PURE__*/React.createElement(StatCard, {
    key: s.label,
    icon: /*#__PURE__*/React.createElement(Icon, {
      n: s.icon,
      s: 18
    }),
    label: s.label,
    value: s.value
  }))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      marginBottom: 14
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      font: '800 20px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Popular Cars"), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 13px var(--font-sans)',
      color: 'var(--brand)',
      display: 'flex',
      alignItems: 'center',
      gap: 2,
      cursor: 'pointer'
    }
  }, "See All ", /*#__PURE__*/React.createElement(Icon, {
    n: "ChevronRight",
    s: 15,
    color: "var(--brand)"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: 16
    }
  }, cars.map(c => /*#__PURE__*/React.createElement(VehicleCard, {
    key: c.name,
    name: c.name,
    specs: c.specs,
    image: /*#__PURE__*/React.createElement(Icon, {
      n: "Car",
      s: 52,
      strokeWidth: 1.2,
      color: "var(--ink-800)"
    }),
    primaryAction: /*#__PURE__*/React.createElement(Button, {
      size: "sm"
    }, "Rent Now"),
    secondaryAction: /*#__PURE__*/React.createElement(Button, {
      size: "sm",
      variant: "secondary"
    }, "Detail")
  }))))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 24,
      minWidth: 0
    }
  }, /*#__PURE__*/React.createElement(Card, {
    pad: "none",
    elevation: "md",
    style: {
      overflow: 'hidden'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative',
      height: 176,
      background: 'var(--surface-sunken)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Car",
    s: 96,
    strokeWidth: 1,
    color: "var(--ink-400)"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 14,
      right: 14,
      width: 36,
      height: 36,
      borderRadius: '50%',
      background: '#fff',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: 'var(--shadow-sm)'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Heart",
    s: 17,
    color: "var(--brand)"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 20
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '700 18px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Full Vehicle Inspection"), /*#__PURE__*/React.createElement("p", {
    style: {
      font: '400 14px/1.5 var(--font-serif-body)',
      color: 'var(--ink-600)',
      margin: '6px 0 14px'
    }
  }, "A complete 45-minute diagnostic across every major system, from engine to brakes."), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between'
    }
  }, /*#__PURE__*/React.createElement(Badge, {
    tone: "brand",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Clock",
      s: 13,
      color: "#fff"
    })
  }, "45 min"), /*#__PURE__*/React.createElement(Button, {
    size: "sm"
  }, "Book now")))), /*#__PURE__*/React.createElement(Card, {
    elevation: "md",
    style: {
      padding: 22
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      font: '800 18px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Brands"), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '700 13px var(--font-sans)',
      color: 'var(--brand)',
      cursor: 'pointer'
    }
  }, "See All")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(4,1fr)',
      gap: 12,
      justifyItems: 'center'
    }
  }, brands.map((b, i) => /*#__PURE__*/React.createElement(BrandChip, {
    key: b.name,
    name: b.name,
    count: b.count,
    logo: brandLogo(b.name),
    active: i === 0
  }))))));
}
window.Dashboard = Dashboard;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-desktop/Dashboard.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-desktop/Sidebar.jsx
try { (() => {
/* Desktop left sidebar — brand wordmark, nav, scan CTA. */
function Sidebar({
  active,
  onChange,
  onScan
}) {
  const nav = [{
    key: 'home',
    label: 'Dashboard',
    icon: 'House'
  }, {
    key: 'reports',
    label: 'Reports',
    icon: 'ChartColumn'
  }, {
    key: 'services',
    label: 'Services',
    icon: 'Wrench'
  }, {
    key: 'saved',
    label: 'Saved',
    icon: 'Heart'
  }, {
    key: 'settings',
    label: 'Settings',
    icon: 'Settings'
  }];
  return /*#__PURE__*/React.createElement("aside", {
    style: {
      width: 248,
      flexShrink: 0,
      background: '#fff',
      borderRight: '1px solid var(--border-subtle)',
      display: 'flex',
      flexDirection: 'column',
      padding: '26px 18px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      padding: '0 10px 26px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 34,
      height: 34,
      borderRadius: 10,
      background: 'var(--brand)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: 'var(--shadow-brand)'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Car",
    s: 20,
    color: "#fff",
    strokeWidth: 2.2
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      font: '800 20px var(--font-sans)',
      color: 'var(--ink-900)',
      letterSpacing: '-0.02em'
    }
  }, "CarTrak")), /*#__PURE__*/React.createElement("nav", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 4,
      flex: 1
    }
  }, nav.map(n => {
    const on = n.key === active;
    return /*#__PURE__*/React.createElement("button", {
      key: n.key,
      onClick: () => onChange(n.key),
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 12,
        padding: '12px 14px',
        borderRadius: 'var(--radius-md)',
        border: 'none',
        cursor: 'pointer',
        textAlign: 'left',
        width: '100%',
        background: on ? 'var(--red-50)' : 'transparent',
        color: on ? 'var(--brand)' : 'var(--ink-600)',
        font: `${on ? '700' : '500'} 15px var(--font-sans)`
      }
    }, /*#__PURE__*/React.createElement(Icon, {
      n: n.icon,
      s: 20,
      color: on ? 'var(--brand)' : 'var(--ink-500)'
    }), n.label);
  })), /*#__PURE__*/React.createElement("button", {
    onClick: onScan,
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 10,
      padding: '15px',
      borderRadius: 'var(--radius-pill)',
      border: 'none',
      cursor: 'pointer',
      background: 'var(--brand)',
      color: '#fff',
      font: '700 15px var(--font-sans)',
      boxShadow: 'var(--shadow-brand)'
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "ScanLine",
    s: 20,
    color: "#fff"
  }), " Scan Vehicle"));
}
window.Sidebar = Sidebar;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-desktop/Sidebar.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-desktop/TopBar.jsx
try { (() => {
/* Desktop top bar — greeting, search, date, notifications, profile. */
function TopBar() {
  const {
    Input,
    IconButton
  } = window.CarTrakDesignSystem_588b8a;
  return /*#__PURE__*/React.createElement("header", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: 24,
      padding: '22px 32px',
      borderBottom: '1px solid var(--border-subtle)',
      background: 'var(--surface-page)'
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    style: {
      font: '800 24px var(--font-sans)',
      color: 'var(--ink-900)'
    }
  }, "Good morning, Alex"), /*#__PURE__*/React.createElement("div", {
    style: {
      font: '400 14px var(--font-serif-body)',
      color: 'var(--ink-500)',
      marginTop: 2
    }
  }, "Here's how your garage is doing \u2014 20 Aug 2026.")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 14
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 300
    }
  }, /*#__PURE__*/React.createElement(Input, {
    placeholder: "Search services, cars\u2026",
    leftIcon: /*#__PURE__*/React.createElement(Icon, {
      n: "Search",
      s: 18
    })
  })), /*#__PURE__*/React.createElement(IconButton, {
    tone: "light",
    size: 46
  }, /*#__PURE__*/React.createElement(Icon, {
    n: "Bell",
    s: 20
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      width: 46,
      height: 46,
      borderRadius: '50%',
      background: 'var(--grad-brand)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#fff',
      font: '700 16px var(--font-sans)',
      boxShadow: 'var(--shadow-sm)'
    }
  }, "A")));
}
window.TopBar = TopBar;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-desktop/TopBar.jsx", error: String((e && e.message) || e) }); }

// ui_kits/cartrak-desktop/kit.jsx
try { (() => {
/* Shared helper for the desktop kit — Icon (Lucide). Exported to window. */
function Icon({
  n,
  s = 20,
  color,
  strokeWidth = 2,
  style = {}
}) {
  const ref = React.useRef();
  React.useEffect(() => {
    if (ref.current && window.lucide && lucide[n]) {
      ref.current.innerHTML = '';
      const el = lucide.createElement(lucide[n]);
      el.setAttribute('width', s);
      el.setAttribute('height', s);
      el.setAttribute('stroke-width', strokeWidth);
      ref.current.appendChild(el);
    }
  }, [n, s, strokeWidth]);
  return /*#__PURE__*/React.createElement("span", {
    ref: ref,
    style: {
      display: 'inline-flex',
      color,
      ...style
    }
  });
}
window.Icon = Icon;
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/cartrak-desktop/kit.jsx", error: String((e && e.message) || e) }); }

__ds_ns.Button = __ds_scope.Button;

__ds_ns.IconButton = __ds_scope.IconButton;

__ds_ns.Badge = __ds_scope.Badge;

__ds_ns.BrandChip = __ds_scope.BrandChip;

__ds_ns.ScoreGauge = __ds_scope.ScoreGauge;

__ds_ns.StatCard = __ds_scope.StatCard;

__ds_ns.VehicleCard = __ds_scope.VehicleCard;

__ds_ns.Input = __ds_scope.Input;

__ds_ns.BottomNav = __ds_scope.BottomNav;

__ds_ns.Card = __ds_scope.Card;

})();
