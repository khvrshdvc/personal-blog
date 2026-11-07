import { expect, test, type Page } from "@playwright/test";
import { API, initialState, mockAdminApi, type MockState } from "./admin-mock";

const PNG = Buffer.from("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==", "base64");

async function open(page: Page, path: string, mutate?: (state: MockState) => void) {
  const state = initialState();
  mutate?.(state);
  await mockAdminApi(page, state);
  await page.goto(path);
  await page.waitForLoadState("networkidle");

  return state;
}

function callsTo(state: MockState, method: string, path: RegExp) {
  return state.calls.filter((call) => call.method === method && path.test(call.path));
}

test.describe("funksional (keng ekran)", () => {
  test.beforeEach(async ({ browserName }, testInfo) => {
    void browserName;
    test.skip(testInfo.project.name !== "toliq", "funksional testlar faqat keng ekranda");
  });

  test("mehmon login sahifasiga yo'naltiriladi va admin noindex", async ({ page }) => {
    await open(page, "/admin", (state) => (state.loggedIn = false));

    await expect(page).toHaveURL(/\/admin\/login$/);
    await expect(page.getByRole("heading", { name: "Admin panel" })).toBeVisible();
    await expect(page.locator('meta[name="robots"][content*="noindex"]').first()).toBeAttached();
  });

  test("login: noto'g'ri parol xato ko'rsatadi, to'g'risi dashboardga olib boradi", async ({ page }) => {
    const state = await open(page, "/admin/login", (s) => (s.loggedIn = false));

    await page.getByLabel("Parol").fill("xato");
    await page.getByRole("button", { name: /Kirish/ }).click();
    await expect(page.locator(".lg-err")).toHaveText("Parol notoʻgʻri.");
    await expect(page).toHaveURL(/\/admin\/login$/);

    await page.getByLabel("Parol").fill("sir-parol");
    await page.getByRole("button", { name: /Kirish/ }).click();
    await expect(page).toHaveURL(/\/admin$/);
    expect(state.csrfCalls).toBeGreaterThan(0);
    await expect(page.getByRole("heading", { level: 1 })).toContainText("Utkirbek");
  });

  test("login: ko'p urinish xabari va parolsiz tugma o'chiq", async ({ page }) => {
    await open(page, "/admin/login", (s) => {
      s.loggedIn = false;
      s.rateLimited = true;
    });

    await expect(page.getByRole("button", { name: /Kirish/ })).toBeDisabled();
    await page.getByLabel("Parol").fill("x");
    await page.getByRole("button", { name: /Kirish/ }).click();
    await expect(page.locator(".lg-err")).toContainText("Juda koʻp urinish");
  });

  test("allaqachon kirgan foydalanuvchi login'dan dashboardga o'tadi", async ({ page }) => {
    await open(page, "/admin/login");

    await expect(page).toHaveURL(/\/admin$/);
  });

  test("dashboard: statistika, oxirgi tahrir, davom ettirish, holat", async ({ page }) => {
    await open(page, "/admin");

    await expect(page.locator(".d-hero h2")).toHaveText("Tugallanmagan qoralama");
    await expect(page.locator(".d-hero .pill")).toHaveText("Qoralama");
    await expect(page.locator(".d-st").nth(0).locator("b")).toHaveText("2");
    await expect(page.locator(".d-st").nth(1).locator("b")).toHaveText("1");
    await expect(page.locator(".d-hero .dbtn.pri")).toHaveAttribute("href", "/admin/posts/3");
    await expect(page.locator(".d-hm i")).toHaveCount(182);
    await expect(page.locator(".chk-r.ok")).toHaveCount(5);
    await expect(page.locator(".d-prj .pi2")).toHaveCount(2);
    await expect(page.locator(".as-t.on")).toContainText("Bosh sahifa");
    await expect(page.locator(".as-t").nth(1).locator(".as-c")).toHaveText("3");
    await expect(page.locator(".as-t").nth(2).locator(".as-c")).toHaveText("2");
  });

  test("N tugmasi yangi yozuvni ochadi, input ichida esa ochmaydi", async ({ page }) => {
    await open(page, "/admin/settings");
    await page.getByLabel("Men haqimda matni").click();
    await page.keyboard.press("n");
    await expect(page).toHaveURL(/\/admin\/settings$/);

    await page.locator(".as-me").click();
    await expect(page).toHaveURL(/\/admin$/);
    await page.keyboard.press("n");
    await expect(page).toHaveURL(/\/admin\/posts\/new$/);
  });

  test("yozuvlar ro'yxati: holatlar, qidiruv va tanlash", async ({ page }) => {
    await open(page, "/admin/posts");

    await expect(page.locator(".ali")).toHaveCount(3);
    await expect(page.locator(".ali").filter({ hasText: "Tugallanmagan qoralama" }).locator(".ar-st")).toContainText("Qoralama");
    await expect(page.locator(".ali").filter({ hasText: "Laravel events" }).locator(".ar-st")).toContainText("Dasturlash · Laravel · Chop etilgan");
    await expect(page.locator(".a-empty h2")).toHaveText("Bugun nima haqida yozasiz?");

    await page.getByRole("searchbox").fill("laravel");
    await expect(page.locator(".ali")).toHaveCount(1);
    await page.getByRole("searchbox").fill("yoqqiydir");
    await expect(page.getByText("Yozuv topilmadi")).toBeVisible();

    await page.getByRole("searchbox").fill("");
    await page.locator(".arow").filter({ hasText: "Birinchi fikr" }).click();
    await expect(page).toHaveURL(/\/admin\/posts\/1$/);
    await expect(page.locator("#ft")).toHaveValue("Birinchi fikr");
    await expect(page.locator(".arow.on")).toContainText("Birinchi fikr");
  });

  test("yozuvni ro'yxatdan o'chirish (tahrirlash rejimi)", async ({ page }) => {
    const state = await open(page, "/admin/posts");

    await page.getByRole("button", { name: "Tahrirlash" }).click();
    await page.getByRole("button", { name: "Birinchi fikr — oʻchirish" }).click();
    await page.locator(".ali.reveal .delbtn").click();

    await expect(page.locator(".ali")).toHaveCount(2);
    await expect(page.locator(".toast.show")).toContainText("Yozuv oʻchirildi");
    expect(state.posts.some((post) => post.id === 1)).toBe(false);
  });

  test("yangi yozuv: avtosaqlash yaratadi va manzilni almashtiradi", async ({ page }) => {
    const state = await open(page, "/admin/posts/new/fikr");

    await expect(page.locator(".pill")).toHaveText("Qoralama");
    await page.locator("#ft").fill("Yangi sarlavha");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");

    await expect(page).toHaveURL(/\/admin\/posts\/100$/);
    expect(callsTo(state, "POST", /^\/posts$/)).toHaveLength(1);
    expect(callsTo(state, "PATCH", /^\/posts\/100$/).at(-1)?.body).toMatchObject({ title: "Yangi sarlavha", category: "fikr", slug: "" });
    await expect(page.locator(".ali").first()).toContainText("Yangi sarlavha");
    await expect(page.locator(".as-t").nth(1).locator(".as-c")).toHaveText("4");
  });

  test("yangi yozuvning sanasi Toshkent vaqti bo'yicha (UTC emas)", async ({ page }) => {
    await page.clock.install({ time: new Date("2026-11-07T21:30:00Z") });
    const state = await open(page, "/admin/posts/new/fikr");

    await page.locator("#ft").fill("Sana sinovi");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");

    expect(callsTo(state, "PATCH", /^\/posts\/100$/).at(-1)?.body).toMatchObject({ published_at: "2026-11-08" });
  });

  test("bo'sh yozuv saqlanmaydi", async ({ page }) => {
    const state = await open(page, "/admin/posts/new/dev");

    await page.locator("#ft").fill("x");
    await page.locator("#ft").fill("");
    await page.waitForTimeout(1000);

    expect(callsTo(state, "POST", /^\/posts$/)).toHaveLength(0);
  });

  test("dev bo'limidagi yangi yozuv dev tegini oladi", async ({ page }) => {
    await open(page, "/admin/posts/new/dev");

    await expect(page.getByRole("group", { name: "Boʻlim" }).getByRole("button", { name: "Dasturlash" })).toHaveAttribute("aria-pressed", "true");
    await expect(page.locator("#ftag")).toHaveValue("Laravel");
    await page.getByRole("group", { name: "Boʻlim" }).getByRole("button", { name: "Fikr" }).click();
    await expect(page.locator("#ftag")).toHaveValue("Hayot");
    await expect(page.locator("#ftag option")).toHaveText(["Hayot", "Oʻsish", "Ustozlik", "Til"]);
  });

  test("Ctrl+S darhol saqlaydi (600ms kutmasdan)", async ({ page }) => {
    const state = await open(page, "/admin/posts/3");

    await page.locator("#ft").fill("Boshqa nom");
    await page.keyboard.press("Control+s");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi", { timeout: 450 });
    expect(callsTo(state, "PATCH", /^\/posts\/3$/).at(-1)?.body).toMatchObject({ title: "Boshqa nom" });
  });

  test("qoralamani chop etish", async ({ page }) => {
    const state = await open(page, "/admin/posts/3");

    await page.getByRole("button", { name: "Chop etish" }).click();

    await expect(page.locator(".toast.show")).toContainText("Yozuv chop etildi");
    await expect(page.locator(".toast a")).toHaveAttribute("href", "/post/tugallanmagan");
    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
    await expect(page.getByRole("button", { name: "Yangilash" })).toBeDisabled();
    expect(state.posts.find((post) => post.id === 3)?.status).toBe("published");
    await expect(page.locator('a.gbtn[aria-label="Saytda koʻrish"]')).toHaveAttribute("href", "/post/tugallanmagan");
  });

  test("Ctrl+Enter chop etadi", async ({ page }) => {
    await open(page, "/admin/posts/3");

    await page.locator("#fb").click();
    await page.keyboard.press("Control+Enter");

    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
  });

  test("bo'sh matnli qoralama chop etilmaydi (validatsiya xabari)", async ({ page }) => {
    await open(page, "/admin/posts/3", (state) => {
      state.posts[2].body = "";
    });

    await page.getByRole("button", { name: "Chop etish" }).click();

    await expect(page.locator(".toast.show")).toContainText("Matn boʻsh");
    await expect(page.locator(".pill")).toHaveText("Qoralama");
  });

  test("chop etilgan yozuvni tahrirlash pending'ga yoziladi va 'Yangilash' qo'llaydi", async ({ page }) => {
    const state = await open(page, "/admin/posts/1");

    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
    await expect(page.getByRole("button", { name: "Yangilash" })).toBeDisabled();

    await page.locator("#ft").fill("Yangi nom");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");

    await expect(page.locator(".pill")).toHaveText("Oʻzgarish bor");
    await expect(page.getByRole("button", { name: "Yangilash" })).toBeEnabled();
    await expect(page.getByRole("button", { name: "Bekor qilish" })).toBeVisible();
    expect(state.posts[0].title).toBe("Birinchi fikr");
    expect(state.posts[0].pending?.title).toBe("Yangi nom");

    await page.getByRole("button", { name: "Yangilash" }).click();
    await expect(page.locator(".toast.show")).toContainText("Yozuv yangilandi");
    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
    expect(state.posts[0].title).toBe("Yangi nom");
    expect(state.posts[0].pending).toBeNull();
  });

  test("kutilayotgan o'zgarishni bekor qilish asl matnni qaytaradi", async ({ page }) => {
    await open(page, "/admin/posts/1");

    await page.locator("#ft").fill("Vaqtinchalik");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");
    await page.getByRole("button", { name: "Bekor qilish" }).click();

    await expect(page.locator("#ft")).toHaveValue("Birinchi fikr");
    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
    await expect(page.locator(".toast.show")).toContainText("Oʻzgarishlar bekor qilindi");
  });

  test("qayta kirganda pending o'zgarish ko'rsatiladi", async ({ page }) => {
    await open(page, "/admin/posts/1", (state) => {
      state.posts[0].pending = { category: "fikr", title: "Kutilayotgan", slug: "birinchi-fikr", tag: "Hayot", excerpt: "", body: "Matn", published_at: "2026-10-01", cover: "" };
    });

    await expect(page.locator("#ft")).toHaveValue("Kutilayotgan");
    await expect(page.locator(".pill")).toHaveText("Oʻzgarish bor");
  });

  test("markdown asboblar paneli va Ctrl+B", async ({ page }) => {
    await open(page, "/admin/posts/3");
    const body = page.locator("#fb");
    const select = (start: number, end: number) => body.evaluate((el: HTMLTextAreaElement, [s, e]) => el.setSelectionRange(s, e), [start, end]);

    await body.fill("salom dunyo");
    await select(0, 5);
    await page.getByRole("button", { name: "Qalin", exact: true }).click();
    await expect(body).toHaveValue("**salom** dunyo");

    await body.fill("salom dunyo");
    await select(6, 11);
    await page.getByRole("button", { name: "Havola" }).click();
    await expect(body).toHaveValue("salom [dunyo](https://)");

    await body.fill("birinchi");
    await select(0, 8);
    await page.getByRole("button", { name: "Sarlavha", exact: true }).click();
    await expect(body).toHaveValue("## birinchi");
    await page.getByRole("button", { name: "Sarlavha", exact: true }).click();
    await expect(body).toHaveValue("birinchi");

    await body.fill("a\nb");
    await select(0, 3);
    await page.getByRole("button", { name: "Roʻyxat" }).click();
    await expect(body).toHaveValue("- a\n- b");

    await body.fill("salom");
    await select(0, 5);
    await body.press("Control+b");
    await expect(body).toHaveValue("**salom**");
    await body.press("Control+i");
    await expect(body).toHaveValue("***salom***");

    await body.fill("kod");
    await select(0, 3);
    await page.getByRole("button", { name: "Kod bloki" }).click();
    await expect(body).toHaveValue("```\nkod\n```");
  });

  test("ko'rinish rejimi matnni serverda render qiladi", async ({ page }) => {
    await open(page, "/admin/posts/3");

    await page.locator("#fb").fill("Birinchi\n\nIkkinchi");
    await page.getByRole("group", { name: "Rejim" }).getByRole("button", { name: "Koʻrinish" }).click();

    await expect(page.locator(".ed-prev .r-title")).toHaveText("Tugallanmagan qoralama");
    await expect(page.locator(".ed-prev .prose p")).toHaveText(["Birinchi", "Ikkinchi"]);
    await page.getByRole("group", { name: "Rejim" }).getByRole("button", { name: "Yozish" }).click();
    await expect(page.locator("#fb")).toHaveValue("Birinchi\n\nIkkinchi");
  });

  test("yozuv sozlamalari: havola, tavsif, sana va muqova", async ({ page }) => {
    const state = await open(page, "/admin/posts/3");

    await page.getByRole("button", { name: "Yozuv sozlamalari" }).click();
    const sheet = page.getByRole("dialog", { name: "Yozuv sozlamalari" });
    await expect(sheet).toBeVisible();

    await sheet.locator("#fslug").fill("mening-havolam");
    await sheet.getByLabel("Qisqacha tavsif").fill("Qisqa gap");
    await sheet.getByLabel("Sana").fill("2026-05-05");
    await expect(sheet.getByText("9 / 200")).toBeVisible();

    await sheet.locator('input[type="file"]').setInputFiles({ name: "c.png", mimeType: "image/png", buffer: PNG });
    await expect(sheet.locator(".sh-cover img")).toHaveAttribute("src", /covers\/test\.png$/);
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");

    expect(callsTo(state, "POST", /^\/uploads$/)).toHaveLength(1);
    expect(callsTo(state, "PATCH", /^\/posts\/3$/).at(-1)?.body).toMatchObject({
      slug: "mening-havolam",
      excerpt: "Qisqa gap",
      published_at: "2026-05-05",
      cover: "covers/test.png",
    });
    expect(state.posts[2].slug).toBe("mening-havolam");

    await sheet.getByRole("button", { name: "Olib tashlash" }).click();
    await expect(sheet.locator(".sh-cover img")).toHaveCount(0);
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");
    expect(callsTo(state, "PATCH", /^\/posts\/3$/).at(-1)?.body).toMatchObject({ cover: "" });

    await page.keyboard.press("Escape");
    await expect(sheet).toBeHidden();
  });

  test("band havola uchun qoralamaga raqam qo'shilgani ma'lum qilinadi", async ({ page }) => {
    await open(page, "/admin/posts/3");

    await page.getByRole("button", { name: "Yozuv sozlamalari" }).click();
    await page.locator("#fslug").fill("laravel-events");

    await expect(page.locator(".fg-f.err")).toHaveText("Bu havola band edi — raqam qoʻshildi");
    await expect(page.locator("#fslug")).toHaveValue("laravel-events");
  });

  test("band havola bilan yangilash 422 beradi va sozlamalarni ochadi", async ({ page }) => {
    await open(page, "/admin/posts/1");

    await page.getByRole("button", { name: "Yozuv sozlamalari" }).click();
    await page.locator("#fslug").fill("laravel-events");
    await expect(page.locator(".save-st")).toHaveText("Saqlandi");
    await page.getByRole("button", { name: "Tayyor" }).click();
    await page.getByRole("button", { name: "Yangilash" }).click();

    await expect(page.locator(".toast.show")).toContainText("Bu havola band");
    await expect(page.getByRole("dialog", { name: "Yozuv sozlamalari" })).toBeVisible();
  });

  test("qoralamaga olish va o'chirish tasdiq so'raydi", async ({ page }) => {
    const state = await open(page, "/admin/posts/1");

    await page.getByRole("button", { name: "Yozuv sozlamalari" }).click();
    await page.getByRole("button", { name: "Qoralamaga olish" }).click();
    await page.getByRole("alertdialog").getByRole("button", { name: "Bekor qilish" }).click();
    await expect(page.locator(".pill")).toHaveText("Chop etilgan");

    await page.getByRole("button", { name: "Qoralamaga olish" }).click();
    await page.getByRole("alertdialog").getByRole("button", { name: "Qoralamaga olish" }).click();
    await expect(page.locator(".pill")).toHaveText("Qoralama");
    expect(state.posts[0].status).toBe("draft");

    await page.getByRole("button", { name: "Yozuv sozlamalari" }).click();
    await page.getByRole("button", { name: "Yozuvni oʻchirish" }).click();
    await page.getByRole("alertdialog").getByRole("button", { name: "Oʻchirish" }).click();
    await expect(page).toHaveURL(/\/admin\/posts$/);
    await expect(page.locator(".toast.show")).toContainText("Yozuv oʻchirildi");
    expect(state.posts.some((post) => post.id === 1)).toBe(false);
  });

  test("yo'q yozuv ro'yxatga qaytaradi", async ({ page }) => {
    await open(page, "/admin/posts/999");

    await expect(page).toHaveURL(/\/admin\/posts$/);
    await expect(page.locator(".toast.show")).toContainText("Yozuv topilmadi");
  });

  test("CSRF muddati tugasa so'rov avtomatik qayta yuboriladi", async ({ page }) => {
    const state = await open(page, "/admin/posts/3");
    const before = state.csrfCalls;
    state.expireCsrfOnce = true;

    await page.getByRole("button", { name: "Chop etish" }).click();

    await expect(page.locator(".pill")).toHaveText("Chop etilgan");
    expect(state.csrfCalls).toBeGreaterThan(before);
  });

  test("sessiya tugasa login sahifasiga qaytariladi", async ({ page }) => {
    const state = await open(page, "/admin");

    state.forceUnauthorized = true;
    await page.locator(".as-t").filter({ hasText: "Sozlamalar" }).click();

    await expect(page).toHaveURL(/\/admin\/login$/);
  });

  test("loyihalar: ro'yxat, tahrirlash va saqlash", async ({ page }) => {
    const state = await open(page, "/admin/projects");

    await expect(page.locator(".ali")).toHaveCount(2);
    await expect(page.locator(".ali").nth(1).locator(".pj-r")).toHaveText("repo koʻrsatilmagan");

    await page.locator(".arow").filter({ hasText: "Shaxsiy blog" }).click();
    await expect(page.locator('input[name="name"]')).toHaveValue("Shaxsiy blog");
    await expect(page.locator('input[name="stack"]')).toHaveValue("Laravel, Next.js");
    await expect(page.locator(".pf-p .app-n")).toHaveText("Shaxsiy blog");

    await page.locator('input[name="name"]').fill("Yangi nom");
    await expect(page.locator(".pf-p .app-n")).toHaveText("Yangi nom");
    await page.getByRole("button", { name: "Saqlash" }).click();

    await expect(page.locator(".toast.show")).toContainText("Loyiha saqlandi");
    expect(state.projects[0].name).toBe("Yangi nom");
    await expect(page.locator(".arow.on")).toContainText("Yangi nom");
  });

  test("yangi loyiha: validatsiya, saqlash va o'chirish", async ({ page }) => {
    const state = await open(page, "/admin/projects/new");

    await page.getByRole("button", { name: "Saqlash" }).click();
    await expect(page.locator(".err").first()).toHaveText("Loyiha nomini yozing");

    await page.locator('input[name="name"]').fill("Telegram bot");
    await page.locator('input[name="repo"]').fill("utkirbek/bot");
    await page.locator('input[name="stack"]').fill("Python,  aiogram , ");
    await expect(page.locator(".pf-p .app-s")).toHaveText("Python · aiogram");
    await page.getByRole("button", { name: "Saqlash" }).click();

    await expect(page).toHaveURL(/\/admin\/projects\/100$/);
    expect(callsTo(state, "POST", /^\/projects$/).at(-1)?.body).toMatchObject({ name: "Telegram bot", stack: ["Python", "aiogram"] });
    await expect(page.locator(".ali")).toHaveCount(3);

    await page.locator(".d-r").getByRole("button", { name: "Oʻchirish" }).click();
    await page.getByRole("alertdialog").getByRole("button", { name: "Oʻchirish" }).click();
    await expect(page).toHaveURL(/\/admin\/projects$/);
    expect(state.projects).toHaveLength(2);
  });

  test("loyihalar: GitHub'dan yuklash", async ({ page }) => {
    const state = await open(page, "/admin/projects");

    await page.getByRole("button", { name: "GitHub'dan yuklash" }).first().click();
    await expect(page.locator(".toast.show")).toContainText("2 ta yangi loyiha");
    await expect(page.locator(".ali")).toHaveCount(4);
    expect(callsTo(state, "POST", /^\/projects\/sync$/)).toHaveLength(1);

    await page.getByRole("button", { name: "GitHub'dan yuklash" }).first().click();
    await expect(page.locator(".toast.show")).toContainText("yangi loyiha topilmadi");
  });

  test("sozlamalar: avtosaqlash, holat tugmasi va chiqish", async ({ page }) => {
    const state = await open(page, "/admin/settings");

    await expect(page.getByLabel("Men haqimda matni")).toHaveValue("Birinchi xatboshi.\n\nIkkinchi xatboshi.");
    await expect(page.locator(".now-k")).toHaveCount(4);
    await expect(page.locator(".ln-n")).toHaveCount(3);
    await expect(page.locator(".tgp-t")).toHaveCount(2);

    await page.getByRole("switch", { name: "Ishga ochiq" }).click();
    await expect(page.locator(".sv")).toHaveText("Saqlandi");
    expect(state.settings.open).toBe(false);

    await page.getByLabel("Men haqimda matni").fill("Yangi bio");
    await page.locator(".ln-p").first().fill("150");
    await page.locator(".now-v").first().fill("Yangi matn");
    await expect(page.locator(".sv")).toHaveText("Saqlandi");

    const put = callsTo(state, "PUT", /^\/settings$/).at(-1)?.body as Record<string, unknown>;
    expect(put).toMatchObject({ bio: "Yangi bio", open: false, github: "utkirbek" });
    expect((put.learn as { p: number }[])[0].p).toBe(100);
    expect((put.now as { v: string }[])[0].v).toBe("Yangi matn");
    expect(put.now as unknown[]).toHaveLength(4);
    expect((put.telegram as { posts: unknown[] }).posts).toHaveLength(2);

    await page.getByRole("button", { name: "Chiqish" }).last().click();
    await expect(page).toHaveURL(/\/admin\/login$/);
    expect(state.loggedIn).toBe(false);
  });

  test("sozlamalarda mavzu almashadi", async ({ page }) => {
    await open(page, "/admin/settings");

    await page.getByRole("group", { name: "Mavzu" }).getByRole("button", { name: "Yorugʻ" }).click();
    await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
  });

  test("API javob bermasa sozlamalar xabar ko'rsatadi", async ({ page }) => {
    await mockAdminApi(page, initialState());
    await page.route(`${API}/api/admin/settings`, (route) => route.fulfill({ status: 500, headers: { "Access-Control-Allow-Origin": "http://localhost:3100", "Access-Control-Allow-Credentials": "true" }, body: "{}" }));
    await page.goto("/admin/settings");

    await expect(page.getByRole("heading", { level: 1 })).toHaveText("Sozlamalarni yuklab boʻlmadi");
  });
});

test("telefon: pastki navigatsiya, yon panel yo'q", async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== "telefon");
  await open(page, "/admin");

  await expect(page.locator(".abar")).toBeVisible();
  await expect(page.locator(".a-side")).toBeHidden();
  await page.locator(".abar").getByRole("link", { name: "Yozuvlar" }).click();
  await expect(page).toHaveURL(/\/admin\/posts$/);
  await expect(page.locator(".a-list")).toBeVisible();
  await expect(page.locator(".a-detail")).toBeHidden();

  await page.locator(".arow").first().click();
  await expect(page.locator("#ft")).toBeVisible();
  await expect(page.locator(".a-list")).toBeHidden();
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(overflow).toBeLessThanOrEqual(0);
});

test("ixcham: yon panel ixcham, ro'yxat ko'rinadi", async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== "ixcham");
  await open(page, "/admin/posts");

  const width = await page.locator(".a-side").evaluate((el) => el.getBoundingClientRect().width);
  expect(width).toBeLessThan(120);
  await expect(page.locator(".a-list")).toBeVisible();
  await expect(page.locator(".a-detail")).toBeVisible();
});
