import { api } from "@/lib/api";
import type { PostListItem } from "@/lib/api";

export async function allPosts(): Promise<PostListItem[]> {
  const posts: PostListItem[] = [];
  let page = 1;
  let last = 1;

  do {
    const result = await api.getPosts({ page, per_page: 50 });
    posts.push(...result.data);
    last = result.meta.last_page;
    page++;
  } while (page <= last && page <= 20);

  return posts;
}
