import React from 'react';

export interface BottomNavItem {
  key: string;
  label: string;
  icon: React.ReactNode;
}

/**
 * @startingPoint section="Navigation" subtitle="Tab bar + scan FAB" viewport="390x120"
 */
export interface BottomNavProps extends React.HTMLAttributes<HTMLDivElement> {
  /** Tab items; the FAB is injected in the middle */
  items: BottomNavItem[];
  /** key of the active tab */
  active?: string;
  onChange?: (key: string) => void;
  /** Glyph inside the raised center FAB */
  fabIcon?: React.ReactNode;
  onFab?: () => void;
}

/** Bottom tab bar with a raised crimson scan FAB in the center. */
export function BottomNav(props: BottomNavProps): JSX.Element;
