import { expect, test } from "@playwright/test";

test("sahifa xatosiz ochiladi va mock postlar chiqadi", async ({ page }) => {
  const errors: string[] = [];
  page.on("pageerror", (error) => errors.push(error.message));
  page.on("console", (message) => {
    if (message.type() === "error") errors.push(message.text());
  });

  await page.goto("/");
  await page.waitForLoadState("networkidle");

  await expect(page.getByTestId("post-card")).toHaveCount(3);
  await expect(page.locator("html")).toHaveAttribute("lang", "uz");
  expect(errors).toEqual([]);

  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(overflow).toBeLessThanOrEqual(0);
});

test("qorongʻi mavzu birlamchi va tokenlar qoʻllanadi", async ({ page }) => {
  await page.goto("/");

  await expect(page.locator("html")).toHaveAttribute("data-theme", "dark");
  const bg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
  expect(bg).toBe("rgb(9, 9, 11)");
  const card = await page.evaluate(() => getComputedStyle(document.querySelector(".c")!).borderRadius);
  expect(card).toBe("24px");
});

test("mavzu almashadi, saqlanadi va miltillamaydi", async ({ page }) => {
  await page.goto("/");
  await page.getByRole("radio", { name: "Yorugʻ" }).click();

  await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
  await expect
    .poll(() => page.evaluate(() => getComputedStyle(document.body).backgroundColor))
    .toBe("rgb(244, 244, 245)");
  expect(await page.evaluate(() => localStorage.getItem("ut4_theme"))).toBe("light");

  await page.reload();
  await expect(page.locator("html")).toHaveAttribute("data-theme", "light");
  await expect(page.getByRole("radio", { name: "Yorugʻ" })).toHaveAttribute("aria-checked", "true");
});

test("avto rejim tizim mavzusiga ergashadi", async ({ page }) => {
  await page.emulateMedia({ colorScheme: "light" });
  await page.goto("/");
  await page.getByRole("radio", { name: "Avto" }).click();
  await expect(page.locator("html")).toHaveAttribute("data-theme", "light");

  await page.emulateMedia({ colorScheme: "dark" });
  await expect(page.locator("html")).toHaveAttribute("data-theme", "dark");
});

test("saqlangan mavzu birinchi bo‘yashdan oldin qoʻllanadi", async ({ page }) => {
  await page.addInitScript(() => localStorage.setItem("ut4_theme", "light"));
  await page.goto("/", { waitUntil: "commit" });
  await page.waitForFunction(() => document.documentElement.dataset.theme === "light");
});

test("shriftlar yuklanadi", async ({ page }) => {
  await page.goto("/");
  const families = await page.evaluate(() => ({
    body: getComputedStyle(document.body).fontFamily,
    title: getComputedStyle(document.querySelector("h2")!).fontFamily,
  }));

  expect(families.body).toContain("Inter");
  expect(families.title).toContain("Source Serif");
});
