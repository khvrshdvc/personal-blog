"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { Icon } from "@/components/icon";
import { ThemeButton } from "@/components/nav/theme-button";
import { usePalette } from "@/components/nav/palette-context";
import type { IconName } from "@/lib/icon-paths";
import { useNavSection } from "@/lib/nav-section";

export interface NavData {
  counts: { fikr: number; dev: number } | null;
  open: boolean | null;
}

type SectionKey = "home" | "fikrlar" | "dasturlash" | "men";

const SECTIONS: { key: SectionKey; href: string; label: string; icon: IconName; count?: "fikr" | "dev" }[] = [
  { key: "home", href: "/", label: "Asosiy", icon: "grid" },
  { key: "fikrlar", href: "/fikrlar", label: "Fikrlar", icon: "bookOpen", count: "fikr" },
  { key: "dasturlash", href: "/dasturlash", label: "Dasturlash", icon: "code", count: "dev" },
  { key: "men", href: "/men", label: "Men haqimda", icon: "person" },
];

export function sectionOf(pathname: string): SectionKey | null {
  if (pathname === "/") return "home";
  if (pathname.startsWith("/fikrlar")) return "fikrlar";
  if (["/dasturlash", "/yozuvlar", "/loyihalar", "/faollik"].some((path) => pathname.startsWith(path))) return "dasturlash";
  if (pathname.startsWith("/men")) return "men";

  return null;
}

interface NavViewProps extends NavData {
  active: SectionKey | null;
  mini: boolean;
  onSearch?: () => void;
  navRef?: React.Ref<HTMLElement>;
}

function NavView({ counts, open, active, mini, onSearch, navRef }: NavViewProps) {
  return (
    <>
      <nav ref={navRef} className="tnav" aria-label="Boʻlimlar">
        <div className="c tn-me">
          <Link className="tn-h" href="/" aria-label="Utkirbek — bosh sahifa">
            <span className="av">U</span>
            <span>
              <b>Utkirbek</b>
              <small>
                <span className={`tn-st${open === false ? " off" : ""}`}>
                  <i />
                  <span>{open === false ? "Band" : "Ishga ochiq"}</span>
                </span>
                {" · Toshkent"}
              </small>
            </span>
          </Link>
          <button className="tn-ib" type="button" onClick={onSearch} aria-label="Qidirish">
            <Icon name="search" />
          </button>
          <ThemeButton className="tn-ib" />
          <Link className="tn-ib" href="/admin" aria-label="Admin panel">
            <Icon name="lock" />
          </Link>
        </div>
        {SECTIONS.map((section) => (
          <Link
            key={section.key}
            href={section.href}
            className={`c tn${active === section.key ? " on" : ""}`}
            aria-current={active === section.key ? "page" : undefined}
          >
            <span className="tn-t">
              <Icon name={section.icon} />
              <span>{section.count && counts ? counts[section.count] : ""}</span>
            </span>
            <b>{section.label}</b>
          </Link>
        ))}
        <button className="c tn-x" type="button" onClick={onSearch} aria-label="Qidirish (Ctrl+K)">
          <Icon name="search" />
          <small>⌘K</small>
        </button>
        <ThemeButton className="c tn-x">
          <small>Mavzu</small>
        </ThemeButton>
        <Link className="c tn-x" href="/admin" aria-label="Admin panel">
          <Icon name="lock" />
          <small>Admin</small>
        </Link>
      </nav>

      <nav className={`tmini${mini ? " show" : ""}`} aria-label="Boʻlimlar (ixcham)" inert={!mini}>
        <Link className="tmi-h" href="/" aria-label="Bosh sahifa">
          <span className="av">U</span>
        </Link>
        {SECTIONS.map((section) => (
          <Link
            key={section.key}
            href={section.href}
            className={`tmi${active === section.key ? " on" : ""}`}
            aria-label={section.label}
            aria-current={active === section.key ? "page" : undefined}
          >
            <Icon name={section.icon} size={16} />
            <span>{section.label}</span>
          </Link>
        ))}
        <span className="tmi-sep" aria-hidden="true" />
        <button className="tmi ic" type="button" onClick={onSearch} aria-label="Qidirish">
          <Icon name="search" size={16} />
        </button>
        <ThemeButton className="tmi ic th" />
        <Link className="tmi ic" href="/admin" aria-label="Admin panel">
          <Icon name="lock" size={16} />
        </Link>
      </nav>
    </>
  );
}

export function NavFallback() {
  return <NavView counts={null} open={null} active={null} mini={false} />;
}

export function SiteNav({ counts, open }: NavData) {
  const pathname = usePathname();
  const palette = usePalette();
  const navRef = useRef<HTMLElement>(null);
  const [mini, setMini] = useState(false);
  const override = useNavSection();
  const active = override ?? sectionOf(pathname);
  const isPost = pathname.startsWith("/post/");

  useEffect(() => {
    let lastY = window.scrollY;
    let direction: "up" | "down" | "" = "";

    function onScroll() {
      const nav = navRef.current;
      if (!nav) return;

      const y = window.scrollY;
      const max = document.documentElement.scrollHeight - window.innerHeight;
      const progress = max > 0 ? Math.min(1, Math.max(0, y / max)) : 0;
      const out = nav.getBoundingClientRect().bottom < -6;
      let show = out;

      if (out && isPost && progress < 0.98) {
        if (Math.abs(y - lastY) > 10) {
          direction = y > lastY ? "down" : "up";
          lastY = y;
        }
        show = direction !== "down";
      } else {
        lastY = y;
      }

      setMini(show);
    }

    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    return () => window.removeEventListener("scroll", onScroll);
  }, [isPost, pathname]);

  return <NavView counts={counts} open={open} active={active} mini={mini} onSearch={palette.open} navRef={navRef} />;
}
