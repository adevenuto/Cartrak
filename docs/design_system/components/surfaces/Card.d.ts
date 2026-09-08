import React from 'react';

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  children?: React.ReactNode;
  /** @default 'md' */
  pad?: 'none' | 'sm' | 'md' | 'lg';
  /** @default 'md' */
  elevation?: 'flat' | 'sm' | 'md' | 'lg';
  /** @default 'lg' */
  radius?: 'sm' | 'md' | 'lg' | 'xl';
}

/** White rounded surface with soft shadow — the app's core container. */
export function Card(props: CardProps): JSX.Element;
