import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { PostView } from "@/components/post/post-view";
import { api } from "@/lib/api";
import { SITE_NAME, absoluteUrl, jsonLd, pageAlternates } from "@/lib/seo";

export async function generateMetadata({ params }: PageProps<"/post/[slug]">): Promise<Metadata> {
  const { slug } = await params;
  const post = await api.getPost(slug).catch(() => null);

  if (!post) {
    return { title: "Topilmadi — Utkirbek", robots: { index: false, follow: false } };
  }

  const title = `${post.title} — ${SITE_NAME}`;
  const description = post.excerpt || `${SITE_NAME} — ${post.title}`;
  const url = `/post/${post.slug}`;

  return {
    title,
    description,
    alternates: pageAlternates(url),
    openGraph: {
      siteName: SITE_NAME,
      locale: "uz_UZ",
      type: "article",
      title,
      description,
      url,
      publishedTime: post.published_at ?? undefined,
      images: post.cover ? [{ url: post.cover }] : undefined,
    },
    twitter: {
      card: post.cover ? "summary_large_image" : "summary",
      title,
      description,
      images: post.cover ? [post.cover] : undefined,
    },
  };
}

export const instant = false;

export default async function PostPage({ params }: PageProps<"/post/[slug]">) {
  const { slug } = await params;
  const post = await api.getPost(slug);

  if (!post) notFound();

  const data = {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    headline: post.title,
    description: post.excerpt ?? undefined,
    datePublished: post.published_at ?? undefined,
    image: post.cover ?? undefined,
    mainEntityOfPage: absoluteUrl(`/post/${post.slug}`),
    author: { "@type": "Person", name: SITE_NAME },
    inLanguage: "uz",
  };

  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd(data) }} />
      <PostView post={post} />
    </>
  );
}
