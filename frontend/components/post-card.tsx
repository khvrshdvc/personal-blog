import Link from "next/link";
import type { PostListItem } from "@/lib/api";
import { fmtLong } from "@/lib/format";

export function PostCard({ post, big = false }: { post: PostListItem; big?: boolean }) {
  const classes = ["c", "pc", big ? "big" : "", post.category === "dev" ? "dev" : ""].filter(Boolean).join(" ");

  return (
    <Link className={classes} href={`/post/${post.slug}`}>
      {big ? (
        <span className="qm" aria-hidden="true">
          “
        </span>
      ) : null}
      <span className="ck">
        {post.tag} · {fmtLong(post.published_at)}
        <span className="r">{post.read_minutes} daqiqa</span>
      </span>
      <h3>{post.title}</h3>
      {post.excerpt ? <p>{post.excerpt}</p> : null}
    </Link>
  );
}
