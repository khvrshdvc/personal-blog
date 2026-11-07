"use client";

import { useEffect, useRef } from "react";

export function ReadProgress() {
  const bar = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function update() {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      const progress = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;

      if (bar.current) bar.current.style.width = `${progress * 100}%`;
    }

    update();
    window.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);

    return () => {
      window.removeEventListener("scroll", update);
      window.removeEventListener("resize", update);
    };
  }, []);

  return <div ref={bar} className="progress" aria-hidden="true" />;
}
