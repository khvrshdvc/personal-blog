import type { Activity, Api, PostDetail, PostListItem, Paginated, PostsQuery, RepoInfo, Run, Telegram } from "./types";

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
  const headers: Record<string, string> = { Accept: "application/json" };

  if (typeof window === "undefined" && process.env.REVALIDATE_SECRET) {
    headers["X-Frontend-Secret"] = process.env.REVALIDATE_SECRET;
  }

  const response = await fetch(`${baseUrl()}/api${path}`, { headers });

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
  async getCounts() {
    const [fikr, dev] = await Promise.all(
      (["fikr", "dev"] as const).map(async (category) => {
        const page = (await request<Paginated<PostListItem>>(`/posts${toSearch({ category, per_page: 1 })}`))!;

        return page.meta.total;
      }),
    );

    return { fikr, dev };
  },
  async getActivity() {
    return request<Activity>("/activity", true);
  },
  async getRuns() {
    return (await request<Run[]>("/runs", true)) ?? [];
  },
  async getRepoInfo(projectId) {
    return request<RepoInfo>(`/projects/${projectId}/github`, true);
  },
  async getTelegram() {
    return request<Telegram>("/telegram", true);
  },
};
