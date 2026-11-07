"use client";

import { useEffect } from "react";

function hydrated(element: HTMLElement): boolean {
  return Object.keys(element).some((key) => key.startsWith("__reactProps$"));
}

export function Glow() {
  useEffect(() => {
    if (!window.matchMedia("(hover: hover) and (pointer: fine)").matches) return;

    let current: HTMLElement | null = null;

    function onMove(event: PointerEvent) {
      const found = event.target instanceof Element ? event.target.closest<HTMLElement>("#site .c") : null;
      const target = found && hydrated(found) ? found : null;

      if (current && current !== target) {
        current.style.removeProperty("--x");
        current.style.removeProperty("--y");
      }

      current = target;

      if (target) {
        const rect = target.getBoundingClientRect();
        target.style.setProperty("--x", `${(event.clientX - rect.left).toFixed(0)}px`);
        target.style.setProperty("--y", `${(event.clientY - rect.top).toFixed(0)}px`);
      }
    }

    document.addEventListener("pointermove", onMove, { passive: true });

    return () => document.removeEventListener("pointermove", onMove);
  }, []);

  return null;
}
