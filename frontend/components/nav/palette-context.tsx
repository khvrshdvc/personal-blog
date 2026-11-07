"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { CommandPalette } from "@/components/nav/command-palette";

interface PaletteContextValue {
  open: () => void;
}

const PaletteContext = createContext<PaletteContextValue>({ open: () => {} });

export function usePalette() {
  return useContext(PaletteContext);
}

export function PaletteProvider({ children }: { children: React.ReactNode }) {
  const [isOpen, setOpen] = useState(false);

  const open = useCallback(() => setOpen(true), []);
  const close = useCallback(() => setOpen(false), []);

  useEffect(() => {
    function onKey(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        setOpen((value) => !value);
      }
    }

    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, []);

  const value = useMemo(() => ({ open }), [open]);

  return (
    <PaletteContext.Provider value={value}>
      {children}
      {isOpen ? <CommandPalette onClose={close} /> : null}
    </PaletteContext.Provider>
  );
}
