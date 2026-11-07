import Link from "next/link";
import type { Metadata } from "next";
import { Suspense } from "react";
import { connection } from "next/server";
import { CardTitle } from "@/components/card-title";
import { Icon } from "@/components/icon";
import { NotifyLink } from "@/components/notify-link";
import { ThemeSeg } from "@/components/theme-seg";
import { api } from "@/lib/api";
import type { Settings } from "@/lib/api";

export const metadata: Metadata = {
  title: "Men haqimda — Utkirbek",
};

const SETTINGS_ACTION = { label: "Sozlamalar", href: "/admin/settings" };

async function load() {
  const [settings, projects, counts] = await Promise.all([
    api.getSettings().catch(() => null as Settings | null),
    api.getProjects().catch(() => null),
    api.getCounts().catch(() => null),
  ]);

  return { settings, projectCount: projects?.length ?? 0, thoughtCount: counts?.fikr ?? 0 };
}

async function Content() {
  await connection();
  const { settings, projectCount, thoughtCount } = await load();
  const isOpen = settings?.open ?? true;
  const github = settings?.github?.trim() || "";
  const telegram = settings?.telegram?.u?.trim() || "";
  const now = (settings?.now ?? []).filter((item) => item.k.trim() !== "" || item.v.trim() !== "");
  const paragraphs = String(settings?.bio ?? "")
    .split(/\n\s*\n/)
    .map((text) => text.trim())
    .filter(Boolean);
  const todo = (name: string) => `${name} havolasi hali qoʻshilmagan`;

  return (
    <div className="mgrid">
      <section className="c m-pro">
        <div className="m-top">
          <span className="av" aria-hidden="true">
            U
          </span>
          <span className={`status${isOpen ? "" : " off"}`}>
            <i />
            <span>{isOpen ? "Ishga ochiq" : "Hozircha band"}</span>
          </span>
        </div>
        <h1>Utkirbek</h1>
        <p>Backend dasturchi · Toshkent, Oʻzbekiston</p>
        <div className="acts">
          <NotifyLink className="act" text={todo("Telegram")}>
            <Icon name="send" />
            Telegram
          </NotifyLink>
          {telegram ? (
            <a className="act" href={`https://t.me/${telegram}`} target="_blank" rel="noopener noreferrer">
              <Icon name="mega" />
              Kanal
            </a>
          ) : (
            <NotifyLink className="act" text={todo("Telegram kanal")}>
              <Icon name="mega" />
              Kanal
            </NotifyLink>
          )}
          {github ? (
            <a className="act" href={`https://github.com/${github}`} target="_blank" rel="noopener noreferrer">
              <Icon name="branch" />
              GitHub
            </a>
          ) : (
            <NotifyLink className="act" text="Bu namuna — GitHub username kiriting" action={SETTINGS_ACTION}>
              <Icon name="branch" />
              GitHub
            </NotifyLink>
          )}
          <NotifyLink className="act" text={todo("Email")}>
            <Icon name="mail" />
            Email
          </NotifyLink>
        </div>
      </section>
      {now.length ? (
        <section className="c m-now">
          <CardTitle icon="sparkle">Hozir</CardTitle>
          <div className="nl">
            {now.map((item) => (
              <div key={item.k}>
                <small>{item.k}</small>
                <b>{item.v}</b>
              </div>
            ))}
          </div>
        </section>
      ) : null}
      {paragraphs.length ? (
        <section className="c m-bio">
          <CardTitle icon="person">Men haqimda</CardTitle>
          {paragraphs.map((text) => (
            <p key={text}>{text}</p>
          ))}
        </section>
      ) : null}
      <div className="m-side">
        <Link className="c mlk" href="/loyihalar">
          <span className="ricon">
            <Icon name="folder" />
          </span>
          <b>Loyihalarim</b>
          <small>{projectCount}</small>
        </Link>
        <Link className="c mlk" href="/fikrlar">
          <span className="ricon">
            <Icon name="bookOpen" />
          </span>
          <b>Fikrlarim</b>
          <small>{thoughtCount}</small>
        </Link>
        <NotifyLink className="c mlk" text={todo("CV")}>
          <span className="ricon">
            <Icon name="download" />
          </span>
          <b>CV yuklab olish</b>
          <small>PDF</small>
        </NotifyLink>
        <div className="c thm">
          <b>Mavzu</b>
          <ThemeSeg />
        </div>
      </div>
    </div>
  );
}

export default function MenPage() {
  return (
    <div className="wrapx">
      <Suspense fallback={<div className="mgrid" aria-hidden="true" />}>
        <Content />
      </Suspense>
      <p className="adm-l">
        <Link href="/admin">Admin panel</Link>
      </p>
    </div>
  );
}
