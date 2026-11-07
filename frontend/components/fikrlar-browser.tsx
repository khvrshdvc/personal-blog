"use client";

import { useEffect, useState } from "react";
import { Icon } from "@/components/icon";
import { PostCard } from "@/components/post-card";
import type { PostListItem } from "@/lib/api";

export function FikrlarBrowser({ posts, tags }: { posts: PostListItem[]; tags: string[] }) {
  const [tag, setTag] = useState("all");
  const [query, setQuery] = useState("");
  const [matched, setMatched] = useState<Set<string> | null>(null);

  useEffect(() => {
    const text = query.trim();

    if (text === "") return;

    const controller = new AbortController();
    const timer = setTimeout(async () => {
      try {
        const response = await fetch(`/api/posts?category=fikr&q=${encodeURIComponent(text)}`, { signal: controller.signal });

        if (response.ok) {
          setMatched(new Set((await response.json()) as string[]));
        }
      } catch {}
    }, 200);

    return () => {
      clearTimeout(timer);
      controller.abort();
    };
  }, [query]);

  const searching = query.trim() !== "";
  const visible = posts.filter(
    (post) => (tag === "all" || post.tag === tag) && (!searching || (matched?.has(post.slug) ?? false)),
  );

  return (
    <>
      <div className="tools">
        <div className="ftabs" role="group" aria-label="Mavzular">
          {["all", ...tags].map((item) => (
            <button key={item} className="scope" type="button" aria-pressed={tag === item} onClick={() => setTag(item)}>
              {item === "all" ? "Barchasi" : item}
            </button>
          ))}
        </div>
        <label className="fs">
          <Icon name="search" />
          <span className="sr">Fikrlardan qidirish</span>
          <input
            type="search"
            placeholder="Qidirish"
            autoComplete="off"
            enterKeyHint="search"
            value={query}
            onChange={(event) => {
              setQuery(event.target.value);
              if (event.target.value.trim() === "") setMatched(null);
            }}
          />
          {query ? (
            <button className="fx" type="button" aria-label="Qidiruvni tozalash" onClick={() => {
              setQuery("");
              setMatched(null);
            }}>
              <Icon name="x" />
            </button>
          ) : null}
        </label>
      </div>
      <div className="pgrid">
        {posts.length === 0 ? <p className="empty">Hali fikr yozilmagan.</p> : null}
        {posts.length > 0 && visible.length === 0 ? (
          <p className="empty">Hech narsa topilmadi. Boshqa soʻz bilan qidirib koʻring.</p>
        ) : null}
        {visible.map((post, index) => (
          <PostCard key={post.id} post={post} big={index === 0 && tag === "all" && !searching} />
        ))}
      </div>
    </>
  );
}
