import React from 'react';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'dark';
export type ButtonSize = 'sm' | 'md' | 'lg';

/**
 * @startingPoint section="Actions" subtitle="Pill button, 4 variants" viewport="360x120"
 */
export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /** Button label / content */
  children?: React.ReactNode;
  /** Visual style. @default 'primary' */
  variant?: ButtonVariant;
  /** @default 'md' */
  size?: ButtonSize;
  /** Full-width block button */
  block?: boolean;
  disabled?: boolean;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
}

/** Pill-shaped action button in CarTrak crimson. */
export function Button(props: ButtonProps): JSX.Element;
