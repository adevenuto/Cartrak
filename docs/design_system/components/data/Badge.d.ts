import React from 'react';

export type BadgeTone = 'neutral' | 'brand' | 'brand-soft' | 'dark' | 'outline';

export interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  children?: React.ReactNode;
  /** @default 'neutral' */
  tone?: BadgeTone;
  /** @default 'md' */
  size?: 'sm' | 'md';
  leftIcon?: React.ReactNode;
}

/** Small pill label for specs, counts, durations and status. */
export function Badge(props: BadgeProps): JSX.Element;
