import { useSyncExternalStore } from "react";
import { THEME_KEY, isThemeMode, resolveTheme, type ThemeMode } from "@/lib/theme";

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

export function setThemeMode(mode: ThemeMode) {
  try {
    localStorage.setItem(THEME_KEY, mode);
  } catch {}
  apply(mode);
  listeners.forEach((listener) => listener());
}

export function toggleTheme() {
  setThemeMode(document.documentElement.dataset.theme === "dark" ? "light" : "dark");
}

export function useThemeMode(): ThemeMode {
  return useSyncExternalStore(subscribe, readMode, () => "dark" as ThemeMode);
}
