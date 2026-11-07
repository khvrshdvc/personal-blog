import Image from "next/image";
import Link from "next/link";
import { Icon } from "@/components/icon";
import { Prose } from "@/components/post/prose";
import { ReadProgress } from "@/components/post/read-progress";
import { SetNavSection } from "@/components/post/set-nav-section";
import { ShareButton } from "@/components/post/share-button";
import type { PostDetail, PostListItem } from "@/lib/api";
import { fmtLong, fmtShort } from "@/lib/format";
import { enhanceCodeBlocks } from "@/lib/post-html";

function Neighbour({ label, post }: { label: string; post: PostListItem }) {
  return (
    <Link className={`c r-nx${post.category === "dev" ? " dev" : ""}`} href={`/post/${post.slug}`}>
      <span className="ck">
        {label}
        <span className="r">{fmtShort(post.published_at)}</span>
      </span>
      <h4>{post.title}</h4>
    </Link>
  );
}

export function PostView({ post }: { post: PostDetail }) {
  const isDev = post.category === "dev";
  const section = isDev ? "dasturlash" : "fikrlar";

  return (
    <>
      <SetNavSection section={section} />
      <ReadProgress />
      <article className={`read ${isDev ? "dev" : "fikr"}`}>
        <div className="r-top">
          <Link className="tlink" href={`/${section}`}>
            <Icon name="chevL" /> {isDev ? "Dasturlash" : "Fikrlar"}
          </Link>
          <ShareButton title={post.title} />
        </div>
        <div className="r-meta">
          <span>{post.tag}</span>
          <span>·</span>
          <span>{fmtLong(post.published_at)}</span>
          <span>·</span>
          <span>{post.read_minutes} daqiqa oʻqish</span>
        </div>
        <h1 className="r-title">{post.title}</h1>
        {post.excerpt ? <p className="r-lead">{post.excerpt}</p> : null}
        {post.cover ? (
          <div className="r-cover">
            <div className="cover" style={{ position: "relative" }}>
              <Image src={post.cover} alt="" fill unoptimized sizes="(max-width: 900px) 100vw, 760px" style={{ objectFit: "cover" }} />
            </div>
          </div>
        ) : null}
        <div className="r-body">
          {isDev && post.toc.length >= 2 ? (
            <details className="disc">
              <summary>
                <span>Mundarija</span>
                <span className="rd">{post.toc.length} boʻlim</span>
                <span className="chev">
                  <Icon name="chevR" />
                </span>
              </summary>
              <ol>
                {post.toc.map((item) => (
                  <li key={item.id}>
                    <a href={`#${item.id}`} className={item.level === 3 ? "lv3" : undefined}>
                      {item.text}
                    </a>
                  </li>
                ))}
              </ol>
            </details>
          ) : null}
          <Prose html={enhanceCodeBlocks(post.body_html)} />
        </div>
        <div className="r-end">
          <Link className="c r-au" href="/men">
            <span className="av">U</span>
            <span className="r-au-t">
              <b>Utkirbek</b>
              <small>Backend dasturchi · Toshkent</small>
            </span>
            <span className="arr sm">
              <Icon name="arrowUR" />
            </span>
          </Link>
          {post.next ? <Neighbour label="Keyingi" post={post.next} /> : null}
          {post.prev ? <Neighbour label="Oldingi" post={post.prev} /> : null}
        </div>
      </article>
    </>
  );
}
