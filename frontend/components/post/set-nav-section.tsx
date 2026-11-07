"use client";

import { useEffect } from "react";
import { setNavSection, type NavSection } from "@/lib/nav-section";

export function SetNavSection({ section }: { section: NavSection }) {
  useEffect(() => {
    setNavSection(section);

    return () => setNavSection(null);
  }, [section]);

  return null;
}
