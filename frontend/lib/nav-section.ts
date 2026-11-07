import { useSyncExternalStore } from "react";

export type NavSection = "fikrlar" | "dasturlash" | null;

let current: NavSection = null;
const listeners = new Set<() => void>();

export function setNavSection(section: NavSection) {
  if (current === section) return;

  current = section;
  listeners.forEach((listener) => listener());
}

function subscribe(listener: () => void) {
  listeners.add(listener);

  return () => {
    listeners.delete(listener);
  };
}

export function useNavSection(): NavSection {
  return useSyncExternalStore(
    subscribe,
    () => current,
    () => null,
  );
}
