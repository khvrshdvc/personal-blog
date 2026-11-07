import type { AdminPost, PostFields, PostState } from "@/lib/admin/types";
import { storageUrl } from "@/lib/admin/api";

export const CATEGORY_TAGS = {
  fikr: ["Hayot", "Oʻsish", "Ustozlik", "Til"],
  dev: ["Laravel", "PHP", "SQL", "Docker"],
} as const;

export function postState(post: AdminPost): PostState {
  if (post.status !== "published") return "draft";

  return post.pending ? "pending" : "published";
}

export const STATE_LABEL: Record<PostState, string> = {
  draft: "Qoralama",
  published: "Chop etilgan",
  pending: "Oʻzgarish bor",
};

export const STATE_CLASS: Record<PostState, string> = {
  draft: "draft",
  published: "pub",
  pending: "chg",
};

export function postView(post: AdminPost): PostFields {
  const base: PostFields = {
    category: post.category,
    title: post.title ?? "",
    slug: post.slug ?? "",
    tag: post.tag ?? "",
    excerpt: post.excerpt ?? "",
    body: post.body ?? "",
    published_at: post.published_at ?? "",
    cover: post.cover ?? "",
  };

  return post.pending ? { ...base, ...post.pending } : base;
}

export function tashkentToday(): string {
  return new Date().toLocaleDateString("sv-SE", { timeZone: "Asia/Tashkent" });
}

export function emptyFields(category: "fikr" | "dev"): PostFields {
  return {
    category,
    title: "",
    slug: "",
    tag: CATEGORY_TAGS[category][0],
    excerpt: "",
    body: "",
    published_at: tashkentToday(),
    cover: "",
  };
}

export function slugifyTitle(title: string): string {
  return title
    .toLowerCase()
    .replace(/[ʻʼ‘’'`]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 80);
}

export function hasCustomSlug(title: string, slug: string): boolean {
  const base = slug.replace(/-\d+$/, "");
  const derived = slugifyTitle(title);

  return !(slug === derived || base === derived || ((slug === "qoralama" || base === "qoralama") && derived === ""));
}

export function countWords(text: string): number {
  return (text.match(/\S+/gu) ?? []).length;
}

export function readMinutes(words: number): number {
  return Math.max(1, Math.round(words / 200));
}

export function coverSrc(cover: string): string | null {
  return storageUrl(cover);
}

export function autoExcerpt(body: string): string {
  const text = body
    .replace(/```[\s\S]*?```/g, " ")
    .replace(/!\[[^\]]*\]\([^)]*\)/g, " ")
    .replace(/\[([^\]]+)\]\([^)]*\)/g, "$1")
    .replace(/[#>*`_]/g, "")
    .replace(/\s+/g, " ")
    .trim();

  if (text.length <= 160) return text;

  return `${text.slice(0, 157).replace(/\s+\S*$/, "")}…`;
}

export function changedFields(a: PostFields, b: PostFields): boolean {
  return (Object.keys(a) as (keyof PostFields)[]).some((key) => a[key] !== b[key]);
}
