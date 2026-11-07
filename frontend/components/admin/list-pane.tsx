"use client";

import { useState } from "react";
import { Icon } from "@/components/icon";

export function MinusIcon() {
  return (
    <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="12" cy="12" r="10" fill="currentColor" />
      <path d="M7.5 12h9" fill="none" stroke="#fff" strokeWidth="2.4" strokeLinecap="round" />
    </svg>
  );
}

interface ListPaneProps {
  title: string;
  count: number | null;
  newHref: string;
  newLabel: string;
  search?: { value: string; onChange: (value: string) => void; label: string };
  children: (state: { editing: boolean; revealed: number | null; reveal: (id: number | null) => void }) => React.ReactNode;
}

export function ListPane({ title, count, newHref, newLabel, search, children }: ListPaneProps) {
  const [editing, setEditing] = useState(false);
  const [revealed, setRevealed] = useState<number | null>(null);

  return (
    <section className={`a-list${editing ? " editing" : ""}`} aria-label="Roʻyxat">
      <header className="al-h">
        <div className="al-top">
          <h1>
            {title} <span>{count ?? ""}</span>
          </h1>
          <div className="al-act">
            <button
              className={`al-eb${editing ? " done" : ""}`}
              type="button"
              onClick={() => {
                setEditing((value) => !value);
                setRevealed(null);
              }}
            >
              {editing ? "Tayyor" : "Tahrirlash"}
            </button>
            <a className="al-nb" href={newHref} aria-label={newLabel} title={newLabel}>
              <Icon name="plus" />
            </a>
          </div>
        </div>
        {search ? (
          <label className="fs">
            <Icon name="search" />
            <span className="sr">{search.label}</span>
            <input type="search" placeholder="Qidirish" autoComplete="off" value={search.value} onChange={(event) => search.onChange(event.target.value)} />
          </label>
        ) : null}
      </header>
      <ul className="a-items">{children({ editing, revealed, reveal: setRevealed })}</ul>
    </section>
  );
}
