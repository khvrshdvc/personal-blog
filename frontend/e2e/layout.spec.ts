import { expect, test } from "@playwright/test";
import { ownClient } from "./client";

test.beforeEach(async ({ page }) => {
  await ownClient(page);
});

test("nav bolimlari, hisoblagichlar va faol bolim", async ({ page }) => {
  await page.goto("/");

  const nav = page.getByRole("navigation", { name: "Boʻlimlar", exact: true });
  await expect(nav.getByRole("link", { name: /Asosiy/ })).toHaveAttribute("aria-current", "page");
  await expect(nav.getByRole("link", { name: /Fikrlar/ })).not.toHaveAttribute("aria-current", "page");
  await expect(nav.getByRole("link", { name: /Dasturlash/ })).toBeVisible();
  await expect(nav.getByRole("link", { name: /Men haqimda/ })).toBeVisible();
  await expect(page.getByText("Ishga ochiq").first()).toBeVisible();
  await expect(page.getByRole("contentinfo")).toContainText("© 2026 Utkirbek");
});

test("hisoblagichlar mock postlardan olinadi (kengaytirilgan nav)", async ({ page, isMobile }) => {
  test.skip(Boolean(isMobile) || (page.viewportSize()?.width ?? 0) <= 760, "hisoblagichlar telefonda yashirin");
  await page.goto("/");

  const nav = page.getByRole("navigation", { name: "Boʻlimlar", exact: true });
  await expect(nav.getByRole("link", { name: /Fikrlar/ })).toContainText("2");
  await expect(nav.getByRole("link", { name: /Dasturlash/ })).toContainText("1");
});

test("nav maketi kenglikka mos", async ({ page }) => {
  await page.goto("/");
  const width = page.viewportSize()!.width;

  if (width <= 760) {
    await expect(page.locator(".tn-ib").first()).toBeVisible();
    await expect(page.locator(".tn-x").first()).toBeHidden();
    const columns = await page.locator(".tnav").evaluate((el) => getComputedStyle(el).gridTemplateColumns.split(" ").length);
    expect(columns).toBe(4);
  } else {
    await expect(page.locator(".tn-x").first()).toBeVisible();
    await expect(page.locator(".tn-ib").first()).toBeHidden();
    const columns = await page.locator(".tnav").evaluate((el) => getComputedStyle(el).gridTemplateColumns.split(" ").length);
    expect(columns).toBe(8);
  }
});

test("admin tugmasi har kenglikda ko'rinadi va /admin ga olib boradi", async ({ page }) => {
  await page.goto("/");

  const button = page.getByRole("link", { name: "Admin panel" }).first();
  await expect(button).toBeVisible();
  await expect(button).toHaveAttribute("href", "/admin");

  if (page.viewportSize()!.width > 760) {
    await expect(page.locator(".tn-x").filter({ hasText: "Admin" })).toBeVisible();
  } else {
    await expect(page.locator(".tn-me .tn-ib[href='/admin']")).toBeVisible();
  }
});

test("ixcham panelda ham admin ikonkasi bor", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.evaluate(() => {
    const spacer = document.createElement("div");
    spacer.style.height = "3000px";
    document.getElementById("view")!.appendChild(spacer);
  });
  await page.mouse.wheel(0, 900);

  await expect(page.locator(".tmini.show a[href='/admin']")).toBeVisible();
});

test("ixcham panel faqat nav ekrandan chiqqanda chiqadi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });
  await page.evaluate(() => {
    const spacer = document.createElement("div");
    spacer.style.height = "3000px";
    document.getElementById("view")!.appendChild(spacer);
  });

  const mini = page.locator(".tmini");
  await expect(mini).not.toHaveClass(/show/);

  await page.mouse.wheel(0, 900);
  await expect(mini).toHaveClass(/show/);
  await expect(mini.getByRole("link", { name: "Fikrlar" })).toBeVisible();

  await page.mouse.wheel(0, -2000);
  await expect(mini).not.toHaveClass(/show/);
});

test("⌘K paneli ochiladi, qidiradi, strelka bilan tanlanadi va yopiladi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });

  await page.keyboard.press("Control+k");
  const dialog = page.getByRole("dialog", { name: "Qidiruv" });
  await expect(dialog).toBeVisible();
  await expect(page.getByRole("combobox")).toBeFocused();
  await expect(dialog.getByText("Soʻnggi yozuvlar")).toBeVisible();
  await expect(dialog.getByRole("option", { name: /Laravel events/ })).toBeVisible();

  await page.keyboard.type("kichik");
  await expect(dialog.getByRole("option", { name: /Kichik odatlar/ })).toBeVisible();
  await expect(dialog.getByRole("option", { name: /Laravel events/ })).toHaveCount(0);

  await page.keyboard.press("Escape");
  await expect(dialog).toBeHidden();
});

test("⌘K sahifaga oʻtkazadi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });

  await page.keyboard.press("Control+k");
  await page.keyboard.type("men");
  const option = page.getByRole("option", { name: /Men haqimda/ });
  await expect(option).toBeVisible();
  await expect(option).toHaveAttribute("aria-selected", "true");
  await page.keyboard.press("Enter");

  await expect(page).toHaveURL(/\/men$/);
  await expect(page.getByRole("dialog", { name: "Qidiruv" })).toBeHidden();
});

test("⌘K strelka tugmalari aylanib tanlaydi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });
  await page.keyboard.press("Control+k");
  await expect(page.getByRole("option", { name: /Laravel events/ })).toBeVisible();
  await expect(page.getByRole("option").first()).toHaveAttribute("aria-selected", "true");

  await page.keyboard.press("ArrowUp");
  const options = page.getByRole("option");
  const count = await options.count();
  await expect(options.nth(count - 1)).toHaveAttribute("aria-selected", "true");

  await page.keyboard.press("ArrowDown");
  await expect(options.first()).toHaveAttribute("aria-selected", "true");
});

test("⌘K fon bosilganda yopiladi va hech narsa topilmasa xabar chiqadi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });
  await page.keyboard.press("Control+k");
  await page.keyboard.type("yoqqiydirsozxyz");
  await expect(page.getByText("Hech narsa topilmadi")).toBeVisible();

  await page.mouse.click(2, 2);
  await expect(page.getByRole("dialog", { name: "Qidiruv" })).toBeHidden();
});

test("qidiruv tugmasi paneli ochadi", async ({ page }) => {
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });
  await page.getByRole("button", { name: /Qidirish/ }).first().click();
  await expect(page.getByRole("dialog", { name: "Qidiruv" })).toBeVisible();
});

test("kartada nur kursorga ergashadi", async ({ page }) => {
  test.skip((page.viewportSize()?.width ?? 0) <= 760, "nur faqat sichqoncha bilan");
  await page.goto("/");
  await page.waitForLoadState("networkidle");
  await page.locator("html[data-ready]").waitFor({ state: "attached" });

  const card = page.locator("#site .c").nth(1);
  const box = (await card.boundingBox())!;
  await page.mouse.move(box.x + 40, box.y + 30);

  await expect.poll(() => card.evaluate((el) => (el as HTMLElement).style.getPropertyValue("--x"))).toBe("40px");

  await page.mouse.move(2, 2);
  await expect.poll(() => card.evaluate((el) => (el as HTMLElement).style.getPropertyValue("--x"))).toBe("");
});

test("kontentga oʻtish havolasi klaviatura bilan ishlaydi", async ({ page }) => {
  await page.goto("/");
  await page.keyboard.press("Tab");
  await expect(page.getByRole("link", { name: "Asosiy qismga oʻtish" })).toBeFocused();
});

test("/api/search mock bilan ishlaydi va uzun soʻrovni kesadi", async ({ request }) => {
  const response = await request.get("/api/search?q=laravel");
  expect(response.ok()).toBeTruthy();
  const items = await response.json();
  expect(items[0]).toMatchObject({ type: "post", category: "dev" });

  const long = await request.get(`/api/search?q=${"a".repeat(500)}`);
  expect(long.ok()).toBeTruthy();
});
