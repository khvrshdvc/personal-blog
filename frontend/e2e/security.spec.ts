import { expect, test } from "@playwright/test";
import { randomIp } from "./client";

const SECRET = "test-secret";


test.describe("revalidate webhook", () => {
  test("to'g'ri maxfiy kalit va teglar bilan 200 beradi", async ({ request }) => {
    const response = await request.post("/api/revalidate", { data: { secret: SECRET, tags: ["posts", "post:laravel-events", "projects", "settings"] } });

    expect(response.status()).toBe(200);
    expect(await response.json()).toEqual({ revalidated: ["posts", "post:laravel-events", "projects", "settings"] });
  });

  test("noto'g'ri yoki yo'q kalit 401 beradi", async ({ request }) => {
    expect((await request.post("/api/revalidate", { data: { secret: "xato", tags: ["posts"] } })).status()).toBe(401);
    expect((await request.post("/api/revalidate", { data: { tags: ["posts"] } })).status()).toBe(401);
    expect((await request.post("/api/revalidate", { data: { secret: 123, tags: ["posts"] } })).status()).toBe(401);
    expect((await request.post("/api/revalidate", { data: { secret: "", tags: ["posts"] } })).status()).toBe(401);
  });

  test("noto'g'ri teglar 400 beradi", async ({ request }) => {
    for (const tags of [[], "posts", ["boshqa"], ["post:"], ["post:a b"], ["post:../x"], [1], Array.from({ length: 21 }, () => "posts"), ["post:" + "a".repeat(201)]]) {
      const response = await request.post("/api/revalidate", { data: { secret: SECRET, tags } });

      expect(response.status(), JSON.stringify(tags).slice(0, 40)).toBe(400);
    }
  });

  test("yaroqsiz JSON 400, GET esa 405 beradi", async ({ request }) => {
    const raw = (body: string) => request.post("/api/revalidate", { headers: { "Content-Type": "application/json" }, data: Buffer.from(body) });

    for (const body of ["{xato", "null", "[]", '"matn"', "123", ""]) {
      expect((await raw(body)).status(), body).toBe(400);
    }

    expect((await request.get("/api/revalidate")).status()).toBe(405);
  });

  test("so'rovlar soni cheklanadi", async ({ request }) => {
    const headers = { "x-forwarded-for": randomIp() };
    let last = 0;

    for (let i = 0; i < 61; i++) {
      last = (await request.post("/api/revalidate", { headers, data: { secret: "xato", tags: ["posts"] } })).status();
    }

    expect(last).toBe(429);
  });
});

test.describe("xavfsizlik sarlavhalari", () => {
  for (const path of ["/", "/fikrlar", "/post/laravel-events", "/admin/login", "/yoq/sahifa"]) {
    test(`${path} himoya sarlavhalarini oladi`, async ({ request }) => {
      const headers = (await request.get(path)).headers();

      expect(headers["x-frame-options"]).toBe("DENY");
      expect(headers["x-content-type-options"]).toBe("nosniff");
      expect(headers["referrer-policy"]).toBe("strict-origin-when-cross-origin");
      expect(headers["permissions-policy"]).toContain("camera=()");
      expect(headers["strict-transport-security"]).toContain("max-age=");
      const csp = headers["content-security-policy"];
      expect(csp).toContain("frame-ancestors 'none'");
      expect(csp).toContain("object-src 'none'");
      expect(csp).toContain("base-uri 'self'");
      expect(csp).toContain("default-src 'self'");
      expect(csp).toContain("connect-src 'self' http://api.test");
      expect(csp).not.toContain("unsafe-eval");
    });
  }

  test("API route'lari ham sarlavha oladi", async ({ request }) => {
    const headers = (await request.get("/api/search?q=a")).headers();

    expect(headers["x-content-type-options"]).toBe("nosniff");
  });

  test("CSP sahifani buzmaydi: konsolda xato yo'q, mavzu va qidiruv ishlaydi", async ({ page }) => {
    const errors: string[] = [];
    page.on("console", (message) => {
      if (message.type() === "error" && !message.location().url.includes("_rsc=")) errors.push(message.text());
    });
    page.on("pageerror", (error) => errors.push(error.message));

    await page.goto("/post/laravel-events");
    await page.waitForLoadState("networkidle");
    await page.locator("html[data-ready]").waitFor({ state: "attached" });
    await page.getByRole("button", { name: "Mavzuni almashtirish" }).click();
    await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
    await page.keyboard.press("Control+k");
    await expect(page.getByRole("dialog", { name: "Qidiruv" })).toBeVisible();

    expect(errors.filter((text) => /Content Security Policy|Refused to/i.test(text))).toEqual([]);
    expect(errors).toEqual([]);
  });
});

test.describe("API route'lari cheklovi", () => {
  test("/api/search cheklanadi va boshqa mijozga ta'sir qilmaydi", async ({ request }) => {
    const mine = { "x-forwarded-for": randomIp() };
    const statuses: number[] = [];

    for (let i = 0; i < 121; i++) {
      statuses.push((await request.get("/api/search?q=a", { headers: mine })).status());
    }

    expect(statuses.slice(0, 120).every((status) => status === 200)).toBe(true);
    expect(statuses[120]).toBe(429);
    expect((await request.get("/api/search?q=a", { headers: { "x-forwarded-for": randomIp() } })).status()).toBe(200);
  });

  test("/api/posts ham cheklanadi", async ({ request }) => {
    const headers = { "x-forwarded-for": randomIp() };
    let last = 0;

    for (let i = 0; i < 121; i++) {
      last = (await request.get("/api/posts?category=fikr&q=a", { headers })).status();
    }

    expect(last).toBe(429);
  });
});
