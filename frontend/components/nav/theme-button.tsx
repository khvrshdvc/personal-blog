"use client";

import { Icon } from "@/components/icon";
import { toggleTheme } from "@/lib/theme-store";

interface ThemeButtonProps {
  className: string;
  children?: React.ReactNode;
}

export function ThemeButton({ className, children }: ThemeButtonProps) {
  return (
    <button type="button" className={className} onClick={toggleTheme} aria-label="Mavzuni almashtirish">
      <Icon name="moon" className="t-moon" />
      <Icon name="sun" className="t-sun" />
      {children}
    </button>
  );
}
