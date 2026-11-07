import { expect, test } from "@playwright/test";
import { ownClient } from "./client";

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

test("fikrlar: sarlavha, teglar va tanlash", async ({ page }) => {
  await page.goto("/fikrlar");
  await page.waitForLoadState("networkidle");

  await expect(page.getByRole("heading", { level: 1 })).toContainText("2 ta yozuv");
  await expect(page.locator(".pgrid .pc")).toHaveCount(2);
  await expect(page.locator(".pgrid .pc").first()).toHaveClass(/big/);

  const tabs = page.getByRole("group", { name: "Mavzular" });
  await expect(tabs.getByRole("button")).toHaveText(["Barchasi", "Hayot", "Oʻsish"]);
  await expect(tabs.getByRole("button", { name: "Barchasi" })).toHaveAttribute("aria-pressed", "true");

  await tabs.getByRole("button", { name: "Hayot" }).click();
  await expect(page.locator(".pgrid .pc")).toHaveCount(1);
  await expect(page.locator(".pgrid .pc")).toContainText("Nega fikrlarimni");
  await expect(page.locator(".pgrid .pc").first()).not.toHaveClass(/big/);

  await tabs.getByRole("button", { name: "Barchasi" }).click();
  await expect(page.locator(".pgrid .pc")).toHaveCount(2);
});

test("fikrlar: qidiruv va tozalash", async ({ page }) => {
  await page.goto("/fikrlar");
  await page.waitForLoadState("networkidle");

  const input = page.getByRole("searchbox");
  await input.fill("kichik");
  await expect(page.locator(".pgrid .pc")).toHaveCount(1);
  await expect(page.locator(".pgrid .pc")).toContainText("Kichik odatlar");

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
  await expect(page.locator(".hm-grid i[data-d]").first()).toBeAttached();
  await expect(page.locator(".hm-grid i[data-d]")).toHaveCount(60);
  const total60 = Number(await page.locator(".sum > div b").first().textContent());
  expect((await page.locator(".hm-grid i").count()) % 7).toBe(0);
  await expect(page.locator(".hm-top")).toContainText("soʻnggi 2 oy");
  const weeks = (await page.locator(".hm-grid i").count()) / 7;
  expect(weeks).toBeLessThanOrEqual(10);
  expect(await page.locator(".hm-grid").evaluate((el) => getComputedStyle(el).gridTemplateColumns.split(" ").length)).toBe(weeks);
  await expect(page.locator(".hm-months span").first()).toBeAttached();

  const cardWidth = (await page.locator(".hmcard").boundingBox())!.width;
  const gridWidth = (await page.locator(".hm-grid").boundingBox())!.width;
  expect(gridWidth).toBeGreaterThan(cardWidth * 0.7);
  const cell = (await page.locator(".hm-grid i[data-d]").first().boundingBox())!;
  expect(cell.height).toBeLessThanOrEqual(40);

  await page.getByRole("button", { name: "Yana 1 oy ochish" }).click();
  await expect(page.locator(".hm-grid i[data-d]")).toHaveCount(90);
  await expect(page.locator(".hm-top")).toContainText("soʻnggi 3 oy");
  expect(Number(await page.locator(".sum > div b").first().textContent())).toBeGreaterThanOrEqual(total60);

  for (let i = 0; i < 20; i++) {
    const button = page.getByRole("button", { name: "Yana 1 oy ochish" });

    if ((await button.count()) === 0) break;
    await button.click();
  }

  await expect(page.getByRole("button", { name: "Yana 1 oy ochish" })).toHaveCount(0);
  await expect(page.locator(".hm-grid i[data-d]")).toHaveCount(365);
  await expect(page.locator(".hm-top")).toContainText("soʻnggi yil");
  expect((await page.locator(".hm-grid i").count()) % 7).toBe(0);

  const active = page.locator(".hm-grid i[data-d]:not([data-c='0'])").first();
  await active.scrollIntoViewIfNeeded();
  await active.hover();
  await expect(page.locator(".tip")).toContainText("ta hissa");

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
