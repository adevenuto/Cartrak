import React from 'react';

/**
 * @startingPoint section="Data" subtitle="Metric tile" viewport="220x110"
 */
export interface StatCardProps extends React.HTMLAttributes<HTMLDivElement> {
  icon?: React.ReactNode;
  label: string;
  value: React.ReactNode;
  /** @default 'card' */
  tone?: 'card' | 'glass' | 'sunken';
}

/** Compact metric tile: icon + label + big value (e.g. "Engine Health 92%"). */
export function StatCard(props: StatCardProps): JSX.Element;
