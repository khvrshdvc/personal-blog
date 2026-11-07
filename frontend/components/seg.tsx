"use client";

import Link from "next/link";
import { useLayoutEffect, useRef } from "react";

interface SegItem {
  key: string;
  label: string;
  href?: string;
}

interface SegProps {
  items: SegItem[];
  active: string;
  label: string;
  onPick?: (key: string) => void;
  className?: string;
}

export function Seg({ items, active, label, onPick, className }: SegProps) {
  const root = useRef<HTMLDivElement>(null);
  const knob = useRef<HTMLSpanElement>(null);

  useLayoutEffect(() => {
    const place = () => {
      const target = root.current?.querySelector<HTMLElement>("[data-active='true']");
      const el = knob.current;

      if (!target || !el) return;

      el.style.width = `${target.offsetWidth}px`;
      el.style.transform = `translateX(${target.offsetLeft - 2}px)`;
    };

    place();
    const observer = new ResizeObserver(place);

    if (root.current) observer.observe(root.current);
    document.fonts?.ready.then(place);

    return () => observer.disconnect();
  }, [active, items.length]);

  return (
    <div ref={root} className={`segc${className ? ` ${className}` : ""}`} role="group" aria-label={label}>
      <span ref={knob} className="knob" aria-hidden="true" />
      {items.map((item) =>
        item.href ? (
          <Link
            key={item.key}
            href={item.href}
            data-active={item.key === active}
            aria-current={item.key === active ? "page" : undefined}
          >
            {item.label}
          </Link>
        ) : (
          <button
            key={item.key}
            type="button"
            data-active={item.key === active}
            aria-pressed={item.key === active}
            onClick={() => onPick?.(item.key)}
          >
            {item.label}
          </button>
        ),
      )}
    </div>
  );
}
