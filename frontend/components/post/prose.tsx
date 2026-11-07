"use client";

import { useRef } from "react";

export function Prose({ html }: { html: string }) {
  const timers = useRef(new WeakMap<Element, ReturnType<typeof setTimeout>>());

  async function onClick(event: React.MouseEvent<HTMLDivElement>) {
    const button = event.target instanceof Element ? event.target.closest<HTMLButtonElement>(".copy-code") : null;

    if (!button) return;

    const code = button.closest(".code-wrap")?.querySelector("pre")?.textContent ?? "";
    const label = button.querySelector("span");

    try {
      await navigator.clipboard.writeText(code);
    } catch {
      return;
    }

    if (!label) return;

    label.textContent = "Nusxalandi";
    clearTimeout(timers.current.get(button));
    timers.current.set(
      button,
      setTimeout(() => {
        label.textContent = "Nusxalash";
      }, 1600),
    );
  }

  return <div className="prose" onClick={onClick} dangerouslySetInnerHTML={{ __html: html }} />;
}
