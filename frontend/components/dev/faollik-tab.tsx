import Link from "next/link";
import { Suspense } from "react";
import { DevShell } from "@/components/dev/dev-shell";
import { Heatmap } from "@/components/dev/heatmap";
import { Icon } from "@/components/icon";
import { NotifyLink } from "@/components/notify-link";
import { RunIcon } from "@/components/run-icon";
import { api } from "@/lib/api";
import { requestTime } from "@/lib/time";
import type { Activity, Run } from "@/lib/api";
import { buildGrid, monthLabels, summarize } from "@/lib/activity";
import { ago, duration } from "@/lib/format";
import { RUN_LABELS } from "@/lib/site";

const DEMO_NOTICE = {
  text: "Bu namuna — GitHub username kiriting",
  action: { label: "Sozlamalar", href: "/admin/settings" },
};

async function safe<T>(promise: Promise<T>, fallback: T): Promise<T> {
  try {
    return await promise;
  } catch {
    return fallback;
  }
}

function RunRow({ run, now }: { run: Run; now: number }) {
  const inner = (
    <>
      <span className={`rstat ${run.status}`} title={RUN_LABELS[run.status]}>
        <RunIcon status={run.status} />
        <span className="sr">{RUN_LABELS[run.status]}</span>
      </span>
      <span className="rbody">
        <span className="rt">{run.title}</span>
        <span className="rsub">
          {run.workflow} · {run.repo}
          {run.branch ? ` · ${run.branch}` : ""}
          {run.num ? ` · #${run.num}` : ""}
        </span>
      </span>
      <span className="rright">
        <span>{ago(run.at, now)}</span>
        <span className="dur">{run.status === "running" ? "ishlamoqda" : duration(run.dur)}</span>
      </span>
    </>
  );

  return (
    <li>
      {run.url ? (
        <a className="row" href={run.url} target="_blank" rel="noopener noreferrer">
          {inner}
        </a>
      ) : (
        <NotifyLink className="row" text={DEMO_NOTICE.text} action={DEMO_NOTICE.action}>
          {inner}
        </NotifyLink>
      )}
    </li>
  );
}

function Notice({ text }: { text: string }) {
  return (
    <ul className="list icons" style={{ marginBottom: 12 }}>
      <li>
        <Link className="row" href="/admin/settings">
          <span className="ricon">
            <Icon name="info" />
          </span>
          <span className="rbody">
            <span className="rt">Namuna maʼlumot</span>
            <span className="rs2">{text}</span>
          </span>
          <span className="chev">
            <Icon name="chevR" />
          </span>
        </Link>
      </li>
    </ul>
  );
}

async function Content() {
  const now = await requestTime();
  const [activity, runs] = await Promise.all([safe(api.getActivity(), null as Activity | null), safe(api.getRuns(), [] as Run[])]);
  const days = activity?.days ?? [];
  const summary = summarize(days);
  const cells = buildGrid(days);

  return (
    <>
      {activity === null ? (
        <Notice text="GitHub maʼlumotini hozircha yuklab boʻlmadi." />
      ) : !activity.real ? (
        <Notice text="Haqiqiy faollik uchun admin panelda GitHub username kiriting." />
      ) : null}
      <div className="sum">
        <div>
          <b>{activity ? summary.total : "—"}</b>
          <span>hissa</span>
        </div>
        <div>
          <b>{activity ? summary.active : "—"}</b>
          <span>faol kun</span>
        </div>
        <div>
          <b>{activity ? summary.streak : "—"}</b>
          <span>kunlik seriya</span>
        </div>
      </div>
      <div className="c hmcard">
        <div className="hm-top">
          <b>Faollik</b>
          <span>soʻnggi 2 oy</span>
        </div>
        {cells.length ? (
          <Heatmap cells={cells} months={monthLabels(cells)} />
        ) : (
          <div className="empty-row">Faollik maʼlumoti hozircha mavjud emas</div>
        )}
      </div>
      <div className="group">
        <h3 className="group-h">GitHub Actions</h3>
        <ul className="list" style={{ borderRadius: 24 }}>
          {runs.length ? (
            runs.slice(0, 8).map((run, index) => <RunRow key={`${run.repo}-${run.num}-${index}`} run={run} now={now} />)
          ) : (
            <li className="empty-row">Loyihalaringizda hali GitHub Actions ishga tushmagan</li>
          )}
        </ul>
      </div>
    </>
  );
}

export function FaollikTab() {
  return (
    <DevShell tab="faollik">
      <Suspense
        fallback={
          <div className="sum" aria-hidden="true">
            {[0, 1, 2].map((item) => (
              <div key={item}>
                <span className="skel" style={{ height: 52 }} />
              </div>
            ))}
          </div>
        }
      >
        <Content />
      </Suspense>
    </DevShell>
  );
}
