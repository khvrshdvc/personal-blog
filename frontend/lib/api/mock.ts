import type { Api, PostDetail, PostListItem, Project, Settings } from "./types";

const posts: PostListItem[] = [
  {
    id: 3,
    category: "dev",
    tag: "Laravel",
    title: "Laravel events: kodni ajratishning oddiy yoʻli",
    slug: "laravel-events",
    excerpt: "Event va listener nima uchun kerakligi va qachon ortiqcha ekani haqida.",
    cover: null,
    published_at: "2026-10-28",
    read_minutes: 6,
  },
  {
    id: 2,
    category: "fikr",
    tag: "Oʻsish",
    title: "Kichik odatlar katta natija beradi",
    slug: "kichik-odatlar",
    excerpt: "Har kuni 20 daqiqa: nega ertalabki mashq emas, izchillik muhim.",
    cover: null,
    published_at: "2026-10-12",
    read_minutes: 4,
  },
  {
    id: 1,
    category: "fikr",
    tag: "Hayot",
    title: "Nega fikrlarimni yozishga qaror qildim",
    slug: "nega-fikrlarimni-yozaman",
    excerpt: "Bu blog faqat kod haqida emas.",
    cover: null,
    published_at: "2026-09-30",
    read_minutes: 3,
  },
];

const projects: Project[] = [
  {
    id: 1,
    name: "Shaxsiy blog",
    repo: "utkirbek/personal-blog",
    description: "Laravel API va Next.js frontend.",
    stack: ["Laravel", "Next.js", "MySQL"],
    demo_url: null,
  },
];

const settings: Settings = {
  github: "",
  open: true,
  bio: "Dasturlashni mustaqil oʻrganyapman — asosan PHP, Laravel va maʼlumotlar bazalari.",
  now: [
    { k: "Oʻrganyapman", v: "Laravel events va PostgreSQL" },
    { k: "Dars beraman", v: "12–18 yoshli bolalarga HTML, Python va ingliz tili" },
    { k: "Yozyapman", v: "Shu blog va Telegram kanal" },
    { k: "Qiziqaman", v: "Kiberxavfsizlik, Flutter, anime" },
  ],
  learn: [
    { n: "Laravel events", p: 72 },
    { n: "PostgreSQL", p: 48 },
    { n: "Docker", p: 35 },
  ],
  telegram: { u: "", n: "", posts: [] },
};

export const mockApi: Api = {
  async getPosts(query = {}) {
    const filtered = posts.filter(
      (post) =>
        (!query.category || post.category === query.category) &&
        (!query.tag || post.tag === query.tag) &&
        (!query.q || post.title.toLowerCase().includes(query.q.toLowerCase())),
    );
    const perPage = query.per_page ?? 12;
    const page = query.page ?? 1;

    return {
      data: filtered.slice((page - 1) * perPage, page * perPage),
      meta: {
        current_page: page,
        last_page: Math.max(1, Math.ceil(filtered.length / perPage)),
        per_page: perPage,
        total: filtered.length,
      },
    };
  },
  async getPost(slug) {
    const index = posts.findIndex((post) => post.slug === slug);

    if (index === -1) {
      return null;
    }

    const detail: PostDetail = {
      ...posts[index],
      body_html: `<p>${posts[index].excerpt ?? ""}</p>`,
      toc: [],
      prev: posts[index + 1] ?? null,
      next: posts[index - 1] ?? null,
    };

    return detail;
  },
  async getTags() {
    const counts = new Map<string, number>();

    for (const post of posts) {
      if (post.tag) {
        counts.set(post.tag, (counts.get(post.tag) ?? 0) + 1);
      }
    }

    return [...counts.entries()].map(([tag, count]) => ({ tag, count }));
  },
  async search(q) {
    const needle = q.trim().toLowerCase();

    return posts
      .filter((post) => needle === "" || post.title.toLowerCase().includes(needle))
      .slice(0, 8)
      .map((post) => ({ type: "post" as const, title: post.title, subtitle: post.tag ?? "", path: `/post/${post.slug}` }));
  },
  async getProjects() {
    return projects;
  },
  async getSettings() {
    return settings;
  },
};
