import React from 'react';

/**
 * @startingPoint section="Data" subtitle="Vehicle score dial" viewport="300x200"
 */
export interface ScoreGaugeProps extends React.HTMLAttributes<HTMLDivElement> {
  /** Current score. @default 85 */
  value?: number;
  /** @default 100 */
  max?: number;
  /** Number of arc segments. @default 20 */
  segments?: number;
  /** @default 'Vehicle Score' */
  label?: string;
  /** Small line under the number, e.g. "Target Score 100" */
  sublabel?: React.ReactNode;
  /** Center glyph */
  icon?: React.ReactNode;
  /** Width in px. @default 260 */
  size?: number;
}

/** Segmented semicircular score gauge (the "Vehicle Score 85/100" dial). */
export function ScoreGauge(props: ScoreGaugeProps): JSX.Element;
