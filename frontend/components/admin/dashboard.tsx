"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { Icon } from "@/components/icon";
import { CardTitle } from "@/components/card-title";
import { adminApi } from "@/lib/admin/api";
import type { Dashboard as DashboardData, PostBrief } from "@/lib/admin/types";
import { STATE_CLASS, STATE_LABEL } from "@/lib/admin/post-model";
import { ago, initials } from "@/lib/format";

const WEEK = ["Yakshanba", "Dushanba", "Seshanba", "Chorshanba", "Payshanba", "Juma", "Shanba"];
const MONTHS = ["yanvar", "fevral", "mart", "aprel", "may", "iyun", "iyul", "avgust", "sentabr", "oktabr", "noyabr", "dekabr"];
const OPACITY = [1, 0.72, 0.52, 0.36, 0.24, 0.16, 0.1, 0.07];
const CHECK_LABELS = { github: "GitHub", telegram: "Telegram kanal", bio: "Men haqimda", learn: "Oʻrganyapman", open: "Ishga ochiq" } as const;

function tashkent(now: number) {
  const parts = new Intl.DateTimeFormat("en-GB", {
    timeZone: "Asia/Tashkent",
    weekday: "long",
    day: "numeric",
    month: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  }).formatToParts(new Date(now));
  const get = (type: string) => parts.find((part) => part.type === type)?.value ?? "";
  const weekday = WEEK[["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"].indexOf(get("weekday"))];

  return { weekday, day: Number(get("day")), month: Number(get("month")), hour: Number(get("hour")), time: `${get("hour")}:${get("minute")}` };
}

function greeting(hour: number): string {
  if (hour >= 5 && hour < 11) return "Xayrli tong";
  if (hour >= 11 && hour < 17) return "Xayrli kun";
  if (hour >= 17 && hour < 22) return "Xayrli kech";

  return "Xayrli tun";
}

function level(count: number): number {
  if (count <= 0) return 0;
  if (count === 1) return 2;
  if (count === 2) return 3;

  return 4;
}

function describeCheck(key: keyof typeof CHECK_LABELS, ok: boolean, value: string): string {
  if (key === "github") return ok ? `@${value}` : "Username kiritilmagan";
  if (key === "telegram") return ok ? `t.me/${value}` : "Username kiritilmagan";
  if (key === "bio") return ok ? `${value} soʻz` : "Matn yoʻq";
  if (key === "learn") return `${value} ta mavzu`;

  return ok ? "Saytda koʻrinmoqda" : "Oʻchirilgan";
}

function Row({ item, now }: { item: PostBrief; now: number }) {
  return (
    <Link className="dl-r" href={`/admin/posts/${item.id}`}>
      <span className={`sdot ${STATE_CLASS[item.status]}`} aria-hidden="true" />
      <span className="dl-t">{item.title || "Sarlavhasiz"}</span>
      <span className="dl-s">{STATE_LABEL[item.status]}</span>
      <span className="dl-a">{item.updated_at ? ago(item.updated_at, now) : ""}</span>
    </Link>
  );
}

export function Dashboard() {
  const [data, setData] = useState<DashboardData | null>(null);
  const [failed, setFailed] = useState(false);
  const [now] = useState(() => Date.now());

  useEffect(() => {
    adminApi
      .dashboard()
      .then(setData)
      .catch(() => setFailed(true));
  }, []);

  const clock = tashkent(now);

  if (!data) {
    return (
      <div className="d-scroll">
        <div className="adash">
          <header className="ad-h">
            <div>
              <p className="eyebrow">{`${clock.weekday}, ${clock.day}-${MONTHS[clock.month - 1]} · Toshkent ${clock.time}`}</p>
              <h1>
                {greeting(clock.hour)}, Utkirbek. <span>{failed ? "Maʼlumotni yuklab boʻlmadi." : "Yuklanmoqda…"}</span>
              </h1>
            </div>
          </header>
        </div>
      </div>
    );
  }

  const { counts, last_edited: last, resume, activity, tags, checks, projects } = data;
  const days = activity.days.slice(-182);
  const columns = Math.ceil(days.length / 7);
  const cells = Array.from({ length: columns * 7 }, (_, index) => days[index - (columns * 7 - days.length)] ?? null);
  const since = activity.days_since_last;
  const okCount = checks.filter((check) => check.ok).length;

  return (
    <div className="d-scroll">
      <div className="adash">
        <header className="ad-h">
          <div>
            <p className="eyebrow">{`${clock.weekday}, ${clock.day}-${MONTHS[clock.month - 1]} · Toshkent ${clock.time}`}</p>
            <h1>
              {greeting(clock.hour)}, Utkirbek. <span>Bugun nima haqida yozasiz?</span>
            </h1>
          </div>
          <Link className="dbtn" href="/">
            <Icon name="eye" />
            Saytni koʻrish
          </Link>
        </header>
        <div className="dgrid">
          {last ? (
            <section className="c d-hero">
              <CardTitle icon="compose">
                Oxirgi tahrir
                <span className="r">
                  <span className={`pill ${STATE_CLASS[last.status]}`}>{STATE_LABEL[last.status]}</span>
                </span>
              </CardTitle>
              <h2>{last.title || "Sarlavhasiz"}</h2>
              <p>{last.excerpt}</p>
              <span className="d-m">
                {last.tag} · {last.updated_at ? ago(last.updated_at, now) : ""}
              </span>
              <div className="d-acts">
                <Link className="dbtn pri" href={`/admin/posts/${last.id}`}>
                  <Icon name="pen" />
                  Davom ettirish
                </Link>
                <Link className="dbtn" href="/admin/posts/new/fikr">
                  <Icon name="plus" />
                  Yangi fikr
                </Link>
                <Link className="dbtn" href="/admin/posts/new/dev">
                  <Icon name="code" />
                  Dasturlash posti
                </Link>
              </div>
            </section>
          ) : (
            <section className="c d-hero">
              <CardTitle icon="compose">Boshlash</CardTitle>
              <h2>Birinchi yozuvingizni yozing</h2>
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
            </section>
          )}
          <div className="d-stats">
            <Link className="c d-st" href="/admin/posts">
              <CardTitle icon="check">Chop etilgan</CardTitle>
              <b>{counts.published}</b>
              <small>
                {counts.thoughts} fikr · {counts.dev} dasturlash
              </small>
            </Link>
            <Link className="c d-st" href="/admin/posts">
              <CardTitle icon="doc">Qoralama</CardTitle>
              <b>{counts.drafts}</b>
              <small>{counts.drafts ? "yakunlanmagan" : "hammasi tayyor"}</small>
            </Link>
            <Link className="c d-st" href="/admin/posts">
              <CardTitle icon="pen">Oʻzgarish</CardTitle>
              <b>{counts.pending}</b>
              <small>{counts.pending ? "yangilashni kutmoqda" : "kutilayotgan yoʻq"}</small>
            </Link>
            <div className="c d-st">
              <CardTitle icon="bookOpen">Soʻzlar</CardTitle>
              <b>{counts.words.toLocaleString("en-US").replace(/,/g, " ")}</b>
              <small>chop etilgan matnda</small>
            </div>
          </div>
          <section className="c d-cont">
            <CardTitle icon="list">
              Davom ettirish
              <Link className="r" href="/admin/posts">
                Barchasi →
              </Link>
            </CardTitle>
            <div className="dl">
              {resume.length ? (
                resume.map((item) => <Row key={item.id} item={item} now={now} />)
              ) : (
                <p className="dl-e">
                  <Icon name="check" /> Qoralama va kutilayotgan oʻzgarish yoʻq.
                </p>
              )}
            </div>
          </section>
          <section className="c d-act">
            <CardTitle icon="grid">
              Yozish faolligi<span className="r">26 hafta</span>
            </CardTitle>
            <div className="mhm d-hm" aria-label="Yozish faolligi">
              {cells.map((cell, index) =>
                cell === null ? <i key={index} className="x" /> : <i key={index} data-l={level(cell.c)} title={`${cell.d} · ${cell.c}`} />,
              )}
            </div>
            <div className="d-af">
              <span>
                <b>{since === null ? "—" : since === 0 ? "Bugun" : `${since} kun`}</b>
                {since === null ? "chop etilgan yozuv yoʻq" : since === 0 ? "oxirgi yozuv" : "oxirgi yozuvdan beri"}
              </span>
              <span>
                <b>{activity.this_month}</b>bu oy
              </span>
              <span>
                <b>{activity.active_days}</b>faol kun
              </span>
            </div>
          </section>
          <section className="c d-cat">
            <CardTitle icon="sliders">Mavzular</CardTitle>
            <div className="cat2">
              <div>
                <b>{counts.thoughts}</b>
                <small>Fikrlar</small>
              </div>
              <div>
                <b>{counts.dev}</b>
                <small>Dasturlash</small>
              </div>
            </div>
            <div className="tbar3" aria-hidden="true">
              {tags.map((item, index) => (
                <i key={item.tag} style={{ flex: item.count, opacity: OPACITY[index] ?? 0.07 }} />
              ))}
            </div>
            <div className="tleg">
              {tags.slice(0, 6).map((item, index) => (
                <span key={item.tag}>
                  <i style={{ opacity: OPACITY[index] }} />
                  {item.tag} <em>{item.count}</em>
                </span>
              ))}
            </div>
          </section>
          <section className="c d-chk">
            <CardTitle icon="info">
              Sayt holati
              <span className="r">
                {okCount}/{checks.length}
              </span>
            </CardTitle>
            <div className="chk">
              {checks.map((check) => (
                <Link key={check.key} className={`chk-r${check.ok ? " ok" : ""}`} href="/admin/settings">
                  <span className="chk-i">{check.ok ? <Icon name="check" /> : null}</span>
                  <span className="chk-t">
                    <b>{CHECK_LABELS[check.key]}</b>
                    <small>{describeCheck(check.key, check.ok, check.value)}</small>
                  </span>
                </Link>
              ))}
            </div>
          </section>
          <section className="c d-prj">
            <CardTitle icon="folder">
              Loyihalar
              <Link className="r" href="/admin/projects">
                {counts.projects} ta →
              </Link>
            </CardTitle>
            <div className="pl2">
              {projects.length ? (
                projects.map((project) => (
                  <Link key={project.id} className="pi2" href={`/admin/projects/${project.id}`}>
                    <span className="app-m" aria-hidden="true">
                      {initials(project.name)}
                    </span>
                    <span className="pj-b">
                      <b>{project.name}</b>
                      <span className="pj-r">{project.repo || "repo yoʻq"}</span>
                    </span>
                  </Link>
                ))
              ) : (
                <p className="dl-e">Hali loyiha yoʻq.</p>
              )}
            </div>
            <Link className="dbtn sm" href="/admin/projects/new">
              <Icon name="plus" />
              Yangi loyiha
            </Link>
          </section>
        </div>
        <p className="ad-k">
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
