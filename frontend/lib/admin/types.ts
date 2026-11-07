import type { Category, Project, Settings } from "@/lib/api/types";

export type { Project, Settings };

export type PostStatus = "draft" | "published";
export type PostState = "draft" | "published" | "pending";

export interface PostFields {
  category: Category;
  title: string;
  slug: string;
  tag: string;
  excerpt: string;
  body: string;
  published_at: string;
  cover: string;
}

export interface AdminPost {
  id: number;
  category: Category;
  tag: string | null;
  title: string;
  slug: string;
  status: PostStatus;
  excerpt: string | null;
  cover: string | null;
  body: string;
  published_at: string | null;
  updated_at: string | null;
  pending: PostFields | null;
  words: number;
}

export interface PostBrief {
  id: number;
  title: string;
  excerpt: string;
  tag: string;
  status: PostState;
  updated_at: string | null;
}

export interface Dashboard {
  counts: { published: number; thoughts: number; dev: number; drafts: number; pending: number; words: number; projects: number };
  last_edited: PostBrief | null;
  resume: PostBrief[];
  activity: { days: { d: string; c: number }[]; active_days: number; this_month: number; days_since_last: number | null };
  tags: { tag: string; count: number }[];
  checks: { key: "github" | "telegram" | "bio" | "learn" | "open"; ok: boolean; value: string }[];
  projects: { id: number; name: string; repo: string | null }[];
}

export interface ProjectInput {
  name?: string;
  repo?: string;
  description?: string;
  stack?: string[];
  demo_url?: string;
}
