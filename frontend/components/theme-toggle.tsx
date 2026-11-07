"use client";

import { useSyncExternalStore } from "react";
import { THEME_KEY, THEME_MODES, isThemeMode, resolveTheme, type ThemeMode } from "@/lib/theme";

const LABELS: Record<ThemeMode, string> = {
  dark: "Qorongʻi",
  light: "Yorugʻ",
  auto: "Avto",
};

const listeners = new Set<() => void>();

function readMode(): ThemeMode {
  try {
    const stored = localStorage.getItem(THEME_KEY);
    return isThemeMode(stored) ? stored : "dark";
  } catch {
    return "dark";
  }
}

function apply(mode: ThemeMode) {
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  document.documentElement.dataset.theme = resolveTheme(mode, prefersDark);
}

function subscribe(callback: () => void) {
  listeners.add(callback);
  const query = window.matchMedia("(prefers-color-scheme: dark)");
  const onChange = () => {
    apply(readMode());
    callback();
  };
  query.addEventListener("change", onChange);
  return () => {
    listeners.delete(callback);
    query.removeEventListener("change", onChange);
  };
}

function setMode(mode: ThemeMode) {
  try {
    localStorage.setItem(THEME_KEY, mode);
  } catch {}
  apply(mode);
  listeners.forEach((listener) => listener());
}

export function ThemeToggle() {
  const mode = useSyncExternalStore(subscribe, readMode, () => "dark" as ThemeMode);

  return (
    <div role="radiogroup" aria-label="Mavzu" className="inline-flex gap-1 rounded-full bg-chip p-1">
      {THEME_MODES.map((item) => (
        <button
          key={item}
          type="button"
          role="radio"
          aria-checked={mode === item}
          onClick={() => setMode(item)}
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
