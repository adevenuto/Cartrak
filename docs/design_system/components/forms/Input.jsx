import React from 'react';

/**
 * Text input / search field. Pill-shaped rounded field on white with soft shadow.
 * Pass leftIcon (e.g. a search glyph) for the "Search Services" pattern.
 */
export function Input({
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
  return (
    <div
      style={{
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
        ...style,
      }}
    >
      {leftIcon && <span style={{ display: 'flex', color: 'var(--ink-500)', flexShrink: 0 }}>{leftIcon}</span>}
      <input
        type={type}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        disabled={disabled}
        onFocus={() => setFocus(true)}
        onBlur={() => setFocus(false)}
        style={{
          flex: 1,
          minWidth: 0,
          border: 'none',
          outline: 'none',
          background: 'transparent',
          font: 'var(--fw-medium) var(--fs-body)/1.3 var(--font-sans)',
          color: 'var(--ink-900)',
        }}
        {...rest}
      />
      {rightIcon && <span style={{ display: 'flex', color: 'var(--ink-500)', flexShrink: 0 }}>{rightIcon}</span>}
    </div>
  );
}
