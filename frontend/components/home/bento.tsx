import Link from "next/link";
import { CardTitle } from "@/components/card-title";
import { Clock } from "@/components/home/clock";
import { LearnRotator } from "@/components/home/learn-rotator";
import { Icon } from "@/components/icon";
import { api } from "@/lib/api";
import { requestTime } from "@/lib/time";
import type { Activity, PostListItem, Project, RepoInfo, Run, Settings, Telegram } from "@/lib/api";
import { INITIAL_DAYS, buildGrid, summarize, weekCount } from "@/lib/activity";
import { CI_LABELS, STACK, TEACH } from "@/lib/site";
import { ago, fmtLong, fmtShort, initials, repoName } from "@/lib/format";

interface HomeData {
  latest: PostListItem[];
  thoughtCount: number;
  settings: Settings | null;
  projects: Project[];
  infos: Record<number, RepoInfo | null>;
  activity: Activity | null;
  runs: Run[];
  telegram: Telegram | null;
}

async function safe<T>(promise: Promise<T>, fallback: T): Promise<T> {
  try {
    return await promise;
  } catch {
    return fallback;
  }
}

async function load(): Promise<HomeData> {
  const [posts, settings, projects, activity, runs, telegram] = await Promise.all([
    safe(api.getPosts({ category: "fikr", per_page: 4 }), null),
    safe(api.getSettings(), null),
    safe(api.getProjects(), [] as Project[]),
    safe(api.getActivity(), null),
    safe(api.getRuns(), [] as Run[]),
    safe(api.getTelegram(), null),
  ]);

  const infoPairs = await Promise.all(
    projects.slice(0, 3).map(async (project) => [project.id, await safe(api.getRepoInfo(project.id), null)] as const),
  );

  return {
    latest: posts?.data ?? [],
    thoughtCount: posts?.meta.total ?? 0,
    settings,
    projects,
    infos: Object.fromEntries(infoPairs),
    activity,
    runs,
    telegram,
  };
}

const RUN_SYMBOL = {
  success: <span className="ok">✓</span>,
  failure: <span className="bad">✕</span>,
  running: <span className="ok">●</span>,
  skipped: <em>–</em>,
} as const;

export async function HomeBento() {
  const now = await requestTime();
  const data = await load();
  const latest = data.latest[0];
  const learn = (data.settings?.learn ?? []).filter((item) => item.n.trim() !== "");
  const tags = STACK.flatMap((group) => group.items).slice(0, 11);
  const tashkent = new Date(now).toLocaleTimeString("en-GB", { timeZone: "Asia/Tashkent", hour: "2-digit", minute: "2-digit" });
  const telegram = data.telegram ?? data.settings?.telegram ?? null;
  const telegramPosts = (telegram?.posts ?? []).filter((post) => post.t.trim() !== "");
  const activityDays = (data.activity?.days ?? []).slice(-INITIAL_DAYS);
  const summary = summarize(activityDays);
  const grid = buildGrid(activityDays);
  const runs = data.runs.slice(0, 4);

  return (
    <div className="bento">
      {latest ? (
        <Link className="c b-post" href={`/post/${latest.slug}`}>
          <span className="qm" aria-hidden="true">
            “
          </span>
          <CardTitle icon="bookOpen">Soʻnggi fikr · {fmtLong(latest.published_at)}</CardTitle>
          <h2>{latest.title}</h2>
          {latest.excerpt ? <p>{latest.excerpt}</p> : null}
          <span className="cf">
            <span>
              {latest.tag} · {latest.read_minutes} daqiqa oʻqish
              {data.thoughtCount > 1 ? ` · yana ${data.thoughtCount - 1} ta fikr` : ""}
            </span>
            <span className="arr">
              <Icon name="arrowUR" />
            </span>
          </span>
        </Link>
      ) : (
        <div className="c b-post">
          <CardTitle icon="bookOpen">Fikrlar</CardTitle>
          <h2>Hali fikr yozilmagan</h2>
        </div>
      )}

      <div className="c b-loc">
        <CardTitle icon="pin">
          Joylashuv<span className="r">UTC+5</span>
        </CardTitle>
        <span className="radar" aria-hidden="true" />
        <span className="pin" aria-hidden="true">
          <i />
          <i />
          <i />
        </span>
        <div className="lb">
          <span>
            <b>Toshkent</b>
            <small>41.31° N · 69.28° E</small>
          </span>
          <Clock initial={tashkent} />
        </div>
      </div>

      <Link className="c b-gh" href="/faollik">
        <CardTitle icon="branch">
          GitHub<span className="r">{summary.streak ? `${summary.streak} kun seriya` : ""}</span>
        </CardTitle>
        <div className="gn">
          <span>{data.activity ? summary.total : "—"}</span>
          <small>hissa · soʻnggi 2 oy</small>
        </div>
        <div className="mhw" aria-hidden="true">
          <div className="mhm" style={{ ["--cols" as string]: weekCount(grid) || 1 }}>
            {grid.map((cell, index) =>
              cell === null ? <i key={index} className="x" /> : <i key={index} data-l={cell.level} />,
            )}
          </div>
        </div>
      </Link>

      {learn.length ? <LearnRotator items={learn} /> : null}

      <div className="c b-tch">
        <div>
          <div className="tv">{TEACH.v}</div>
          <div className="tt">{TEACH.t}</div>
        </div>
        <div className="tdots" aria-hidden="true">
          {Array.from({ length: 12 }, (_, index) => (
            <i key={index} />
          ))}
        </div>
      </div>

      <Link className="c b-term" href="/faollik" aria-label="GitHub Actions — soʻnggi ishlar">
        <div className="tbar2" aria-hidden="true">
          <i />
          <i />
          <i />
          <span>~/utkirbek — zsh</span>
        </div>
        <div className="tc">
          <div>
            <b>$ gh run list --limit 4</b>
          </div>
          {runs.length ? (
            runs.map((run, index) => (
              <div key={`${run.repo}-${run.num}-${index}`} style={{ animationDelay: `${(0.15 + index * 0.18).toFixed(2)}s` }}>
                {RUN_SYMBOL[run.status]} <b>{run.workflow}</b> {run.title}{" "}
                <em>
                  · {run.repo} · {ago(run.at, now)}
                </em>
              </div>
            ))
          ) : (
            <div style={{ animationDelay: ".15s" }}>
              <em>hali ishga tushmagan</em>
            </div>
          )}
          <div style={{ animationDelay: `${(0.3 + runs.length * 0.18).toFixed(2)}s` }}>
            <b>$</b>
            <span className="cur" />
          </div>
        </div>
      </Link>

      <Link className="c b-proj" href="/loyihalar">
        <CardTitle icon="folder">
          Loyihalar<span className="r">{data.projects.length}</span>
        </CardTitle>
        <div className="pl">
          {data.projects.length ? (
            data.projects.slice(0, 3).map((project) => {
              const run = data.infos[project.id]?.run ?? data.runs.find((item) => repoName(item.repo) === repoName(project.repo));
              const status = run?.status;

              return (
                <span key={project.id} className="pi">
                  <span className="pm" aria-hidden="true">
                    {initials(project.name)}
                  </span>
                  <span className="pn">{project.name || "Nomsiz"}</span>
                  <span className={`pd${status ? ` ${status}` : ""}`} title={status ? CI_LABELS[status] : undefined} />
                </span>
              );
            })
          ) : (
            <span className="pn">Hali loyiha yoʻq</span>
          )}
        </div>
      </Link>

      <div className="c b-stk">
        <CardTitle icon="cube">Stack</CardTitle>
        <div className="tags">
          {tags.map((tag, index) => (
            <span key={tag} className={index < 2 ? "on" : undefined}>
              {tag}
            </span>
          ))}
        </div>
      </div>

      <div className="c b-rec">
        <CardTitle icon="list">
          Fikrlar
          <Link className="r" href="/fikrlar">
            Barchasi →
          </Link>
        </CardTitle>
        <div className="rl">
          {data.latest.map((post) => (
            <Link key={post.id} href={`/post/${post.slug}`}>
              <time dateTime={post.published_at ?? undefined}>{fmtShort(post.published_at)}</time>
              <span>{post.title}</span>
            </Link>
          ))}
        </div>
      </div>

      <section className="c b-tg" aria-label="Telegram kanal">
        <div className="tg-l">
          <CardTitle icon="send">Telegram kanal</CardTitle>
          <div className="tg-ch">
            <span className="tg-av" aria-hidden="true">
              <Icon name="send" />
              <i />
            </span>
            <b className="tg-n">{telegram?.n || "Telegram kanal"}</b>
            <span className="tg-u">{telegram?.u ? `t.me/${telegram.u}` : "kanal · ingliz tilida"}</span>
          </div>
          {telegram?.u ? (
            <a className="tg-btn" href={`https://t.me/${telegram.u}`} target="_blank" rel="noopener noreferrer">
              <Icon name="plus" />
              Obuna boʻlish
            </a>
          ) : (
            <span className="tg-btn" aria-disabled="true">
              <Icon name="plus" />
              Obuna boʻlish
            </span>
          )}
        </div>
        <div className="tg-chat" aria-label="Soʻnggi postlar">
          <span className="tg-day">Soʻnggi postlar</span>
          {telegramPosts.length ? (
            telegramPosts.map((post, index) => (
              <div key={`${post.d}-${index}`} className="tg-b">
                <p>{post.t}</p>
                <small>{fmtShort(post.d)}</small>
              </div>
            ))
          ) : (
            <div className="tg-b">
              <p>Hali post qoʻshilmagan.</p>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}

export function BentoSkeleton() {
  return (
    <div className="bento" aria-hidden="true">
      {Array.from({ length: 6 }, (_, index) => (
        <div key={index} className="c" style={{ minHeight: 140 }} />
      ))}
    </div>
  );
}
