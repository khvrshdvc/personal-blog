"use client";

import { useRouter } from "next/navigation";
import { useEffect, useMemo, useRef, useState } from "react";
import { Icon } from "@/components/icon";
import type { SearchItem } from "@/lib/api/types";
import { buildPaletteItems } from "@/lib/palette";

export function CommandPalette({ onClose }: { onClose: () => void }) {
  const router = useRouter();
  const inputRef = useRef<HTMLInputElement>(null);
  const previous = useRef<Element | null>(null);
  const [query, setQuery] = useState("");
  const [results, setResults] = useState<SearchItem[]>([]);
  const [active, setActive] = useState(0);

  const items = useMemo(() => buildPaletteItems(query, results), [query, results]);

  useEffect(() => {
    previous.current = document.activeElement;
    inputRef.current?.focus();

    return () => {
      const element = previous.current;

      if (element instanceof HTMLElement && document.activeElement === document.body) {
        element.focus();
      }
    };
  }, []);

  useEffect(() => {
    const controller = new AbortController();
    const timer = setTimeout(async () => {
      try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query.trim())}`, { signal: controller.signal });

        if (response.ok) {
          setResults((await response.json()) as SearchItem[]);
          setActive(0);
        }
      } catch {}
    }, query === "" ? 0 : 180);

    return () => {
      clearTimeout(timer);
      controller.abort();
    };
  }, [query]);

  const current = Math.min(active, Math.max(0, items.length - 1));

  useEffect(() => {
    document.getElementById(`kpo${current}`)?.scrollIntoView({ block: "nearest" });
  }, [current]);

  function go(index: number) {
    const item = items[index];

    if (item) {
      onClose();
      router.push(item.href);
    }
  }

  function onKeyDown(event: React.KeyboardEvent) {
    if (event.key === "Escape") {
      event.preventDefault();
      onClose();
    } else if (event.key === "ArrowDown" && items.length) {
      event.preventDefault();
      setActive((current + 1) % items.length);
    } else if (event.key === "ArrowUp" && items.length) {
      event.preventDefault();
      setActive((current - 1 + items.length) % items.length);
    } else if (event.key === "Enter") {
      event.preventDefault();
      go(current);
    }
  }

  return (
    <div
      className="kp-back"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="kp" role="dialog" aria-modal="true" aria-label="Qidiruv" onKeyDown={onKeyDown}>
        <div className="kp-in">
          <Icon name="search" />
          <input
            ref={inputRef}
            type="search"
            value={query}
            onChange={(event) => {
              setQuery(event.target.value);
              setActive(0);
            }}
            placeholder="Fikrlar, yozuvlar, loyihalar…"
            autoComplete="off"
            role="combobox"
            aria-expanded="true"
            aria-controls="kpl"
            aria-activedescendant={items.length ? `kpo${current}` : undefined}
          />
          <kbd>esc</kbd>
        </div>
        <div className="kp-l" id="kpl" role="listbox">
          {items.length === 0 ? <div className="kp-e">Hech narsa topilmadi</div> : null}
          {items.map((item, index) => {
            const isFirstInGroup = index === 0 || items[index - 1].group !== item.group;

            return (
              <div key={`${item.group}-${item.href}-${index}`}>
                {isFirstInGroup ? <h6>{item.group}</h6> : null}
                <a
                  className={`kp-r${index === current ? " on" : ""}`}
                  href={item.href}
                  id={`kpo${index}`}
                  role="option"
                  aria-selected={index === current}
                  onClick={(event) => {
                    event.preventDefault();
                    go(index);
                  }}
                  onMouseMove={() => setActive(index)}
                >
                  <span className="ic">
                    <Icon name={item.icon} size={15} />
                  </span>
                  <span className="t">{item.title}</span>
                  {item.note ? <small>{item.note}</small> : null}
                </a>
              </div>
            );
          })}
        </div>
        <div className="kp-f">
          <span>
            <kbd>↑</kbd>
            <kbd>↓</kbd> tanlash
          </span>
          <span>
            <kbd>↵</kbd> ochish
          </span>
          <span>
            <kbd>esc</kbd> yopish
          </span>
        </div>
      </div>
    </div>
  );
}
