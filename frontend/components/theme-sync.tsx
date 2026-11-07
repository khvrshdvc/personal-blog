"use client";

import { useEffect } from "react";
import { syncTheme, useThemeMode } from "@/lib/theme-store";

export function ThemeSync() {
  useThemeMode();

  useEffect(() => {
    syncTheme();
  }, []);

  return null;
}
