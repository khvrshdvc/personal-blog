"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useEffect } from "react";
import { PostsList } from "@/components/admin/posts-list";
import { ProjectsList } from "@/components/admin/projects-list";
import { useAdmin } from "@/components/admin/session";
import { ThemeButton } from "@/components/nav/theme-button";
import { Icon } from "@/components/icon";
import type { IconName } from "@/lib/icon-paths";

type Section = "dashboard" | "posts" | "projects" | "settings";

const NAV: { key: Section; icon: IconName; label: string; short: string; href: string }[] = [
  { key: "dashboard", icon: "grid", label: "Bosh sahifa", short: "Bosh", href: "/admin" },
  { key: "posts", icon: "doc", label: "Yozuvlar", short: "Yozuvlar", href: "/admin/posts" },
  { key: "projects", icon: "folder", label: "Loyihalar", short: "Loyihalar", href: "/admin/projects" },
  { key: "settings", icon: "gear", label: "Sozlamalar", short: "Sozlamalar", href: "/admin/settings" },
];

function sectionOf(pathname: string): Section {
  if (pathname.startsWith("/admin/posts")) return "posts";
  if (pathname.startsWith("/admin/projects")) return "projects";
  if (pathname.startsWith("/admin/settings")) return "settings";

  return "dashboard";
}

function activeId(pathname: string): number | null {
  const match = /^\/admin\/(?:posts|projects)\/(\d+)$/.exec(pathname);

  return match ? Number(match[1]) : null;
}

export function AdminShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const router = useRouter();
  const { status, posts, projects, logout } = useAdmin();
  const section = sectionOf(pathname);
  const isIndex = pathname === "/admin/posts" || pathname === "/admin/projects";
  const pane = isIndex ? "list" : "detail";
  const counts: Partial<Record<Section, number>> = { posts: posts?.length, projects: projects?.length };

  useEffect(() => {
    function onKey(event: KeyboardEvent) {
      if (event.key.toLowerCase() !== "n" || event.metaKey || event.ctrlKey || event.altKey) return;

      const target = event.target;

      if (target instanceof HTMLElement && (target.isContentEditable || ["INPUT", "TEXTAREA", "SELECT"].includes(target.tagName))) return;

      event.preventDefault();
      router.push("/admin/posts/new");
    }

    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, [router]);

  if (status !== "authed") {
    return <div className="adm" data-section={section} data-pane="detail" aria-busy="true" />;
  }

  return (
    <div className="adm" data-section={section} data-pane={pane}>
      <aside className="a-side" aria-label="Admin menyu">
        <Link className="c as-me" href="/admin">
          <span className="av">U</span>
          <span>
            <b>Utkirbek</b>
            <small>Admin panel</small>
          </span>
        </Link>
        <Link className="as-new" href="/admin/posts/new">
          <Icon name="plus" />
          <span>Yangi yozuv</span>
          <kbd>N</kbd>
        </Link>
        <nav className="as-nav" aria-label="Boʻlimlar">
          {NAV.map((item) => (
            <Link
              key={item.key}
              className={`c as-t${section === item.key ? " on" : ""}`}
              href={item.href}
              aria-current={section === item.key ? "page" : undefined}
            >
              <Icon name={item.icon} />
              <span>{item.label}</span>
              {counts[item.key] !== undefined ? <span className="as-c">{counts[item.key]}</span> : null}
            </Link>
          ))}
        </nav>
        <span className="as-sp" />
        <div className="as-x">
          <Link className="c as-b" href="/">
            <Icon name="eye" />
            <span>Sayt</span>
          </Link>
          <ThemeButton className="c as-b">
            <span>Mavzu</span>
          </ThemeButton>
          <button className="c as-b" type="button" onClick={logout}>
            <Icon name="logout" />
            <span>Chiqish</span>
          </button>
        </div>
      </aside>
      {section === "posts" ? <PostsList activeId={activeId(pathname)} /> : null}
      {section === "projects" ? <ProjectsList activeId={activeId(pathname)} /> : null}
      <section className="a-detail" id="adetail">
        {children}
      </section>
      <nav className="abar" aria-label="Admin boʻlimlari">
        {NAV.map((item) => (
          <Link key={item.key} className={`ab${section === item.key ? " on" : ""}`} href={item.href}>
            <Icon name={item.icon} />
            <span>{item.short}</span>
          </Link>
        ))}
        <Link className="ab" href="/">
          <Icon name="eye" />
          <span>Sayt</span>
        </Link>
      </nav>
    </div>
  );
}
