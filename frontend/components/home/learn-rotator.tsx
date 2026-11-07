"use client";

import { useEffect, useState } from "react";
import { clampPercent } from "@/lib/format";

interface LearnItem {
  n: string;
  p: number;
}

export function LearnRotator({ items }: { items: LearnItem[] }) {
  const [state, setState] = useState({ cur: 0, prev: -1 });

  useEffect(() => {
    if (items.length < 2 || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    const interval = setInterval(() => {
      if (document.hidden) return;
      setState((value) => ({ cur: (value.cur + 1) % items.length, prev: value.cur }));
    }, 4200);

    return () => clearInterval(interval);
  }, [items.length]);

  return (
    <div className="c b-lrn">
      {items.map((item, index) => {
        const percent = clampPercent(item.p);
        const classes = ["lp", state.cur === index ? "on" : "", state.prev === index && state.cur !== index ? "was" : ""]
          .filter(Boolean)
          .join(" ");

        return (
          <div key={item.n} className={classes}>
            <span className="ring">
              <svg viewBox="0 0 62 62" aria-hidden="true">
                <circle className="tr" cx="31" cy="31" r="26" />
                <circle
                  className="pr"
                  cx="31"
                  cy="31"
                  r="26"
                  strokeDasharray="163.4"
                  strokeDashoffset={(163.4 * (1 - percent / 100)).toFixed(1)}
                />
              </svg>
              <b>{percent}%</b>
            </span>
            <span>
              <span className="lt1">Oʻrganyapman</span>
              <span className="lt2">{item.n}</span>
            </span>
          </div>
        );
      })}
      {items.length > 1 ? (
        <span className="ldots" aria-hidden="true">
          {items.map((item, index) => (
            <i key={item.n} className={state.cur === index ? "on" : undefined} />
          ))}
        </span>
      ) : null}
    </div>
  );
}
