import { SHORT_MONTHS } from "@/lib/format";

export interface Day {
  d: string;
  c: number;
}

export interface Cell {
  d: string;
  c: number;
  level: 0 | 1 | 2 | 3 | 4;
}

export interface MonthLabel {
  column: number;
  label: string;
}

const DAY = 86400000;

function utc(iso: string): number {
  const [y, m, d] = iso.split("-").map(Number);

  return Date.UTC(y, m - 1, d);
}

function iso(time: number): string {
  return new Date(time).toISOString().slice(0, 10);
}

export function level(count: number): 0 | 1 | 2 | 3 | 4 {
  if (count <= 0) return 0;
  if (count <= 2) return 1;
  if (count <= 5) return 2;
  if (count <= 9) return 3;

  return 4;
}

export function summarize(days: Day[]) {
  let total = 0;
  let streak = 0;

  for (const day of days) total += day.c;

  for (let i = days.length - 1; i >= 0; i--) {
    if (days[i].c > 0) {
      streak++;
    } else if (i !== days.length - 1) {
      break;
    }
  }

  return { total, streak, active: days.filter((day) => day.c > 0).length };
}

export function buildGrid(days: Day[]): (Cell | null)[] {
  if (days.length === 0) return [];

  const counts = new Map(days.map((day) => [day.d, day.c]));
  const first = utc(days[0].d);
  const end = utc(days[days.length - 1].d);
  const offset = (new Date(first).getUTCDay() + 6) % 7;
  const start = first - offset * DAY;
  const total = Math.ceil(((end - start) / DAY + 1) / 7) * 7;
  const cells: (Cell | null)[] = [];

  for (let i = 0; i < total; i++) {
    const time = start + i * DAY;

    if (time < first || time > end) {
      cells.push(null);
      continue;
    }

    const date = iso(time);
    const count = counts.get(date) ?? 0;
    cells.push({ d: date, c: count, level: level(count) });
  }

  return cells;
}

export function weekCount(cells: (Cell | null)[]): number {
  return Math.ceil(cells.length / 7);
}

export function monthLabels(cells: (Cell | null)[]): MonthLabel[] {
  const labels: MonthLabel[] = [];
  let previous = -1;

  for (let week = 0; week < weekCount(cells); week++) {
    const cell = cells.slice(week * 7, week * 7 + 7).find((item) => item !== null);

    if (!cell) continue;

    const month = Number(cell.d.slice(5, 7)) - 1;

    if (month !== previous) {
      labels.push({ column: week + 1, label: SHORT_MONTHS[month] });
      previous = month;
    }
  }

  return labels;
}
