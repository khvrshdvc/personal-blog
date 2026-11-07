"use client";

import { Seg } from "@/components/seg";
import { isThemeMode } from "@/lib/theme";
import { setThemeMode, useThemeMode } from "@/lib/theme-store";

const ITEMS = [
  { key: "light", label: "Yorugʻ" },
  { key: "dark", label: "Qorongʻi" },
  { key: "auto", label: "Avto" },
];

export function ThemeSeg() {
  const mode = useThemeMode();

  return (
    <Seg
      items={ITEMS}
      active={mode}
      label="Mavzu"
      onPick={(key) => {
        if (isThemeMode(key)) setThemeMode(key);
      }}
    />
  );
}
