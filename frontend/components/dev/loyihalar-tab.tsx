import { Suspense } from "react";
import { DevShell } from "@/components/dev/dev-shell";
import { CardTitle } from "@/components/card-title";
import { Icon } from "@/components/icon";
import { NotifyLink } from "@/components/notify-link";
import { RunIcon } from "@/components/run-icon";
import { api } from "@/lib/api";
import { requestTime } from "@/lib/time";
import type { Project, RepoInfo, Settings } from "@/lib/api";
import { ago, githubUrl, initials } from "@/lib/format";
import { CI_LABELS, LANG_COLORS, STACK, mentions } from "@/lib/site";

const DEMO_NOTICE = {
  text: "Bu namuna — GitHub username kiriting",
  action: { label: "Sozlamalar", href: "/admin/settings" },
};

function codeUrl(project: Project, github: string | null): string | null {
  const direct = githubUrl(project.repo);

  if (direct) return direct;

  return project.repo && github ? `https://github.com/${github}/${project.repo}` : null;
}

function App({ project, info, github, now }: { project: Project; info: RepoInfo | null; github: string | null; now: number }) {
  const code = codeUrl(project, github);
  const hasLinks = Boolean(project.repo) || Boolean(project.demo_url);
  const hasMeta = Boolean(info && (info.run || info.lang || info.stars || info.pushed_at));

  return (
    <div className="c app">
      <div className="app-h">
        <span className="app-m" aria-hidden="true">
          {initials(project.name)}
        </span>
        <span className="app-n">{project.name || "Nomsiz loyiha"}</span>
        {hasLinks ? (
          <span className="app-ls">
            {project.repo ? (
              code ? (
                <a className="app-l" href={code} target="_blank" rel="noopener noreferrer" aria-label={`${project.name} — kod`}>
                  Kod <Icon name="arrowUR" />
                </a>
              ) : (
                <NotifyLink className="app-l" text={DEMO_NOTICE.text} action={DEMO_NOTICE.action} ariaLabel={`${project.name} — kod`}>
                  Kod <Icon name="arrowUR" />
                </NotifyLink>
              )
            ) : null}
            {project.demo_url ? (
              <a className="app-l" href={project.demo_url} target="_blank" rel="noopener noreferrer" aria-label={`${project.name} — demo`}>
                Demo <Icon name="arrowUR" />
              </a>
            ) : null}
          </span>
        ) : null}
      </div>
      {project.description ? <p className="app-d">{project.description}</p> : null}
      {hasMeta && info ? (
        <span className="a-meta">
          {info.run ? (
            <span className={`ci ${info.run.status}`}>
              <RunIcon status={info.run.status} />
              {CI_LABELS[info.run.status]}
            </span>
          ) : null}
          {info.lang ? (
            <span className="lang" style={{ ["--lc" as string]: LANG_COLORS[info.lang] ?? "#8e8e93" }}>
              {info.lang}
            </span>
          ) : null}
          {info.stars ? (
            <span>
              <Icon name="star" />
              {info.stars}
            </span>
          ) : null}
          {info.pushed_at ? <span>{ago(info.pushed_at, now)}</span> : null}
        </span>
      ) : null}
      {project.stack.length ? <span className="app-s">{project.stack.join(" · ")}</span> : null}
    </div>
  );
}

async function safe<T>(promise: Promise<T>, fallback: T): Promise<T> {
  try {
    return await promise;
  } catch {
    return fallback;
  }
}

async function Content() {
  const now = await requestTime();
  const [projects, settings] = await Promise.all([
    safe(api.getProjects(), null as Project[] | null),
    safe(api.getSettings(), null as Settings | null),
  ]);

  if (projects === null) {
    return <p className="empty">Loyihalarni yuklab boʻlmadi. Birozdan keyin qayta urinib koʻring.</p>;
  }

  const infos = await Promise.all(projects.map((project) => safe(api.getRepoInfo(project.id), null)));
  const learn = (settings?.learn ?? []).map((item) => item.n.toLowerCase()).filter(Boolean);
  const github = settings?.github?.trim() || null;

  return (
    <>
      <div className="apps-grid">
        {projects.length ? (
          projects.map((project, index) => <App key={project.id} project={project} info={infos[index]} github={github} now={now} />)
        ) : (
          <p className="empty">Hali loyiha qoʻshilmagan.</p>
        )}
      </div>
      <div className="stk-grid">
        {STACK.map((group) => (
          <div key={group.name} className="c">
            <CardTitle icon={group.icon}>{group.name}</CardTitle>
            <div className="tags">
              {group.items.map((item) => (
                <span key={item} className={learn.some((text) => mentions(text, item)) ? "on" : undefined}>
                  {item}
                </span>
              ))}
            </div>
          </div>
        ))}
      </div>
    </>
  );
}

export function LoyihalarTab() {
  return (
    <DevShell tab="loyihalar">
      <Suspense
        fallback={
          <div className="apps-grid" aria-hidden="true">
            <span className="skel" />
            <span className="skel" />
          </div>
        }
      >
        <Content />
      </Suspense>
    </DevShell>
  );
}
