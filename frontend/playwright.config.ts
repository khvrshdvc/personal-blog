import { defineConfig } from "@playwright/test";

const port = 3100;

export default defineConfig({
  testDir: "./e2e",
  timeout: 30000,
  use: { baseURL: `http://localhost:${port}` },
  projects: [
    { name: "telefon", use: { viewport: { width: 390, height: 844 } } },
    { name: "ixcham", use: { viewport: { width: 1024, height: 768 } } },
    { name: "toliq", use: { viewport: { width: 1440, height: 900 } } },
  ],
  webServer: {
    command: `npm run build && npm run start -- -p ${port}`,
    url: `http://localhost:${port}`,
    reuseExistingServer: false,
    timeout: 240000,
    env: { API_MOCK: "1" },
  },
});
