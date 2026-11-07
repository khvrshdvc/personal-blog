export type Category = "fikr" | "dev";

export interface PostListItem {
  id: number;
  category: Category;
  tag: string | null;
  title: string;
  slug: string;
  excerpt: string | null;
  cover: string | null;
  published_at: string | null;
  read_minutes: number;
}

export interface TocItem {
  id: string;
  text: string;
  level: 2 | 3;
}

export interface PostDetail extends PostListItem {
  body_html: string;
  toc: TocItem[];
  prev: PostListItem | null;
  next: PostListItem | null;
}

export interface PageMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface Paginated<T> {
  data: T[];
  meta: PageMeta;
}

export interface PostsQuery {
  category?: Category;
  tag?: string;
  q?: string;
  page?: number;
  per_page?: number;
}

export interface TagCount {
  tag: string;
  count: number;
}

export interface SearchItem {
  type: "post" | "project";
  title: string;
  subtitle: string;
  path: string;
}

export interface Project {
  id: number;
  name: string;
  repo: string | null;
  description: string | null;
  stack: string[];
  demo_url: string | null;
}

export interface Settings {
  github: string | null;
  open: boolean | null;
  bio: string | null;
  now: { k: string; v: string }[] | null;
  learn: { n: string; p: number }[] | null;
  telegram: { u: string; n: string; posts: { t: string; d: string }[] } | null;
}

export interface Api {
  getPosts(query?: PostsQuery): Promise<Paginated<PostListItem>>;
  getPost(slug: string): Promise<PostDetail | null>;
  getTags(category?: Category): Promise<TagCount[]>;
  search(q: string): Promise<SearchItem[]>;
  getProjects(): Promise<Project[]>;
  getSettings(): Promise<Settings>;
}
