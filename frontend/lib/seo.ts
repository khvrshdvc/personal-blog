export const SITE_NAME = "Utkirbek";

export const SITE_DESCRIPTION = "Utkirbek — oʻrganish, ustozlik, til va hayot haqida fikrlar. Alohida boʻlimda dasturlash.";

export function siteUrl(): string {
  return (process.env.SITE_URL ?? "http://localhost:3000").replace(/\/$/, "");
}

export function absoluteUrl(path: string): string {
  return `${siteUrl()}${path.startsWith("/") ? path : `/${path}`}`;
}

export function xml(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&apos;");
}

export function jsonLd(data: unknown): string {
  return JSON.stringify(data).replace(/</g, "\\u003c");
}

export function pageAlternates(canonical: string) {
  return { canonical, types: { "application/rss+xml": "/rss.xml" } };
}
