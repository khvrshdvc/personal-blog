"use client";

import { useState } from "react";
import { Heatmap } from "@/components/dev/heatmap";
import { buildGrid, monthLabels, rangeFrom, summarize, type Day } from "@/lib/activity";
import { MONTH_NAMES } from "@/lib/format";

const INITIAL_MONTHS_BACK = 3;

export function ActivityView({ days, available }: { days: Day[]; available: boolean }) {
  const [back, setBack] = useState(INITIAL_MONTHS_BACK);
  const range = rangeFrom(days, back);
  const past = range.days.filter((day) => day.d <= range.today);
  const summary = summarize(past);
  const cells = buildGrid(range.days, range.today);
  const label = `${MONTH_NAMES[range.startMonth]} – ${MONTH_NAMES[range.endMonth]}`;

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
          <span>{label}</span>
        </div>
        {cells.length ? (
          <Heatmap cells={cells} months={monthLabels(cells)} />
        ) : (
          <div className="empty-row">Faollik maʼlumoti hozircha mavjud emas</div>
        )}
        {range.hasMore ? (
          <button className="more-btn" type="button" onClick={() => setBack((value) => value + 1)}>
            Oldingi oyni ochish
          </button>
        ) : null}
      </div>
    </>
  );
}
