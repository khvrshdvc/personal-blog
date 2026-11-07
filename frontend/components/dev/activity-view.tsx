"use client";

import { useState } from "react";
import { Heatmap } from "@/components/dev/heatmap";
import { INITIAL_DAYS, STEP_DAYS, buildGrid, monthLabels, summarize, type Day } from "@/lib/activity";

function rangeLabel(visible: number, all: number): string {
  if (visible >= all) return "soʻnggi yil";

  return `soʻnggi ${Math.round(visible / 30)} oy`;
}

export function ActivityView({ days, available }: { days: Day[]; available: boolean }) {
  const [visible, setVisible] = useState(INITIAL_DAYS);
  const shown = days.slice(-visible);
  const summary = summarize(shown);
  const cells = buildGrid(shown);
  const hasMore = visible < days.length;

  return (
    <>
      <div className="sum">
        <div>
          <b>{available ? summary.total : "—"}</b>
          <span>hissa</span>
        </div>
        <div>
          <b>{available ? summary.active : "—"}</b>
          <span>faol kun</span>
        </div>
        <div>
          <b>{available ? summary.streak : "—"}</b>
          <span>kunlik seriya</span>
        </div>
      </div>
      <div className="c hmcard">
        <div className="hm-top">
          <b>Faollik</b>
          <span>{rangeLabel(visible, days.length)}</span>
        </div>
        {cells.length ? (
          <Heatmap cells={cells} months={monthLabels(cells)} />
        ) : (
          <div className="empty-row">Faollik maʼlumoti hozircha mavjud emas</div>
        )}
        {hasMore ? (
          <button className="more-btn" type="button" onClick={() => setVisible((value) => Math.min(days.length, value + STEP_DAYS))}>
            Yana 1 oy ochish
          </button>
        ) : null}
      </div>
    </>
  );
}
