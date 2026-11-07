import type { Metadata } from "next";
import { Suspense } from "react";
import { connection } from "next/server";
import { FikrlarBrowser } from "@/components/fikrlar-browser";
import { api } from "@/lib/api";
import type { PostListItem } from "@/lib/api";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Fikrlar — Utkirbek",
  alternates: pageAlternates("/fikrlar"),
};

const FIKR_TAGS = ["Hayot", "Oʻsish", "Ustozlik", "Til"];

async function Content() {
  await connection();

  let posts: PostListItem[] | null = null;

  try {
    posts = (await api.getPosts({ category: "fikr", per_page: 50 })).data;
  } catch {
    posts = null;
  }

  if (posts === null) {
    return <p className="empty">Yozuvlarni yuklab boʻlmadi. Birozdan keyin qayta urinib koʻring.</p>;
  }

  const present = new Set(posts.map((post) => post.tag));

  return (
    <>
      <header className="ph">
        <p className="eyebrow">Fikrlar</p>
        <h1>
          Oʻrganish, ustozlik, til va hayot haqida. <span>{posts.length} ta yozuv.</span>
        </h1>
      </header>
      <FikrlarBrowser posts={posts} tags={FIKR_TAGS.filter((tag) => present.has(tag))} />
    </>
  );
}

export default function FikrlarPage() {
  return (
    <div className="wrapx">
      <Suspense fallback={<header className="ph"><p className="eyebrow">Fikrlar</p></header>}>
        <Content />
      </Suspense>
    </div>
  );
}
