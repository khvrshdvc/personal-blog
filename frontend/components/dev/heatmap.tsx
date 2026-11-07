"use client";

import { useEffect, useRef, useState } from "react";
import { weekCount, type Cell, type MonthLabel } from "@/lib/activity";
import { fmtLong } from "@/lib/format";

interface Tip {
  text: string;
  x: number;
  y: number;
}

export function Heatmap({ cells, months }: { cells: (Cell | null)[]; months: MonthLabel[] }) {
  const wrap = useRef<HTMLDivElement>(null);
  const [tip, setTip] = useState<Tip | null>(null);
  const columns = `repeat(${weekCount(cells)}, 28px)`;

  useEffect(() => {
    const el = wrap.current;

    if (el) el.scrollLeft = el.scrollWidth;

    const hide = () => setTip(null);
    window.addEventListener("scroll", hide, { passive: true });

    return () => window.removeEventListener("scroll", hide);
  }, []);

  function show(event: React.MouseEvent) {
    const target = event.target;

    if (!(target instanceof HTMLElement) || target.tagName !== "I" || !target.dataset.d) {
      setTip(null);
      return;
    }

    const rect = target.getBoundingClientRect();
    const count = Number(target.dataset.c);

    setTip({
      text: `${count ? `${count} ta hissa` : "Hissa yoʻq"} · ${fmtLong(target.dataset.d)}`,
      x: rect.left + rect.width / 2,
      y: rect.top,
    });
  }

  return (
    <>
      <div className="hm-wrap" ref={wrap}>
        <div className="hm" style={{ minWidth: 0 }}>
          <div className="hm-months" aria-hidden="true" style={{ gridTemplateColumns: columns }}>
            {months.map((month) => (
              <span key={month.column} style={{ gridColumn: `${month.column}/span 3` }}>
                {month.label}
              </span>
            ))}
          </div>
          <div className="hm-body">
            <div className="hm-days" aria-hidden="true">
              <span>Du</span>
              <span />
              <span>Ch</span>
              <span />
              <span>Ju</span>
              <span />
              <span />
            </div>
            <div className="hm-grid" role="img" aria-label="GitHub faollik xaritasi" style={{ gridTemplateColumns: columns, flex: "none" }} onMouseOver={show} onMouseLeave={() => setTip(null)}>
              {cells.map((cell, index) =>
                cell === null ? (
                  <i key={index} className="x" />
                ) : (
                  <i key={index} data-l={cell.level} data-c={cell.c} data-d={cell.d} />
                ),
              )}
            </div>
          </div>
          <div className="hm-foot">
            <span>Kataklar ustiga olib boring</span>
            <div className="legend">
              <span>Kam</span>
              {[0, 1, 2, 3, 4].map((level) => (
                <i key={level} data-l={level} />
              ))}
              <span>Koʻp</span>
            </div>
          </div>
        </div>
      </div>
      {tip ? (
        <div className="tip" style={{ left: tip.x, top: tip.y }}>
          {tip.text}
        </div>
      ) : null}
    </>
  );
}
