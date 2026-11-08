import type { Metadata } from "next";
import { Suspense } from "react";
import { connection } from "next/server";
import { TelegramFeedList } from "@/components/telegram-feed";
import { api } from "@/lib/api";
import type { TelegramFeed } from "@/lib/api";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Fikrlar — Utkirbek",
  alternates: pageAlternates("/fikrlar"),
};

async function Content() {
  await connection();

  let feed: TelegramFeed | null = null;

  try {
    feed = await api.getTelegramFeed();
  } catch {
    feed = null;
  }

  if (feed === null) {
    return <p className="empty">Fikrlarni yuklab boʻlmadi. Birozdan keyin qayta urinib koʻring.</p>;
  }

  return (
    <>
      <header className="ph">
        <p className="eyebrow">Fikrlar</p>
        <h1>
          Telegram kanalimdan so&apos;nggi fikrlar. <span>{feed.posts.length} ta yozuv.</span>
        </h1>
      </header>
      <TelegramFeedList posts={feed.posts} channel={feed.u} />
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
