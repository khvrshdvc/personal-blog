import type { Page, Route } from "@playwright/test";

export const API = "http://api.test";

interface MockPost {
  id: number;
  category: "fikr" | "dev";
  tag: string;
  title: string;
  slug: string;
  status: "draft" | "published";
  excerpt: string | null;
  cover: string | null;
  body: string;
  published_at: string;
  updated_at: string;
  pending: Record<string, string> | null;
}

interface MockProject {
  id: number;
  name: string;
  repo: string | null;
  description: string | null;
  stack: string[];
  demo_url: string | null;
}

export interface Call {
  method: string;
  path: string;
  body: unknown;
}

export interface MockState {
  loggedIn: boolean;
  rateLimited: boolean;
  expireCsrfOnce: boolean;
  forceUnauthorized: boolean;
  csrfCalls: number;
  calls: Call[];
  posts: MockPost[];
  projects: MockProject[];
  settings: Record<string, unknown>;
  nextId: number;
}

const TAGS = { fikr: ["Hayot", "Oʻsish", "Ustozlik", "Til"], dev: ["Laravel", "PHP", "SQL", "Docker"] };

function slugify(text: string): string {
  return text.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "").slice(0, 80);
}

function words(text: string): number {
  return (text.match(/\S+/g) ?? []).length;
}

export function makePost(partial: Partial<MockPost> & { id: number }): MockPost {
  return {
    category: "fikr",
    tag: "Hayot",
    title: "Sarlavha",
    slug: `post-${partial.id}`,
    status: "published",
    excerpt: null,
    cover: null,
    body: "Matn bor",
    published_at: "2026-10-01",
    updated_at: new Date(2026, 9, 1 + partial.id).toISOString(),
    pending: null,
    ...partial,
  };
}

export function initialState(): MockState {
  return {
    loggedIn: true,
    rateLimited: false,
    expireCsrfOnce: false,
    forceUnauthorized: false,
    csrfCalls: 0,
    calls: [],
    nextId: 100,
    posts: [
      makePost({ id: 1, title: "Birinchi fikr", slug: "birinchi-fikr", body: "Birinchi matn so'zlari bor", excerpt: "Qisqa tavsif" }),
      makePost({ id: 2, title: "Laravel events", slug: "laravel-events", category: "dev", tag: "Laravel", body: "Event va listener" }),
      makePost({ id: 3, title: "Tugallanmagan qoralama", slug: "tugallanmagan", status: "draft", body: "Yarim matn" }),
    ],
    projects: [
      { id: 1, name: "Shaxsiy blog", repo: "utkirbek/blog", description: "Laravel va Next.js", stack: ["Laravel", "Next.js"], demo_url: null },
      { id: 2, name: "CRM tizimi", repo: null, description: null, stack: [], demo_url: null },
    ],
    settings: {
      github: "utkirbek",
      open: true,
      bio: "Birinchi xatboshi.\n\nIkkinchi xatboshi.",
      now: [
        { k: "Oʻrganyapman", v: "Laravel" },
        { k: "Dars beraman", v: "HTML" },
        { k: "Yozyapman", v: "Blog" },
        { k: "Qiziqaman", v: "Anime" },
      ],
      learn: [
        { n: "Laravel events", p: 72 },
        { n: "PostgreSQL", p: 48 },
        { n: "Docker", p: 35 },
      ],
      telegram: { u: "kanal", n: "Kanal nomi", posts: [{ t: "Bir", d: "2026-11-01" }, { t: "Ikki", d: "2026-11-02" }] },
    },
  };
}

function adminPost(post: MockPost) {
  const view = { ...post, ...(post.pending ?? {}) };

  return { ...post, words: words(view.body as string) };
}

function brief(post: MockPost) {
  const view = { ...post, ...(post.pending ?? {}) };

  return {
    id: post.id,
    title: view.title as string,
    excerpt: ((view.excerpt as string | null) ?? (view.body as string)).slice(0, 80),
    tag: view.tag as string,
    status: post.status === "draft" ? "draft" : post.pending ? "pending" : "published",
    updated_at: post.updated_at,
  };
}

function normalize(input: Record<string, unknown>, post: MockPost) {
  const base: Record<string, unknown> = {
    category: post.pending?.category ?? post.category,
    title: post.pending?.title ?? post.title,
    slug: post.pending?.slug ?? post.slug,
    tag: post.pending?.tag ?? post.tag,
    excerpt: post.pending?.excerpt ?? post.excerpt ?? "",
    body: post.pending?.body ?? post.body,
    published_at: post.pending?.published_at ?? post.published_at,
    cover: post.pending?.cover ?? post.cover ?? "",
  };
  const merged = { ...base, ...input };
  const category = merged.category === "dev" ? "dev" : "fikr";
  const tag = TAGS[category].includes(merged.tag as string) ? (merged.tag as string) : TAGS[category][0];

  return {
    category,
    title: String(merged.title ?? "").trim(),
    slug: slugify(String(merged.slug ?? "")),
    tag,
    excerpt: String(merged.excerpt ?? "").slice(0, 200),
    body: String(merged.body ?? ""),
    published_at: String(merged.published_at || "2026-11-07"),
    cover: String(merged.cover ?? ""),
  };
}

function unique(state: MockState, slug: string, ignore: number | null): string {
  let candidate = slug;
  let n = 2;

  while (state.posts.some((post) => post.slug === candidate && post.id !== ignore)) candidate = `${slug}-${n++}`;

  return candidate;
}

function json(route: Route, status: number, data: unknown) {
  return route.fulfill({
    status,
    headers: cors(route),
    contentType: "application/json",
    body: JSON.stringify(data),
  });
}

function cors(route: Route): Record<string, string> {
  return {
    "Access-Control-Allow-Origin": route.request().headers()["origin"] ?? "http://localhost:3100",
    "Access-Control-Allow-Credentials": "true",
    "Access-Control-Allow-Headers": "Content-Type, X-XSRF-TOKEN, Accept",
    "Access-Control-Allow-Methods": "GET, POST, PATCH, PUT, DELETE, OPTIONS",
  };
}

export async function mockAdminApi(page: Page, state: MockState = initialState()): Promise<MockState> {
  await page.route(`${API}/storage/**`, (route) =>
    route.fulfill({
      status: 200,
      contentType: "image/png",
      headers: cors(route),
      body: Buffer.from("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==", "base64"),
    }),
  );

  await page.route(`${API}/**`, async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    const method = request.method();

    if (method === "OPTIONS") return route.fulfill({ status: 204, headers: cors(route) });

    if (url.pathname === "/sanctum/csrf-cookie") {
      state.csrfCalls += 1;

      return route.fulfill({ status: 204, headers: cors(route) });
    }

    const path = url.pathname.replace(/^\/api\/admin/, "") || "/";
    let body: Record<string, unknown> = {};

    try {
      body = (request.postDataJSON() as Record<string, unknown>) ?? {};
    } catch {}

    state.calls.push({ method, path, body });

    if (state.expireCsrfOnce && method !== "GET") {
      state.expireCsrfOnce = false;

      return json(route, 419, { message: "CSRF token mismatch." });
    }

    if (path === "/login") {
      if (state.rateLimited) return json(route, 429, { message: "Too Many Attempts." });
      if (body.password !== "sir-parol") return json(route, 422, { message: "Parol notoʻgʻri.", errors: { password: ["Parol notoʻgʻri."] } });

      state.loggedIn = true;

      return route.fulfill({ status: 204, headers: cors(route) });
    }

    if (!state.loggedIn || state.forceUnauthorized) return json(route, 401, { message: "Unauthenticated." });

    if (path === "/me") return json(route, 200, { name: "Utkirbek" });

    if (path === "/logout") {
      state.loggedIn = false;

      return route.fulfill({ status: 204, headers: cors(route) });
    }

    if (path === "/dashboard") {
      const published = state.posts.filter((post) => post.status === "published");
      const sorted = [...state.posts].sort((a, b) => b.updated_at.localeCompare(a.updated_at));
      const today = new Date().toISOString().slice(0, 10);

      return json(route, 200, {
        counts: {
          published: published.length,
          thoughts: published.filter((post) => post.category === "fikr").length,
          dev: published.filter((post) => post.category === "dev").length,
          drafts: state.posts.length - published.length,
          pending: published.filter((post) => post.pending).length,
          words: published.reduce((sum, post) => sum + words(post.body), 0),
          projects: state.projects.length,
        },
        last_edited: sorted[0] ? brief(sorted[0]) : null,
        resume: sorted.slice(1).filter((post) => post.status === "draft" || post.pending).map(brief),
        activity: {
          days: Array.from({ length: 182 }, (_, index) => ({ d: today, c: index % 9 === 0 ? 2 : 0 })),
          active_days: 21,
          this_month: 3,
          days_since_last: 4,
        },
        tags: [{ tag: "Hayot", count: 2 }, { tag: "Laravel", count: 1 }],
        checks: [
          { key: "github", ok: true, value: "utkirbek" },
          { key: "telegram", ok: true, value: "kanal" },
          { key: "bio", ok: true, value: "3" },
          { key: "learn", ok: true, value: "3" },
          { key: "open", ok: true, value: "true" },
        ],
        projects: state.projects.slice(0, 4).map((project) => ({ id: project.id, name: project.name, repo: project.repo })),
      });
    }

    if (path === "/preview") {
      const html = String(body.body ?? "")
        .split(/\n\n/)
        .map((chunk) => `<p>${chunk.replace(/&/g, "&amp;").replace(/</g, "&lt;")}</p>`)
        .join("");

      return json(route, 200, { html });
    }

    if (path === "/uploads") return json(route, 201, { url: `${API}/storage/covers/test.png`, path: "covers/test.png" });

    const postMatch = /^\/posts\/(\d+)(?:\/(publish|apply|pending|unpublish))?$/.exec(path);

    if (path === "/posts" && method === "GET") {
      return json(route, 200, [...state.posts].sort((a, b) => b.updated_at.localeCompare(a.updated_at)).map(adminPost));
    }

    if (path === "/posts" && method === "POST") {
      const category = body.category === "dev" ? "dev" : "fikr";
      const title = String(body.title ?? "");
      const post = makePost({
        id: state.nextId++,
        category,
        tag: TAGS[category][0],
        title,
        slug: unique(state, slugify(title) || "qoralama", null),
        status: "draft",
        body: String(body.body ?? ""),
        published_at: "2026-11-07",
        updated_at: new Date().toISOString(),
      });
      state.posts.push(post);

      return json(route, 201, adminPost(post));
    }

    if (postMatch) {
      const post = state.posts.find((item) => item.id === Number(postMatch[1]));

      if (!post) return json(route, 404, { message: "Not found." });

      const action = postMatch[2];
      const touch = () => (post.updated_at = new Date().toISOString());

      if (!action && method === "GET") return json(route, 200, adminPost(post));

      if (!action && method === "DELETE") {
        state.posts = state.posts.filter((item) => item.id !== post.id);

        return route.fulfill({ status: 204, headers: cors(route) });
      }

      if (!action && method === "PATCH") {
        if (body.cover && !/^(covers\/[A-Za-z0-9._-]+|https?:\/\/\S+)$/.test(String(body.cover))) {
          return json(route, 422, { message: "Muqova manzili notoʻgʻri.", errors: { cover: ["Muqova manzili notoʻgʻri."] } });
        }

        const data = normalize(body, post);

        if (post.status === "published") {
          const main = { category: post.category, title: post.title, slug: post.slug, tag: post.tag, excerpt: post.excerpt ?? "", body: post.body, published_at: post.published_at, cover: post.cover ?? "" };
          post.pending = JSON.stringify(data) === JSON.stringify(main) ? null : data;
        } else {
          post.category = data.category as "fikr" | "dev";
          post.title = data.title;
          post.slug = unique(state, data.slug || slugify(data.title) || "qoralama", post.id);
          post.tag = data.tag;
          post.excerpt = data.excerpt || null;
          post.body = data.body;
          post.published_at = data.published_at;
          post.cover = data.cover || null;
        }

        touch();

        return json(route, 200, adminPost(post));
      }

      if (action === "publish") {
        if (post.status === "published") return json(route, 409, { message: "Yozuv allaqachon chop etilgan." });
        if (!post.title) return json(route, 422, { message: "Sarlavha yozilmagan", errors: { title: ["Sarlavha yozilmagan"] } });
        if (!post.body.trim()) return json(route, 422, { message: "Matn boʻsh", errors: { body: ["Matn boʻsh"] } });

        post.status = "published";
        post.excerpt = post.excerpt || post.body.slice(0, 160);
        touch();

        return json(route, 200, adminPost(post));
      }

      if (action === "apply") {
        if (post.status !== "published" || !post.pending) return json(route, 409, { message: "Kutilayotgan oʻzgarish yoʻq." });

        const taken = state.posts.some((item) => item.slug === post.pending!.slug && item.id !== post.id);

        if (taken) return json(route, 422, { message: "Bu havola band", errors: { slug: ["Bu havola band — boshqasini tanlang"] } });

        Object.assign(post, post.pending, { excerpt: post.pending.excerpt || null, cover: post.pending.cover || null });
        post.pending = null;
        touch();

        return json(route, 200, adminPost(post));
      }

      if (action === "pending") {
        post.pending = null;
        touch();

        return json(route, 200, adminPost(post));
      }

      if (action === "unpublish") {
        if (post.pending) Object.assign(post, post.pending, { excerpt: post.pending.excerpt || null, cover: post.pending.cover || null });

        post.pending = null;
        post.status = "draft";
        touch();

        return json(route, 200, adminPost(post));
      }
    }

    if (path === "/projects" && method === "GET") return json(route, 200, state.projects);

    if (path === "/projects" && method === "POST") {
      if (!String(body.name ?? "").trim()) return json(route, 422, { message: "Loyiha nomini yozing", errors: { name: ["Loyiha nomini yozing"] } });

      const project: MockProject = {
        id: state.nextId++,
        name: String(body.name).trim(),
        repo: body.repo ? String(body.repo) : null,
        description: body.description ? String(body.description) : null,
        stack: (body.stack as string[]) ?? [],
        demo_url: body.demo_url ? String(body.demo_url) : null,
      };
      state.projects.push(project);

      return json(route, 201, project);
    }

    const projectMatch = /^\/projects\/(\d+)$/.exec(path);

    if (projectMatch) {
      const project = state.projects.find((item) => item.id === Number(projectMatch[1]));

      if (!project) return json(route, 404, { message: "Not found." });

      if (method === "DELETE") {
        state.projects = state.projects.filter((item) => item.id !== project.id);

        return route.fulfill({ status: 204, headers: cors(route) });
      }

      if (method === "PATCH") {
        if ("name" in body && !String(body.name).trim()) return json(route, 422, { message: "Loyiha nomini yozing", errors: { name: ["Loyiha nomini yozing"] } });

        Object.assign(project, {
          name: body.name ?? project.name,
          repo: body.repo ? String(body.repo) : null,
          description: body.description ? String(body.description) : null,
          stack: (body.stack as string[]) ?? project.stack,
          demo_url: body.demo_url ? String(body.demo_url) : null,
        });

        return json(route, 200, project);
      }
    }

    if (path === "/settings" && method === "GET") return json(route, 200, state.settings);

    if (path === "/settings" && method === "PUT") {
      const learn = (body.learn as { p: number }[]) ?? [];

      if (learn.some((item) => item.p > 100)) {
        return json(route, 422, { message: "Foiz 100 dan oshmasin.", errors: { "learn.0.p": ["Foiz 100 dan oshmasin."] } });
      }

      state.settings = body;

      return json(route, 200, body);
    }

    return json(route, 404, { message: `Mock: ${method} ${path} yoʻq` });
  });

  return state;
}
