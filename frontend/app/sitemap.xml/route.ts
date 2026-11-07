import { connection } from "next/server";
import { allPosts } from "@/lib/feed";
import { absoluteUrl, xml } from "@/lib/seo";

const PAGES = ["/", "/fikrlar", "/dasturlash", "/loyihalar", "/faollik", "/men"];

export async function GET() {
  await connection();

  let posts: Awaited<ReturnType<typeof allPosts>> = [];

  try {
    posts = await allPosts();
  } catch {}

  const urls = [
    ...PAGES.map((path) => `<url><loc>${xml(absoluteUrl(path))}</loc></url>`),
    ...posts.map(
      (post) =>
        `<url><loc>${xml(absoluteUrl(`/post/${post.slug}`))}</loc>${post.published_at ? `<lastmod>${post.published_at}</lastmod>` : ""}</url>`,
    ),
  ];

  const body = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls.join("\n")}\n</urlset>\n`;

  return new Response(body, { headers: { "Content-Type": "application/xml; charset=UTF-8" } });
}
