import React from 'react';

export type IconButtonTone = 'light' | 'brand' | 'ghost' | 'dark';

export interface IconButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /** The icon glyph/SVG */
  children?: React.ReactNode;
  /** @default 'light' */
  tone?: IconButtonTone;
  /** Diameter in px. @default 44 */
  size?: number;
  disabled?: boolean;
}

/** Circular icon button used in headers and toolbars. */
export function IconButton(props: IconButtonProps): JSX.Element;
