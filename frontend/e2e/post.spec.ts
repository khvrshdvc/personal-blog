import { expect, test } from "@playwright/test";

test("dasturlash posti: sarlavha, mundarija, kod bloki va tuzilma", async ({ page }) => {
  await page.goto("/post/laravel-events");
  await page.waitForLoadState("networkidle");

  await expect(page.getByRole("heading", { level: 1 })).toContainText("Laravel events");
  await expect(page.locator(".read")).toHaveClass(/dev/);
  await expect(page.locator(".r-meta")).toContainText("Laravel");
  await expect(page.locator(".r-meta")).toContainText("6 daqiqa oʻqish");
  await expect(page.locator(".tlink")).toHaveAttribute("href", "/dasturlash");

  const toc = page.locator("details.disc");
  await expect(toc.locator("summary")).toContainText("2 boʻlim");
  await toc.locator("summary").click();
  await expect(toc.locator("a")).toHaveCount(2);
  await expect(toc.locator("a.lv3")).toHaveText("Listener");
  await expect(toc.locator("a").first()).toHaveAttribute("href", "#nima-uchun");

  await expect(page.locator(".prose h2#nima-uchun")).toBeVisible();
  await expect(page.locator(".prose blockquote")).toBeVisible();
  await expect(page.locator(".code-wrap .code-bar span").first()).toHaveText("php");
  await expect(page.locator(".code-wrap pre code")).toContainText("event(new PostCreated");
});

test("kod nusxalanadi", async ({ page, context }) => {
  await context.grantPermissions(["clipboard-read", "clipboard-write"]);
  await page.goto("/post/laravel-events");
  await page.waitForLoadState("networkidle");

  const button = page.locator(".copy-code");
  await button.click();
  await expect(button).toContainText("Nusxalandi");
  expect(await page.evaluate(() => navigator.clipboard.readText())).toContain("event(new PostCreated($post));");
  await expect(button).toContainText("Nusxalash", { timeout: 4000 });
});

test("fikr posti: mundarijasiz, fikr uslubida, qo'shni postlar", async ({ page }) => {
  await page.goto("/post/kichik-odatlar");
  await page.waitForLoadState("networkidle");

  await expect(page.locator(".read")).toHaveClass(/fikr/);
  await expect(page.locator("details.disc")).toHaveCount(0);
  await expect(page.locator(".tlink")).toHaveAttribute("href", "/fikrlar");
  await expect(page.locator(".r-nx").first()).toContainText("Keyingi");
  await expect(page.locator(".r-nx").first()).toHaveAttribute("href", "/post/laravel-events");
  await expect(page.locator(".r-nx").nth(1)).toContainText("Oldingi");
  await expect(page.locator(".r-au")).toHaveAttribute("href", "/men");
});

test("post sahifasida nav bo'limi kategoriyadan belgilanadi", async ({ page }) => {
  await page.goto("/post/laravel-events");
  await expect(page.locator(".tn.on")).toContainText("Dasturlash");
  await expect(page.locator(".tn.on")).toHaveCount(1);

  await page.goto("/post/kichik-odatlar");
  await expect(page.locator(".tn.on")).toContainText("Fikrlar");

  await page.getByRole("navigation", { name: "Boʻlimlar", exact: true }).getByRole("link", { name: /Men haqimda/ }).click();
  await expect(page).toHaveURL(/\/men$/);
  await expect(page.locator(".tn.on")).toContainText("Men haqimda");
});

test("o'qish chizig'i skroll bilan o'sadi", async ({ page }) => {
  await page.goto("/post/laravel-events");
  await page.waitForLoadState("networkidle");
  await page.evaluate(() => {
    const spacer = document.createElement("div");
    spacer.style.height = "3000px";
    document.querySelector(".prose")!.appendChild(spacer);
  });

  const width = () => page.locator(".progress").evaluate((el) => el.getBoundingClientRect().width);
  expect(await width()).toBe(0);

  await page.mouse.wheel(0, 1500);
  await expect.poll(width).toBeGreaterThan(20);
});

test("ulashish havolani nusxalaydi", async ({ page, context }) => {
  await context.grantPermissions(["clipboard-read", "clipboard-write"]);
  await page.goto("/post/kichik-odatlar");
  await page.waitForLoadState("networkidle");

  await page.getByRole("button", { name: "Ulashish" }).click();
  await expect(page.locator(".toast.show")).toContainText("Havola nusxalandi");
  expect(await page.evaluate(() => navigator.clipboard.readText())).toContain("/post/kichik-odatlar");
});

test("post SEO: title, description, canonical, OG, Twitter, JSON-LD", async ({ page }) => {
  await page.goto("/post/laravel-events");

  await expect(page).toHaveTitle("Laravel events: kodni ajratishning oddiy yoʻli — Utkirbek");
  const meta = (selector: string) => page.locator(selector).getAttribute("content");
  expect(await meta('meta[name="description"]')).toContain("Event va listener");
  await expect(page.locator('link[rel="canonical"]')).toHaveAttribute("href", /\/post\/laravel-events$/);
  expect(await meta('meta[property="og:type"]')).toBe("article");
  expect(await meta('meta[property="og:title"]')).toContain("Laravel events");
  expect(await meta('meta[property="og:site_name"]')).toBe("Utkirbek");
  expect(await meta('meta[property="article:published_time"]')).toContain("2026-10-28");
  expect(await meta('meta[name="twitter:card"]')).toBe("summary");
  await expect(page.locator('link[rel="alternate"][type="application/rss+xml"]')).toHaveAttribute("href", /\/rss\.xml$/);

  const ld = JSON.parse((await page.locator('script[type="application/ld+json"]').textContent()) ?? "{}");
  expect(ld["@type"]).toBe("BlogPosting");
  expect(ld.headline).toContain("Laravel events");
  expect(ld.inLanguage).toBe("uz");
});

test("mavjud bo'lmagan post 404 va noindex", async ({ page }) => {
  const response = await page.goto("/post/yoq-post");

  expect(response?.status()).toBe(404);
  await expect(page.getByRole("heading", { level: 1 })).toHaveText("Sahifa topilmadi");
  await expect(page.locator('meta[name="robots"][content*="noindex"]').first()).toBeAttached();
  await expect(page.getByRole("navigation", { name: "Boʻlimlar", exact: true })).toBeVisible();
  await page.getByRole("link", { name: "Bosh sahifaga" }).click();
  await expect(page).toHaveURL(/\/$/);
});

test("noma'lum manzil nav bilan 404 beradi", async ({ page }) => {
  const response = await page.goto("/yoq/sahifa/umuman");

  expect(response?.status()).toBe(404);
  await expect(page.getByRole("heading", { level: 1 })).toHaveText("Sahifa topilmadi");
  await expect(page.getByRole("navigation", { name: "Boʻlimlar", exact: true })).toBeVisible();
});

test("bosh sahifa metama'lumotlari: canonical, OG, RSS", async ({ page }) => {
  await page.goto("/");

  await expect(page.locator('link[rel="canonical"]')).toHaveAttribute("href", "http://localhost:3100");
  await expect(page.locator('meta[property="og:site_name"]')).toHaveAttribute("content", "Utkirbek");
  await expect(page.locator('meta[property="og:locale"]')).toHaveAttribute("content", "uz_UZ");
  await expect(page.locator('link[rel="alternate"][type="application/rss+xml"]')).toHaveCount(1);
});

test("sitemap.xml sahifalar va postlarni oz ichiga oladi", async ({ request }) => {
  const response = await request.get("/sitemap.xml");

  expect(response.status()).toBe(200);
  expect(response.headers()["content-type"]).toContain("application/xml");
  const body = await response.text();
  expect(body).toContain("<urlset");
  for (const path of ["/", "/fikrlar", "/dasturlash", "/loyihalar", "/faollik", "/men", "/post/laravel-events", "/post/kichik-odatlar"]) {
    expect(body).toContain(`<loc>http://localhost:3100${path}</loc>`);
  }
  expect(body).toContain("<lastmod>2026-10-28</lastmod>");
});

test("rss.xml yaroqli va postlarni beradi", async ({ request }) => {
  const response = await request.get("/rss.xml");

  expect(response.status()).toBe(200);
  expect(response.headers()["content-type"]).toContain("application/rss+xml");
  const body = await response.text();
  expect(body).toContain('<rss version="2.0"');
  expect((body.match(/<item>/g) ?? []).length).toBe(3);
  expect(body).toContain("<title>Laravel events: kodni ajratishning oddiy yoʻli</title>");
  expect(body).toContain("<link>http://localhost:3100/post/laravel-events</link>");
  expect(body).toContain("<category>Dasturlash</category>");
  expect(body).toMatch(/<pubDate>[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4}/);
});

test("robots.txt admin va api'ni yopadi, sitemap'ni ko'rsatadi", async ({ request }) => {
  const body = await (await request.get("/robots.txt")).text();

  expect(body).toContain("Disallow: /admin");
  expect(body).toContain("Disallow: /api/");
  expect(body).toContain("Sitemap: http://localhost:3100/sitemap.xml");
});

test("yorug' mavzu 404 sahifada ham qo'llanadi", async ({ page }) => {
  await page.addInitScript(() => localStorage.setItem("ut4_theme", "light"));
  await page.goto("/yoq/sahifa");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });

  await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
  await expect
    .poll(() => page.evaluate(() => getComputedStyle(document.body).backgroundColor))
    .toBe("rgb(244, 244, 245)");
});
