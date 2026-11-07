import { Suspense } from "react";
import { connection } from "next/server";
import { ThemeToggle } from "@/components/theme-toggle";
import { api } from "@/lib/api";
import type { PostListItem } from "@/lib/api";

async function loadPosts(): Promise<PostListItem[] | null> {
  try {
    return (await api.getPosts({ per_page: 3 })).data;
  } catch {
    return null;
  }
}

async function Posts() {
  await connection();
  const posts = await loadPosts();

  if (posts === null) {
    return (
      <div className="c" role="alert">
        <div className="ck">API</div>
        <p className="mt-2 text-t2">API bilan aloqa yoʻq. Backend ishlayotganini tekshiring.</p>
      </div>
    );
  }

  return (
    <div className="grid gap-3 min-[901px]:grid-cols-3">
      {posts.map((post) => (
        <article key={post.id} className="c" data-testid="post-card">
          <div className="ck">{post.tag}</div>
          <h2 className="mt-3 font-serif text-[22px] leading-[1.2] font-semibold">{post.title}</h2>
          <p className="mt-2 text-[15px] text-t2">{post.excerpt}</p>
          <p className="mt-4 text-[13px] text-t3">{post.read_minutes} daqiqa</p>
        </article>
      ))}
    </div>
  );
}

function PostsSkeleton() {
  return (
    <div className="grid gap-3 min-[901px]:grid-cols-3" aria-hidden="true">
      {[0, 1, 2].map((item) => (
        <div key={item} className="c h-[170px]" />
      ))}
    </div>
  );
}

export default function Home() {
  return (
    <main className="wrapx py-12">
      <div className="flex items-center justify-between gap-4">
        <span className="font-mono text-[13px] text-t3">utkirbek.uz</span>
        <ThemeToggle />
      </div>

      <section className="mt-12 mb-8">
        <h1 className="max-w-[1000px] text-[clamp(40px,4.6vw,58px)] max-[760px]:text-[34px] leading-[1.03] font-semibold tracking-[-0.045em]">
          Salom, men Utkirbek. <span className="text-t3">Oʻrganaman va yozaman.</span>
        </h1>
        <p className="mt-4 max-w-[560px] text-[16px] leading-[1.55] text-t2">
          Fikrlar, ustozlik va til haqida. Dasturlash alohida boʻlimda.
        </p>
      </section>

      <Suspense fallback={<PostsSkeleton />}>
        <Posts />
      </Suspense>
    </main>
  );
}
