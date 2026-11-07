import Link from "next/link";
import { Icon } from "@/components/icon";

export function PostsEmptyPane() {
  return (
    <div className="a-empty">
      <div className="c ae-c">
        <span className="ae-i">
          <Icon name="compose" />
        </span>
        <h2>Bugun nima haqida yozasiz?</h2>
        <p>Chapdan yozuvni tanlang yoki yangisini boshlang. Yozganlaringiz avtomatik saqlanadi.</p>
        <div className="d-acts">
          <Link className="dbtn pri" href="/admin/posts/new/fikr">
            <Icon name="plus" />
            Yangi fikr
          </Link>
          <Link className="dbtn" href="/admin/posts/new/dev">
            <Icon name="code" />
            Dasturlash posti
          </Link>
        </div>
        <p className="ae-k">
          <span>
            <kbd>N</kbd> yangi yozuv
          </span>
          <span>
            <kbd>Ctrl</kbd>
            <kbd>S</kbd> saqlash
          </span>
          <span>
            <kbd>Ctrl</kbd>
            <kbd>↵</kbd> chop etish
          </span>
        </p>
      </div>
    </div>
  );
}

export function ProjectsEmptyPane() {
  return (
    <div className="a-empty">
      <div className="c ae-c">
        <span className="ae-i">
          <Icon name="folder" />
        </span>
        <h2>Loyihalaringiz</h2>
        <p>Chapdan loyihani tanlang yoki yangisini qoʻshing. Saytda &quot;Dasturlash → Loyihalar&quot; boʻlimida koʻrinadi.</p>
        <div className="d-acts">
          <Link className="dbtn pri" href="/admin/projects/new">
            <Icon name="plus" />
            Yangi loyiha
          </Link>
        </div>
      </div>
    </div>
  );
}
