import React from 'react';

export interface BrandChipProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  name: string;
  /** The brand logo element (img/svg) */
  logo?: React.ReactNode;
  /** Additional-models count shown as "+N" */
  count?: number;
  active?: boolean;
}

/** Rounded brand tile for the "Brands" carousel — logo chip + name + count. */
export function BrandChip(props: BrandChipProps): JSX.Element;
