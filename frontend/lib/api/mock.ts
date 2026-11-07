import type { Activity, Api, PostDetail, PostListItem, Project, RepoInfo, Run, RunStatus, Settings, Telegram, TelegramFeed } from "./types";

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

const RICH: Record<string, { html: string; toc: PostDetail["toc"] }> = {
  "laravel-events": {
    html: '<p>Event va <strong>listener</strong> kodni ajratadi.</p><h2 id="nima-uchun">Nima uchun kerak</h2><p>Bitta amal bir nechta natija bersa.</p><pre><code class="language-php">&lt;?php\nevent(new PostCreated($post));\n</code></pre><h3 id="listener">Listener</h3><blockquote>Ortiqcha murakkablikdan qoching.</blockquote><ul><li>Oddiy</li><li>Tushunarli</li></ul>',
    toc: [
      { id: "nima-uchun", text: "Nima uchun kerak", level: 2 },
      { id: "listener", text: "Listener", level: 3 },
    ],
  },
};

const projects: Project[] = [
  {
    id: 1,
    name: "Shaxsiy blog",
    repo: "utkirbek/blog-tulqinov",
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
  telegram: { u: "ingliz_kanal", n: "Ingliz tili — kundalik", posts: [] },
};

function mulberry(seed: number) {
  let a = seed;

  return () => {
    a = (a + 0x6d2b79f5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;

    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

function isoDay(date: Date) {
  return date.toISOString().slice(0, 10);
}

function buildActivity(): Activity {
  const random = mulberry(20261105);
  const [year, month, day] = new Date().toLocaleDateString("sv-SE", { timeZone: "Asia/Tashkent" }).split("-").map(Number);
  const today = new Date(Date.UTC(year, month - 1, day));
  const days: Activity["days"] = [];
  let total = 0;

  for (let i = 364; i >= 0; i--) {
    const date = new Date(today.getTime() - i * 86400000);
    const growth = Math.max(0.08, 1 - i / 400);
    const weekday = (date.getUTCDay() + 6) % 7 >= 5 ? 0.5 : 1;
    const count =
      random() < 0.1 + growth * 0.62 * weekday
        ? 1 + Math.floor(random() * random() * 11 * growth * weekday + random() * 2)
        : 0;

    total += count;
    days.push({ d: isoDay(date), c: count });
  }

  return { days, total, real: false };
}

const RUNS: [string, string, string, string, number, RunStatus, number | null, number][] = [
  ["erp-brainup", "Tests", "feat: kurslar uchun CRUD va validatsiya", "main", 48, "running", null, 0.01],
  ["blog-tulqinov", "Deploy", "feat: fikrlar va dasturlash boʻlimlari", "main", 31, "success", 96, 0.3],
  ["blog-tulqinov", "Tests", "feat: fikrlar va dasturlash boʻlimlari", "main", 57, "success", 74, 0.31],
  ["crm-system", "Tests", "fix: bitim summasini hisoblash", "fix/deal-total", 112, "failure", 131, 1.2],
  ["crm-system", "Laravel Pint", "fix: bitim summasini hisoblash", "fix/deal-total", 89, "success", 21, 1.21],
  ["erp-brainup", "Tests", "refactor: StudentService ajratildi", "main", 47, "success", 83, 2.1],
  ["telegram-task-bot", "Lint", "feat: kunlik vazifalar jadvali", "main", 12, "success", 18, 4],
  ["symfony-blog", "CI", "chore: bogʻliqliklarni yangilash", "main", 9, "skipped", null, 30],
];

function buildRuns(): Run[] {
  const now = Date.now();

  return RUNS.map(([repo, workflow, title, branch, num, status, dur, ageDays]) => ({
    status,
    workflow,
    title,
    repo,
    branch,
    num,
    url: null,
    at: new Date(now - ageDays * 86400000).toISOString(),
    dur,
  }));
}

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

    const rich = RICH[slug];
    const detail: PostDetail = {
      ...posts[index],
      body_html: rich?.html ?? `<p>${posts[index].excerpt ?? ""}</p><p>Bu mock matn.</p>`,
      toc: rich?.toc ?? [],
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
      .map((post) => ({ type: "post" as const, category: post.category, title: post.title, subtitle: post.tag ?? "", path: `/post/${post.slug}` }));
  },
  async getProjects() {
    return projects;
  },
  async getSettings() {
    return settings;
  },
  async getActivity() {
    return buildActivity();
  },
  async getRuns() {
    return buildRuns();
  },
  async getRepoInfo(projectId) {
    const project = projects.find((item) => item.id === projectId);
    const name = project?.repo?.split("/").pop();
    const run = buildRuns().find((item) => item.repo === name);

    if (!project || !run) {
      return { lang: project ? "PHP" : null, stars: project ? 0 : null, pushed_at: null, run: null } satisfies RepoInfo;
    }

    return { lang: "PHP", stars: 1, pushed_at: run.at, run: { status: run.status, at: run.at } } satisfies RepoInfo;
  },
  async getTelegram() {
    return {
      u: "ingliz_kanal",
      n: "Ingliz tili — kundalik",
      posts: [
        { t: "Bugungi soʻz: resilient — chidamli.", d: "2026-11-03" },
        { t: "Texnik hujjatni oʻqishning 3 qoidasi.", d: "2026-11-05" },
      ],
    } satisfies Telegram;
  },
  async getTelegramFeed() {
    return {
      u: "ingliz_kanal",
      n: "Ingliz tili — kundalik",
      posts: [
        { id: 12, t: "Texnik hujjatni oʻqishning 3 qoidasi.\nAvval mundarija, keyin misollar, oxirida tafsilotlar.", d: "2026-11-05", url: "https://t.me/ingliz_kanal/12" },
        { id: 11, t: "Bugungi soʻz: resilient — chidamli.", d: "2026-11-03", url: "https://t.me/ingliz_kanal/11" },
      ],
    } satisfies TelegramFeed;
  },
  async getCounts() {
    return {
      fikr: posts.filter((post) => post.category === "fikr").length,
      dev: posts.filter((post) => post.category === "dev").length,
    };
  },
};
