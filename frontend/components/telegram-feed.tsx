"use client";

import { Fragment, useState } from "react";
import { Icon } from "@/components/icon";
import { fmtLong } from "@/lib/format";
import type { TelegramFeed } from "@/lib/api";

const URL_PATTERN = /(https?:\/\/[^\s<]+[^\s<.,;:!?)"'»])/g;

function Linked({ text }: { text: string }) {
  return (
    <>
      {text.split(URL_PATTERN).map((part, index) =>
        index % 2 === 1 ? (
          <a key={index} href={part} target="_blank" rel="noopener noreferrer nofollow">
            {part}
          </a>
        ) : (
          <Fragment key={index}>{part}</Fragment>
        ),
      )}
    </>
  );
}

export function TelegramFeedList({ posts, channel }: { posts: TelegramFeed["posts"]; channel: string }) {
  const [query, setQuery] = useState("");
  const needle = query.trim().toLowerCase();
  const visible = needle === "" ? posts : posts.filter((post) => post.t.toLowerCase().includes(needle));

  return (
    <div className="tgw">
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
        {channel ? (
          <a className="tgo" href={`https://t.me/${channel}`} target="_blank" rel="noopener noreferrer">
            <Icon name="send" />
            Kanalga oʻtish
          </a>
        ) : null}
      </div>
      <div className="tgl">
        {posts.length === 0 ? <p className="empty">Kanalda hali fikr yoʻq.</p> : null}
        {posts.length > 0 && visible.length === 0 ? (
          <p className="empty">Hech narsa topilmadi. Boshqa soʻz bilan qidirib koʻring.</p>
        ) : null}
        {visible.map((post) => (
          <article key={post.id} className="c tgp">
            <header className="tgh">
              <span className="tgd">{fmtLong(post.d)}</span>
              <a href={post.url} target="_blank" rel="noopener noreferrer" aria-label="Telegramda ochish">
                Telegramda ochish
                <Icon name="arrowUR" />
              </a>
            </header>
            <div className="tgb">
              <Linked text={post.t} />
            </div>
          </article>
        ))}
      </div>
    </div>
  );
}
