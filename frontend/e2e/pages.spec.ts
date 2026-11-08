import { expect, test } from "@playwright/test";
import { ownClient } from "./client";

const MONTH_NAMES = ["yanvar", "fevral", "mart", "aprel", "may", "iyun", "iyul", "avgust", "sentabr", "oktabr", "noyabr", "dekabr"];

test.beforeEach(async ({ page }) => {
  await ownClient(page);
});

const PAGES = ["/", "/fikrlar", "/dasturlash", "/loyihalar", "/faollik", "/men"];

for (const path of PAGES) {
  test(`${path} xatosiz, gorizontal scrollsiz ochiladi`, async ({ page }) => {
    const errors: string[] = [];
    page.on("pageerror", (error) => errors.push(error.message));
    page.on("console", (message) => {
      if (message.type() === "error" && !message.location().url.includes("_rsc=")) errors.push(message.text());
    });

    const response = await page.goto(path);
    expect(response?.status()).toBe(200);
    await page.waitForLoadState("networkidle");

    expect(errors).toEqual([]);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(overflow).toBeLessThanOrEqual(0);
  });
}

test("bosh sahifa: bento kartalari mock ma'lumot bilan", async ({ page }) => {
  await page.goto("/");

  await expect(page.getByRole("heading", { level: 1 })).toContainText("Backend dasturchi");
  await expect(page.locator(".b-post")).toContainText("Kichik odatlar katta natija beradi");
  await expect(page.locator(".b-post")).toContainText("yana 1 ta fikr");
  await expect(page.locator(".b-post")).toHaveAttribute("href", "/post/kichik-odatlar");
  await expect(page.locator(".b-loc")).toContainText("Toshkent");
  await expect(page.locator(".b-loc .tm")).toHaveText(/^\d{2}:\d{2}$/);
  await expect(page.locator(".b-gh .gn span").first()).toHaveText(/^\d+$/);
  const miniCells = await page.locator(".b-gh .mhm i").count();
  expect(miniCells % 7).toBe(0);
  expect(miniCells).toBeGreaterThanOrEqual(63);
  expect(miniCells).toBeLessThanOrEqual(70);
  await expect(page.locator(".b-gh .mhm i[data-l]")).toHaveCount(60);
  await expect(page.locator(".b-gh small")).toContainText("soʻnggi 2 oy");
  await expect(page.locator(".b-gh")).toHaveAttribute("href", "/faollik");
  await expect(page.locator(".b-tch .tv")).toHaveText("12–18");
  await expect(page.locator(".b-term")).toContainText("gh run list --limit 4");
  await expect(page.locator(".b-term .tc > div")).toHaveCount(6);
  await expect(page.locator(".b-proj")).toContainText("Shaxsiy blog");
  await expect(page.locator(".b-stk .tags span")).toHaveCount(11);
  await expect(page.locator(".b-rec .rl a")).toHaveCount(2);
  await expect(page.locator(".b-tg")).toContainText("Ingliz tili — kundalik");
  await expect(page.locator(".b-tg a.tg-btn")).toHaveAttribute("href", "https://t.me/ingliz_kanal");
});

test("bosh sahifa: o'rganyapman kartasi aylanadi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");

  await expect(page.locator(".b-lrn .lp.on .lt2")).toHaveText("Laravel events");
  await expect(page.locator(".b-lrn .lp.on .lt2")).toHaveText("PostgreSQL", { timeout: 8000 });
  await expect(page.locator(".b-lrn .ldots i.on")).toHaveCount(1);
  expect(await page.locator(".b-lrn .lp.on .ring").evaluate((el) => getComputedStyle(el).boxShadow)).toBe("none");
});

test("fikrlar: Telegram kanal postlari va qidiruv", async ({ page }) => {
  await page.goto("/fikrlar");
  await page.waitForLoadState("networkidle");

  await expect(page.getByRole("heading", { level: 1 })).toContainText("2 ta yozuv");
  await expect(page.locator(".pgrid .pc")).toHaveCount(2);
  await expect(page.locator(".pgrid .pc").first()).toHaveClass(/big/);
  await expect(page.locator(".pgrid .pc").first()).toHaveAttribute("href", "https://t.me/ingliz_kanal/12");
  await expect(page.locator(".pgrid .pc").first()).toContainText("Telegram");

  const input = page.getByRole("searchbox");
  await input.fill("resilient");
  await expect(page.locator(".pgrid .pc")).toHaveCount(1);
  await expect(page.locator(".pgrid .pc")).toContainText("chidamli");

  await input.fill("yoqqiydirsoz");
  await expect(page.getByText("Hech narsa topilmadi. Boshqa soʻz bilan qidirib koʻring.")).toBeVisible();

  await page.getByRole("button", { name: "Qidiruvni tozalash" }).click();
  await expect(input).toHaveValue("");
  await expect(page.locator(".pgrid .pc")).toHaveCount(2);
});

test("dasturlash: bo'limlar o'zgaradi va faol bo'lim belgilanadi", async ({ page }) => {
  await page.goto("/dasturlash");
  await page.waitForLoadState("networkidle");

  const seg = page.getByRole("group", { name: "Dasturlash boʻlimi" });
  await expect(seg.getByRole("link", { name: "Yozuvlar" })).toHaveAttribute("aria-current", "page");
  await expect(page.locator(".pgrid .pc")).toHaveCount(1);
  await expect(page.locator(".pgrid .pc.dev")).toContainText("Laravel events");
  await expect(page.locator(".tn.on")).toContainText("Dasturlash");

  await seg.getByRole("link", { name: "Loyihalar" }).click();
  await expect(page).toHaveURL(/\/loyihalar$/);
  await expect(page.locator(".apps-grid .app")).toHaveCount(1);
  await expect(page.locator(".tn.on")).toContainText("Dasturlash");

  await page.getByRole("group", { name: "Dasturlash boʻlimi" }).getByRole("link", { name: "Faollik" }).click();
  await expect(page).toHaveURL(/\/faollik$/);
});

test("/yozuvlar ham dasturlash yozuvlarini ko'rsatadi", async ({ page }) => {
  await page.goto("/yozuvlar");
  await expect(page.locator(".pgrid .pc.dev")).toHaveCount(1);
});

test("loyihalar: karta, havolalar va stack", async ({ page }) => {
  await page.goto("/loyihalar");
  await page.waitForLoadState("networkidle");

  const app = page.locator(".apps-grid .app");
  await expect(app).toContainText("Shaxsiy blog");
  await expect(app.locator(".app-m")).toHaveText("SB");
  await expect(app.getByRole("link", { name: "Shaxsiy blog — kod" })).toHaveAttribute("href", "https://github.com/utkirbek/blog-tulqinov");
  await expect(app.locator(".a-meta .ci")).toBeVisible();
  await expect(app.locator(".a-meta .lang")).toHaveText("PHP");
  await expect(app.locator(".app-s")).toHaveText("Laravel · Next.js · MySQL");

  await expect(page.locator(".stk-grid .c")).toHaveCount(4);
  await expect(page.locator(".stk-grid .tags span.on")).toContainText(["Laravel", "Docker"]);
});

test("faollik: yig'indi, issiqlik xaritasi, tooltip va Actions", async ({ page }) => {
  await page.goto("/faollik");
  await page.waitForLoadState("networkidle");

  await expect(page.getByText("Namuna maʼlumot")).toBeVisible();
  await expect(page.locator(".sum > div")).toHaveCount(3);
  await expect(page.locator(".sum > div").first().locator("b")).toHaveText(/^\d+$/);
  const total = Number(await page.locator(".sum > div b").first().textContent());
  const startDate = new Date(new Date().getFullYear(), new Date().getMonth() - 4, 1).toLocaleDateString("sv-SE");
  const summed = await page.locator(".hm-grid i[data-d]:not([data-f])").evaluateAll(
    (nodes, from) => nodes.filter((node) => (node as HTMLElement).dataset.d! >= from).reduce((sum, node) => sum + Number((node as HTMLElement).dataset.c), 0),
    startDate,
  );
  expect(summed).toBe(total);

  const monthsLabel = await page.locator(".hm-top span").textContent();
  expect(monthsLabel).toMatch(/^[a-zʻ]+ – [a-zʻ]+$/);
  expect(monthsLabel!.split(" – ")[0]).toBe(MONTH_NAMES[(new Date().getMonth() + 12 - 4) % 12]);
  expect(monthsLabel!.split(" – ")[1]).toBe(MONTH_NAMES[(new Date().getMonth() + 1) % 12]);

  const cells = await page.locator(".hm-grid i[data-d]").count();
  expect(cells).toBeGreaterThanOrEqual(150);
  expect(cells).toBeLessThanOrEqual(215);
  expect((await page.locator(".hm-grid i").count()) % 7).toBe(0);
  await expect(page.locator(".hm-grid i.x")).toHaveCount(0);
  await expect(page.locator(".hm-grid i")).toHaveCount(cells);

  const future = page.locator(".hm-grid i[data-f]");
  expect(await future.count()).toBeGreaterThan(20);
  expect(await future.first().getAttribute("data-c")).toBe("0");
  await expect(page.locator(".hm-grid i[data-d]:not([data-f])").last()).toHaveAttribute("data-d", new Date().toLocaleDateString("sv-SE", { timeZone: "Asia/Tashkent" }));

  const weeks = (await page.locator(".hm-grid i").count()) / 7;
  expect(weeks).toBeGreaterThanOrEqual(22);
  expect(weeks).toBeLessThanOrEqual(30);
  expect(await page.locator(".hm-grid").evaluate((el) => getComputedStyle(el).gridTemplateColumns.split(" ").length)).toBe(weeks);

  const cardWidth = (await page.locator(".hmcard").boundingBox())!.width;
  const gridWidth = (await page.locator(".hm-grid").boundingBox())!.width;
  expect(gridWidth).toBeGreaterThan(cardWidth * 0.7);
  const cell = (await page.locator(".hm-grid i[data-d]").first().boundingBox())!;
  expect(cell.height).toBeLessThanOrEqual(36);
  await expect(page.locator(".hm-months span").first()).toBeAttached();

  await expect(page.getByRole("button", { name: /oyni ochish/ })).toHaveCount(0);
  expect(await page.locator(".hm-grid i[data-d]:not([data-f])").count()).toBeGreaterThan(100);

  const active = page.locator(".hm-grid i[data-d]:not([data-c='0'])").first();
  await active.scrollIntoViewIfNeeded();
  await active.hover();
  await expect(page.locator(".tip")).toContainText("ta hissa");

  await expect(page.locator(".an .bars").first().locator("li")).toHaveCount(12);
  await expect(page.locator(".an .bars").nth(1).locator("li")).toHaveCount(7);
  await expect(page.locator(".an-s > div")).toHaveCount(3);
  await expect(page.locator(".an-s > div").first().locator("b")).toHaveText(/^\d+$/);

  await expect(page.locator(".group .list li")).toHaveCount(8);
  await expect(page.locator(".rstat.failure")).toHaveCount(1);
  await expect(page.locator(".rstat.running")).toHaveCount(1);
});

test("faollik: URL'siz run bosilganda tushuntirish chiqadi", async ({ page }) => {
  await page.goto("/faollik");
  await page.waitForLoadState("networkidle");

  await page.locator(".group .list a.row").first().click();
  await expect(page.locator(".toast.show")).toContainText("Bu namuna");
  await expect(page.locator(".toast a")).toHaveAttribute("href", "/admin/settings");
});

test("men: profil, hozir, bio, havolalar va hisoblagichlar", async ({ page }) => {
  await page.goto("/men");
  await page.waitForLoadState("networkidle");

  await expect(page.getByRole("heading", { level: 1 })).toHaveText("Utkirbek");
  await expect(page.locator(".status")).toContainText("Ishga ochiq");
  await expect(page.locator(".nl > div")).toHaveCount(4);
  await expect(page.locator(".m-bio p")).toHaveCount(1);
  await expect(page.locator(".mlk").nth(0)).toContainText("1");
  await expect(page.locator(".mlk").nth(1)).toContainText("2");
  await expect(page.getByRole("link", { name: "Kanal" })).toHaveAttribute("href", "https://t.me/ingliz_kanal");
  await expect(page.locator(".adm-l").getByRole("link", { name: "Admin panel" })).toHaveAttribute("href", "/admin");
});

test("men: mavzu segmenti va 'qo'shilmagan' xabari", async ({ page }) => {
  await page.goto("/men");
  await page.waitForLoadState("networkidle");

  const seg = page.getByRole("group", { name: "Mavzu" });
  await expect(seg.getByRole("button", { name: "Qorongʻi" })).toHaveAttribute("aria-pressed", "true");
  await seg.getByRole("button", { name: "Yorugʻ" }).click();
  await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
  await expect(seg.getByRole("button", { name: "Yorugʻ" })).toHaveAttribute("aria-pressed", "true");

  await page.getByRole("link", { name: "Email" }).click();
  await expect(page.locator(".toast.show")).toContainText("Email havolasi hali qoʻshilmagan");
});
