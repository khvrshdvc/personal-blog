"use client";

import { THEME_MODES, type ThemeMode } from "@/lib/theme";
import { setThemeMode, useThemeMode } from "@/lib/theme-store";

const LABELS: Record<ThemeMode, string> = {
  dark: "Qorongʻi",
  light: "Yorugʻ",
  auto: "Avto",
};

export function ThemeToggle() {
  const mode = useThemeMode();

  return (
    <div role="radiogroup" aria-label="Mavzu" className="inline-flex gap-1 rounded-full bg-chip p-1">
      {THEME_MODES.map((item) => (
        <button
          key={item}
          type="button"
          role="radio"
          aria-checked={mode === item}
          onClick={() => setThemeMode(item)}
          className={`h-9 rounded-full px-4 text-[14px] font-medium transition-colors ${
            mode === item ? "bg-inv text-inv-t" : "text-t2 hover:text-t1"
          }`}
        >
          {LABELS[item]}
        </button>
      ))}
    </div>
  );
}
