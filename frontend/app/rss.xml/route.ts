import { connection } from "next/server";
import { api } from "@/lib/api";
import type { PostListItem } from "@/lib/api";
import { SITE_DESCRIPTION, SITE_NAME, absoluteUrl, xml } from "@/lib/seo";

function rfc822(iso: string | null): string {
  return new Date(`${iso ?? "1970-01-01"}T00:00:00Z`).toUTCString();
}

export async function GET() {
  await connection();

  let posts: PostListItem[] = [];

  try {
    posts = (await api.getPosts({ per_page: 20 })).data;
  } catch {}

  const items = posts
    .map((post) => {
      const link = xml(absoluteUrl(`/post/${post.slug}`));

      return `<item><title>${xml(post.title)}</title><link>${link}</link><guid isPermaLink="true">${link}</guid><pubDate>${rfc822(post.published_at)}</pubDate><category>${post.category === "dev" ? "Dasturlash" : "Fikrlar"}</category><description>${xml(post.excerpt ?? "")}</description></item>`;
    })
    .join("\n");

  const updated = posts[0] ? `<lastBuildDate>${rfc822(posts[0].published_at)}</lastBuildDate>` : "";
  const body = `<?xml version="1.0" encoding="UTF-8"?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>${xml(SITE_NAME)}</title><link>${xml(absoluteUrl("/"))}</link><description>${xml(SITE_DESCRIPTION)}</description><language>uz</language><atom:link href="${xml(absoluteUrl("/rss.xml"))}" rel="self" type="application/rss+xml"/>${updated}\n${items}\n</channel></rss>\n`;

  return new Response(body, { headers: { "Content-Type": "application/rss+xml; charset=UTF-8" } });
}
