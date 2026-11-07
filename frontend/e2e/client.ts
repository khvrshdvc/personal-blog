import type { Page } from "@playwright/test";

export function randomIp(): string {
  return `10.${Math.floor(Math.random() * 250)}.${Math.floor(Math.random() * 250)}.${Math.floor(Math.random() * 250)}`;
}

export async function ownClient(page: Page): Promise<void> {
  await page.context().setExtraHTTPHeaders({ "x-forwarded-for": randomIp() });
}
