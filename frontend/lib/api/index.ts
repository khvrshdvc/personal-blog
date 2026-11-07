import { cacheLife, cacheTag } from "next/cache";
import { source } from "./source";
import type { Category, PostsQuery } from "./types";

async function getPosts(query: PostsQuery = {}) {
  "use cache";
  cacheLife("hours");
  cacheTag("posts");

  return source.getPosts(query);
}

async function getPost(slug: string) {
  "use cache";
  cacheLife("hours");
  cacheTag("posts", `post:${slug}`);

  return source.getPost(slug);
}

async function getTags(category?: Category) {
  "use cache";
  cacheLife("hours");
  cacheTag("posts");

  return source.getTags(category);
}

async function search(q: string) {
  "use cache";
  cacheLife("minutes");
  cacheTag("posts", "projects");

  return source.search(q);
}

async function getProjects() {
  "use cache";
  cacheLife("hours");
  cacheTag("projects");

  return source.getProjects();
}

async function getSettings() {
  "use cache";
  cacheLife("hours");
  cacheTag("settings");

  return source.getSettings();
}

async function getCounts() {
  "use cache";
  cacheLife("hours");
  cacheTag("posts");

  return source.getCounts();
}

async function getActivity() {
  "use cache";
  cacheLife("minutes");
  cacheTag("settings");

  return source.getActivity();
}

async function getRuns() {
  "use cache";
  cacheLife("minutes");
  cacheTag("projects", "settings");

  return source.getRuns();
}

async function getRepoInfo(projectId: number) {
  "use cache";
  cacheLife("minutes");
  cacheTag("projects", "settings");

  return source.getRepoInfo(projectId);
}

async function getTelegram() {
  "use cache";
  cacheLife("minutes");
  cacheTag("settings");

  return source.getTelegram();
}

async function getTelegramFeed() {
  "use cache";
  cacheLife("minutes");
  cacheTag("settings");

  return source.getTelegramFeed();
}

export const api = {
  getPosts,
  getPost,
  getTags,
  search,
  getProjects,
  getSettings,
  getCounts,
  getActivity,
  getRuns,
  getRepoInfo,
  getTelegram,
  getTelegramFeed,
};

export { ApiError } from "./http";
export type * from "./types";
