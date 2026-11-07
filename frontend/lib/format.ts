const SHORT = ["Yan", "Fev", "Mar", "Apr", "May", "Iyn", "Iyl", "Avg", "Sen", "Okt", "Noy", "Dek"];
const MONTHS = ["yanvar", "fevral", "mart", "aprel", "may", "iyun", "iyul", "avgust", "sentabr", "oktabr", "noyabr", "dekabr"];

export const SHORT_MONTHS = SHORT;

function parts(iso: string | null | undefined): [number, number, number] | null {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso ?? "");

  return match ? [Number(match[1]), Number(match[2]), Number(match[3])] : null;
}

export function fmtShort(iso: string | null | undefined): string {
  const p = parts(iso);

  return p ? `${p[2]} ${SHORT[p[1] - 1].toLowerCase()}` : "";
}

export function fmtLong(iso: string | null | undefined): string {
  const p = parts(iso);

  return p ? `${p[2]}-${MONTHS[p[1] - 1]}, ${p[0]}` : "";
}

export function ago(iso: string, now: number): string {
  const seconds = Math.max(0, Math.floor((now - new Date(iso).getTime()) / 1000));

  if (seconds < 60) return "hozirgina";
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes} daqiqa oldin`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} soat oldin`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days} kun oldin`;
  if (days < 30) return `${Math.floor(days / 7)} hafta oldin`;
  if (days < 365) return `${Math.floor(days / 30)} oy oldin`;

  return `${Math.floor(days / 365)} yil oldin`;
}

export function duration(seconds: number | null): string {
  if (seconds === null) return "—";

  const total = Math.max(0, Math.round(seconds));
  const minutes = Math.floor(total / 60);
  const rest = total % 60;

  return minutes ? `${minutes}m ${String(rest).padStart(2, "0")}s` : `${rest}s`;
}

export function initials(name: string | null | undefined): string {
  const words = String(name ?? "").split(/[^A-Za-z0-9ʻʼ']+/).filter(Boolean);
  const first = words[0] ?? "?";
  const second = words[1] ? words[1][0] : first.slice(1, 2);

  return (first[0] + second).toUpperCase();
}

export function clampPercent(value: number): number {
  return Math.max(0, Math.min(100, Math.round(Number(value) || 0)));
}

export function repoName(repo: string | null | undefined): string {
  return (repo ?? "").split("/").pop()?.toLowerCase() ?? "";
}

export function githubUrl(repo: string | null | undefined): string | null {
  const value = (repo ?? "").trim();

  return value.includes("/") ? `https://github.com/${value}` : null;
}
