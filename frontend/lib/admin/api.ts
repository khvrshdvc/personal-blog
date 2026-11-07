import type { AdminPost, Dashboard, PostFields, Project, ProjectInput, Settings } from "@/lib/admin/types";

const BASE = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost";

export class AdminApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors: Record<string, string[]> = {},
  ) {
    super(message);
  }

  first(field?: string): string {
    const list = field ? this.errors[field] : Object.values(this.errors)[0];

    return list?.[0] ?? this.message;
  }
}

let unauthorizedHandler: (() => void) | null = null;
let csrfReady = false;

export function onUnauthorized(handler: (() => void) | null) {
  unauthorizedHandler = handler;
}

export function apiBase(): string {
  return BASE;
}

export function storageUrl(cover: string | null | undefined): string | null {
  if (!cover) return null;

  return /^(https?:\/\/|\/(?!\/))/i.test(cover) ? cover : `${BASE}/storage/${cover}`;
}

function xsrfToken(): string {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

  return match ? decodeURIComponent(match[1]) : "";
}

async function ensureCsrf(force = false) {
  if (csrfReady && !force) return;

  await fetch(`${BASE}/sanctum/csrf-cookie`, { credentials: "include" });
  csrfReady = true;
}

interface Options {
  method?: string;
  json?: unknown;
  form?: FormData;
  signal?: AbortSignal;
}

async function request<T>(path: string, options: Options = {}, retried = false): Promise<T> {
  const method = options.method ?? "GET";

  if (method !== "GET") await ensureCsrf();

  const headers: Record<string, string> = { Accept: "application/json" };

  if (method !== "GET") headers["X-XSRF-TOKEN"] = xsrfToken();
  if (options.json !== undefined) headers["Content-Type"] = "application/json";

  const response = await fetch(`${BASE}/api/admin${path}`, {
    method,
    headers,
    credentials: "include",
    body: options.form ?? (options.json !== undefined ? JSON.stringify(options.json) : undefined),
    signal: options.signal,
  });

  if (response.status === 419 && !retried) {
    await ensureCsrf(true);

    return request<T>(path, options, true);
  }

  if (response.status === 401 && path !== "/login" && path !== "/me") {
    unauthorizedHandler?.();
  }

  if (!response.ok) {
    let message = "Xatolik yuz berdi";
    let errors: Record<string, string[]> = {};

    try {
      const data = (await response.json()) as { message?: string; errors?: Record<string, string[]> };
      message = data.message ?? message;
      errors = data.errors ?? {};
    } catch {}

    throw new AdminApiError(response.status, message, errors);
  }

  if (response.status === 204) return undefined as T;

  return (await response.json()) as T;
}

export const adminApi = {
  me: () => request<{ name: string }>("/me"),
  login: (password: string) => request<void>("/login", { method: "POST", json: { password } }),
  logout: () => request<void>("/logout", { method: "POST" }),
  dashboard: () => request<Dashboard>("/dashboard"),
  posts: () => request<AdminPost[]>("/posts"),
  post: (id: number) => request<AdminPost>(`/posts/${id}`),
  createPost: (input: { category: string; title?: string; body?: string }) =>
    request<AdminPost>("/posts", { method: "POST", json: input }),
  savePost: (id: number, fields: Partial<PostFields>, signal?: AbortSignal) =>
    request<AdminPost>(`/posts/${id}`, { method: "PATCH", json: fields, signal }),
  publishPost: (id: number) => request<AdminPost>(`/posts/${id}/publish`, { method: "POST" }),
  applyPost: (id: number) => request<AdminPost>(`/posts/${id}/apply`, { method: "POST" }),
  discardPending: (id: number) => request<AdminPost>(`/posts/${id}/pending`, { method: "DELETE" }),
  unpublishPost: (id: number) => request<AdminPost>(`/posts/${id}/unpublish`, { method: "POST" }),
  deletePost: (id: number) => request<void>(`/posts/${id}`, { method: "DELETE" }),
  preview: (body: string, signal?: AbortSignal) => request<{ html: string }>("/preview", { method: "POST", json: { body }, signal }),
  upload: (file: File) => {
    const form = new FormData();
    form.append("file", file);

    return request<{ url: string; path: string }>("/uploads", { method: "POST", form });
  },
  projects: () => request<Project[]>("/projects"),
  syncProjects: () => request<{ created: number; updated: number; total: number }>("/projects/sync", { method: "POST" }),
  createProject: (input: ProjectInput) => request<Project>("/projects", { method: "POST", json: input }),
  saveProject: (id: number, input: ProjectInput) => request<Project>(`/projects/${id}`, { method: "PATCH", json: input }),
  deleteProject: (id: number) => request<void>(`/projects/${id}`, { method: "DELETE" }),
  settings: () => request<Settings>("/settings"),
  saveSettings: (settings: Settings) => request<Settings>("/settings", { method: "PUT", json: settings }),
};
