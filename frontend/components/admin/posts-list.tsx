"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { ListPane, MinusIcon } from "@/components/admin/list-pane";
import { useAdmin } from "@/components/admin/session";
import { useNotify } from "@/components/toast";
import { useRouter } from "next/navigation";
import { adminApi } from "@/lib/admin/api";
import { STATE_CLASS, STATE_LABEL, autoExcerpt, postState, postView } from "@/lib/admin/post-model";
import { fmtShort } from "@/lib/format";

export function PostsList({ activeId }: { activeId: number | null }) {
  const { posts, removePost } = useAdmin();
  const notify = useNotify();
  const router = useRouter();
  const [query, setQuery] = useState("");

  const visible = useMemo(() => {
    const needle = query.trim().toLowerCase();

    return (posts ?? []).filter((post) => !needle || postView(post).title.toLowerCase().includes(needle));
  }, [posts, query]);

  async function remove(id: number) {
    try {
      await adminApi.deletePost(id);
    } catch {
      notify("Oʻchirib boʻlmadi");

      return;
    }

    removePost(id);
    notify("Yozuv oʻchirildi");

    if (activeId === id) router.replace("/admin/posts");
  }

  return (
    <ListPane
      title="Yozuvlar"
      count={posts?.length ?? null}
      newHref="/admin/posts/new"
      newLabel="Yangi yozuv"
      search={{ value: query, onChange: setQuery, label: "Yozuvlarni qidirish" }}
    >
      {({ editing, revealed, reveal }) => (
        <>
          {visible.map((post) => {
            const view = postView(post);
            const state = postState(post);
            const active = post.id === activeId;

            return (
              <li key={post.id} className={`ali${revealed === post.id ? " reveal" : ""}`} data-active={active ? "1" : undefined}>
                <button
                  className="minus"
                  type="button"
                  aria-label={`${view.title || "Sarlavhasiz"} — oʻchirish`}
                  onClick={() => reveal(revealed === post.id ? null : post.id)}
                >
                  <MinusIcon />
                </button>
                <Link className={`arow${active ? " on" : ""}`} href={`/admin/posts/${post.id}`} aria-current={active ? "true" : undefined}>
                  <span className="ar-top">
                    <span className={`sdot ${STATE_CLASS[state]}`} aria-hidden="true" />
                    <b className="rt">{view.title || "Sarlavhasiz"}</b>
                    <span className="rd">{fmtShort(view.published_at)}</span>
                  </span>
                  <span className="rs2">{view.excerpt || autoExcerpt(view.body) || "Matn yoʻq"}</span>
                  <span className="ar-st">
                    {view.category === "dev" ? "Dasturlash" : "Fikr"} · {view.tag} · {STATE_LABEL[state]}
                  </span>
                </Link>
                <button className="delbtn" type="button" onClick={() => remove(post.id)} tabIndex={editing ? 0 : -1}>
                  Oʻchirish
                </button>
              </li>
            );
          })}
          {posts !== null && visible.length === 0 ? <li className="a-empty-l">Yozuv topilmadi</li> : null}
        </>
      )}
    </ListPane>
  );
}
