import React from 'react';

/**
 * @startingPoint section="Forms" subtitle="Search + text field" viewport="360x90"
 */
export interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'style'> {
  value?: string;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  placeholder?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  type?: string;
  disabled?: boolean;
  /** 'pill' (default) or 'md' rounded */
  rounded?: 'pill' | 'md';
  style?: React.CSSProperties;
}

/** Rounded input / search field on a white chip. */
export function Input(props: InputProps): JSX.Element;
