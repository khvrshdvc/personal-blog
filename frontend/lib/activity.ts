import { SHORT_MONTHS } from "@/lib/format";

export interface Day {
  d: string;
  c: number;
}

export interface Cell {
  d: string;
  c: number;
  level: 0 | 1 | 2 | 3 | 4;
  future: boolean;
}

export interface MonthLabel {
  column: number;
  label: string;
}

const DAY = 86400000;

export const INITIAL_DAYS = 60;

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

export function buildGrid(days: Day[], today?: string): (Cell | null)[] {
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
    cells.push({ d: date, c: count, level: level(count), future: today !== undefined && date > today });
  }

  return cells;
}

export function weekCount(cells: (Cell | null)[]): number {
  return Math.ceil(cells.length / 7);
}

export function monthLabels(cells: (Cell | null)[]): MonthLabel[] {
  const labels: MonthLabel[] = [];
  const weeks = weekCount(cells);

  for (let week = 0; week < weeks; week++) {
    const column = cells.slice(week * 7, week * 7 + 7).filter((item): item is Cell => item !== null);
    const first = column.find((item) => item.d.endsWith("-01"));

    if (first && week < weeks - 1) {
      labels.push({ column: week + 1, label: SHORT_MONTHS[Number(first.d.slice(5, 7)) - 1] });
    } else if (week === 0 && column.length) {
      labels.push({ column: 1, label: SHORT_MONTHS[Number(column[column.length - 1].d.slice(5, 7)) - 1] });
    }
  }

  return labels;
}

export interface Range {
  days: Day[];
  stats: Day[];
  today: string;
  startMonth: number;
  endMonth: number;
}

export function rangeFrom(days: Day[], monthsBack: number, monthsAhead = 1): Range {
  const today = days[days.length - 1]?.d ?? iso(Date.now());
  const first = days[0]?.d ?? today;
  const [year, month] = today.split("-").map(Number);
  const startTime = Math.max(utc(first), Date.UTC(year, month - 1 - monthsBack, 1));
  const endTime = Date.UTC(year, month - 1 + monthsAhead + 1, 0);
  const gridStart = startTime - ((new Date(startTime).getUTCDay() + 6) % 7) * DAY;
  const gridEnd = endTime + (6 - ((new Date(endTime).getUTCDay() + 6) % 7)) * DAY;
  const counts = new Map(days.map((day) => [day.d, day.c]));
  const out: Day[] = [];

  for (let time = gridStart; time <= gridEnd; time += DAY) {
    const date = iso(time);
    out.push({ d: date, c: counts.get(date) ?? 0 });
  }

  const from = iso(startTime);

  return {
    days: out,
    stats: out.filter((day) => day.d >= from && day.d <= today),
    today,
    startMonth: new Date(startTime).getUTCMonth(),
    endMonth: new Date(endTime).getUTCMonth(),
  };
}

export const WEEKDAYS = ["Du", "Se", "Ch", "Pa", "Ju", "Sh", "Ya"];

export interface Insights {
  longestStreak: number;
  best: Day | null;
  perActive: number;
  weekdays: number[];
  months: { key: string; month: number; total: number }[];
  busiestWeekday: number | null;
}

export function insights(days: Day[], today: string): Insights {
  const past = days.filter((day) => day.d <= today);
  const weekdays = [0, 0, 0, 0, 0, 0, 0];
  const monthTotals = new Map<string, number>();
  let longest = 0;
  let run = 0;
  let best: Day | null = null;
  let total = 0;
  let active = 0;

  for (const day of past) {
    const weekday = (new Date(utc(day.d)).getUTCDay() + 6) % 7;
    weekdays[weekday] += day.c;
    monthTotals.set(day.d.slice(0, 7), (monthTotals.get(day.d.slice(0, 7)) ?? 0) + day.c);

    if (day.c > 0) {
      run++;
      active++;
      total += day.c;
      longest = Math.max(longest, run);
      if (best === null || day.c > best.c) best = day;
    } else {
      run = 0;
    }
  }

  const [year, month] = today.split("-").map(Number);
  const months = Array.from({ length: 12 }, (_, index) => {
    const date = new Date(Date.UTC(year, month - 1 - (11 - index), 1));
    const key = date.toISOString().slice(0, 7);

    return { key, month: date.getUTCMonth(), total: monthTotals.get(key) ?? 0 };
  });
  const top = Math.max(...weekdays);

  return {
    longestStreak: longest,
    best,
    perActive: active ? Math.round((total / active) * 10) / 10 : 0,
    weekdays,
    months,
    busiestWeekday: top > 0 ? weekdays.indexOf(top) : null,
  };
}
