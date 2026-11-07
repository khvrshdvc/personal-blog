import { httpApi } from "./http";
import { mockApi } from "./mock";
import type { Api } from "./types";

export const source: Api = process.env.API_MOCK === "1" ? mockApi : httpApi;
