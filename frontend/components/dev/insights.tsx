import { WEEKDAYS, insights, type Day } from "@/lib/activity";
import { SHORT_MONTHS, fmtLong } from "@/lib/format";

function Bars({ items, label }: { items: { key: string; label: string; value: number }[]; label: string }) {
  const max = Math.max(1, ...items.map((item) => item.value));

  return (
    <ul className="bars" aria-label={label}>
      {items.map((item) => (
        <li key={item.key} title={`${item.label}: ${item.value} ta hissa`}>
          <span className="bar-n">{item.value || ""}</span>
          <span className="bar-c">
            <i style={{ height: `${Math.max(item.value ? 6 : 2, (item.value / max) * 100)}%` }} data-z={item.value === 0 ? "" : undefined} />
          </span>
          <span className="bar-l">{item.label}</span>
        </li>
      ))}
    </ul>
  );
}

export function ActivityInsights({ days, today }: { days: Day[]; today: string }) {
  const data = insights(days, today);
  const yearTotal = data.months.reduce((sum, item) => sum + item.total, 0);

  return (
    <div className="an">
      <div className="c an-c">
        <div className="hm-top">
          <b>Oylar boʻyicha</b>
          <span>{yearTotal} hissa · 12 oy</span>
        </div>
        <Bars label="Oylar boʻyicha hissalar" items={data.months.map((item) => ({ key: item.key, label: SHORT_MONTHS[item.month], value: item.total }))} />
      </div>
      <div className="c an-c">
        <div className="hm-top">
          <b>Haftaning kunlari</b>
          <span>{data.busiestWeekday === null ? "—" : `eng faol: ${WEEKDAYS[data.busiestWeekday]}`}</span>
        </div>
        <Bars label="Haftaning kunlari boʻyicha hissalar" items={data.weekdays.map((value, index) => ({ key: WEEKDAYS[index], label: WEEKDAYS[index], value }))} />
      </div>
      <div className="an-s">
        <div>
          <b>{data.longestStreak}</b>
          <span>eng uzun seriya, kun</span>
        </div>
        <div>
          <b>{data.best ? data.best.c : "—"}</b>
          <span>{data.best ? `eng faol kun · ${fmtLong(data.best.d)}` : "eng faol kun"}</span>
        </div>
        <div>
          <b>{data.perActive || "—"}</b>
          <span>faol kunga oʻrtacha hissa</span>
        </div>
      </div>
    </div>
  );
}
