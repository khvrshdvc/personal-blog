import { Heatmap } from "@/components/dev/heatmap";
import { buildGrid, monthLabels, rangeFrom, summarize, type Day } from "@/lib/activity";
import { MONTH_NAMES } from "@/lib/format";

const MONTHS_BACK = 4;

export function ActivityView({ days, available }: { days: Day[]; available: boolean }) {
  const range = rangeFrom(days, MONTHS_BACK);
  const summary = summarize(range.stats);
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
      </div>
    </>
  );
}
