"use client";

import { useState } from "react";
import { Icon } from "@/components/icon";
import { fmtLong } from "@/lib/format";
import type { TelegramFeed } from "@/lib/api";

export function TelegramFeedList({ posts }: { posts: TelegramFeed["posts"] }) {
  const [query, setQuery] = useState("");
  const needle = query.trim().toLowerCase();
  const visible = needle === "" ? posts : posts.filter((post) => post.t.toLowerCase().includes(needle));

  return (
    <>
      <div className="tools">
        <label className="fs">
          <Icon name="search" />
          <span className="sr">Fikrlardan qidirish</span>
          <input
            type="search"
            placeholder="Qidirish"
            autoComplete="off"
            enterKeyHint="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
          />
          {query ? (
            <button className="fx" type="button" aria-label="Qidiruvni tozalash" onClick={() => setQuery("")}>
              <Icon name="x" />
            </button>
          ) : null}
        </label>
      </div>
      <div className="pgrid">
        {posts.length === 0 ? <p className="empty">Kanalda hali fikr yoʻq.</p> : null}
        {posts.length > 0 && visible.length === 0 ? (
          <p className="empty">Hech narsa topilmadi. Boshqa soʻz bilan qidirib koʻring.</p>
        ) : null}
        {visible.map((post, index) => {
          const big = index === 0 && needle === "";

          return (
            <a key={post.id} className={`c pc${big ? " big" : ""}`} href={post.url} target="_blank" rel="noopener noreferrer">
              {big ? (
                <span className="qm" aria-hidden="true">
                  “
                </span>
              ) : null}
              <span className="ck">
                {fmtLong(post.d)}
                <span className="r">Telegram</span>
              </span>
              <p className="tg">{post.t}</p>
            </a>
          );
        })}
      </div>
    </>
  );
}
