import { Suspense } from "react";
import { connection } from "next/server";
import { DevShell } from "@/components/dev/dev-shell";
import { PostCard } from "@/components/post-card";
import { api } from "@/lib/api";
import type { PostListItem } from "@/lib/api";

async function Posts() {
  await connection();

  let posts: PostListItem[] | null = null;

  try {
    posts = (await api.getPosts({ category: "dev", per_page: 50 })).data;
  } catch {
    posts = null;
  }

  if (posts === null) {
    return <p className="empty">Yozuvlarni yuklab boʻlmadi. Birozdan keyin qayta urinib koʻring.</p>;
  }

  return (
    <div className="pgrid">
      {posts.length ? posts.map((post) => <PostCard key={post.id} post={post} />) : <p className="empty">Hali yozuv yoʻq.</p>}
    </div>
  );
}

export function YozuvlarTab() {
  return (
    <DevShell tab="yozuvlar">
      <Suspense fallback={<div className="pgrid" aria-hidden="true" />}>
        <Posts />
      </Suspense>
    </DevShell>
  );
}
