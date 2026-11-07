export const THEME_KEY = "ut4_theme";

export type ThemeMode = "dark" | "light" | "auto";

export const THEME_MODES: ThemeMode[] = ["dark", "light", "auto"];

export const THEME_SCRIPT = `(function(){var r=document.documentElement,m="dark";try{m=localStorage.getItem("${THEME_KEY}")||"dark"}catch(e){}var d=m==="dark"||(m==="auto"&&window.matchMedia&&matchMedia("(prefers-color-scheme: dark)").matches);r.dataset.theme=d?"dark":"light"})();`;

export function isThemeMode(value: unknown): value is ThemeMode {
  return value === "dark" || value === "light" || value === "auto";
}

export function resolveTheme(mode: ThemeMode, prefersDark: boolean): "dark" | "light" {
  return mode === "dark" || (mode === "auto" && prefersDark) ? "dark" : "light";
}
