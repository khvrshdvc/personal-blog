import type { Api, PostDetail, PostsQuery } from "./types";

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
  ) {
    super(message);
  }
}

function baseUrl(): string {
  if (typeof window === "undefined") {
    return process.env.API_URL ?? "http://localhost";
  }

  return process.env.NEXT_PUBLIC_API_URL ?? "http://localhost";
}

function toSearch(params: Record<string, string | number | undefined>): string {
  const search = new URLSearchParams();

  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== "") {
      search.set(key, String(value));
    }
  }

  const text = search.toString();

  return text ? `?${text}` : "";
}

async function request<T>(path: string, allow404 = false): Promise<T | null> {
  const response = await fetch(`${baseUrl()}/api${path}`, {
    headers: { Accept: "application/json" },
  });

  if (allow404 && response.status === 404) {
    return null;
  }

  if (!response.ok) {
    throw new ApiError(response.status, `API ${response.status}: ${path}`);
  }

  return (await response.json()) as T;
}

export const httpApi: Api = {
  async getPosts(query: PostsQuery = {}) {
    return (await request(`/posts${toSearch({ ...query })}`))!;
  },
  async getPost(slug) {
    return request<PostDetail>(`/posts/${encodeURIComponent(slug)}`, true);
  },
  async getTags(category) {
    return (await request(`/tags${toSearch({ category })}`))!;
  },
  async search(q) {
    return (await request(`/search${toSearch({ q })}`))!;
  },
  async getProjects() {
    return (await request("/projects"))!;
  },
  async getSettings() {
    return (await request("/settings"))!;
  },
};
