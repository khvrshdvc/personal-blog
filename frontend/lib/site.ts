import type { IconName } from "@/lib/icon-paths";

export const TEACH = { v: "12–18", t: "yoshli oʻquvchilarga dars" };

export const STACK: { name: string; icon: IconName; items: string[] }[] = [
  { name: "Backend", icon: "code", items: ["PHP", "Laravel", "Symfony", "REST API", "Composer"] },
  { name: "Maʼlumotlar bazasi", icon: "db", items: ["MySQL", "PostgreSQL", "Redis", "Eloquent"] },
  { name: "DevOps", icon: "cube", items: ["Docker", "Linux (WSL)", "Git", "GitHub Actions", "Laravel Sail"] },
  { name: "Boshqa tillar", icon: "brief", items: ["Python", "C", "JavaScript"] },
];

export const LANG_COLORS: Record<string, string> = {
  PHP: "#4F5D95",
  Python: "#3572A5",
  JavaScript: "#f1e05a",
  TypeScript: "#3178c6",
  Blade: "#f7523f",
  Vue: "#41b883",
  Shell: "#89e051",
  HTML: "#e34c26",
  CSS: "#563d7c",
  Dart: "#00B4AB",
  Go: "#00ADD8",
};

export const RUN_LABELS = {
  success: "Muvaffaqiyatli",
  failure: "Xato",
  running: "Ishlamoqda",
  skipped: "Oʻtkazib yuborildi",
} as const;

export const CI_LABELS = {
  success: "CI oʻtdi",
  failure: "CI xato",
  running: "CI ishlamoqda",
  skipped: "CI oʻtkazildi",
} as const;

export function mentions(text: string, item: string): boolean {
  const escaped = item.toLowerCase().replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

  return new RegExp(`(?<![\\p{L}\\p{N}])${escaped}(?![\\p{L}\\p{N}])`, "u").test(text.toLowerCase());
}
