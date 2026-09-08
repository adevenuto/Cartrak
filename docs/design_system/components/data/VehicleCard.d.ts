import React from 'react';

/**
 * @startingPoint section="Data" subtitle="Vehicle listing card" viewport="380x210"
 */
export interface VehicleCardProps extends React.HTMLAttributes<HTMLDivElement> {
  name: string;
  /** Spec strings shown as a divided row, e.g. ['Automatic','5 seats','Diesel'] */
  specs?: string[];
  /** An <img> element of the vehicle */
  image?: React.ReactNode;
  price?: React.ReactNode;
  /** Usually a <Button> */
  primaryAction?: React.ReactNode;
  secondaryAction?: React.ReactNode;
}

/** Vehicle listing card with name, spec row, image, and actions. */
export function VehicleCard(props: VehicleCardProps): JSX.Element;
