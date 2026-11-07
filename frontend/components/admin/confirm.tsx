"use client";

import { createContext, useCallback, useContext, useEffect, useRef, useState } from "react";

interface ConfirmOptions {
  title: string;
  message?: string;
  ok?: string;
  cancel?: string;
}

type Confirm = (options: ConfirmOptions) => Promise<boolean>;

const ConfirmContext = createContext<Confirm>(async () => false);

export function useConfirm(): Confirm {
  return useContext(ConfirmContext);
}

export function ConfirmProvider({ children }: { children: React.ReactNode }) {
  const [options, setOptions] = useState<ConfirmOptions | null>(null);
  const resolver = useRef<((value: boolean) => void) | null>(null);
  const okButton = useRef<HTMLButtonElement>(null);

  const confirm = useCallback<Confirm>((next) => {
    setOptions(next);

    return new Promise<boolean>((resolve) => {
      resolver.current = resolve;
    });
  }, []);

  const done = useCallback((value: boolean) => {
    resolver.current?.(value);
    resolver.current = null;
    setOptions(null);
  }, []);

  useEffect(() => {
    if (!options) return;

    okButton.current?.focus();

    function onKey(event: KeyboardEvent) {
      if (event.key === "Escape") done(false);
    }

    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, [options, done]);

  return (
    <ConfirmContext.Provider value={confirm}>
      {children}
      {options ? (
        <div
          className="alert-back"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) done(false);
          }}
        >
          <div className="alert glass" role="alertdialog" aria-modal="true" aria-labelledby="al-t">
            <h3 id="al-t">{options.title}</h3>
            {options.message ? <p>{options.message}</p> : null}
            <div className="alert-btns">
              <button type="button" onClick={() => done(false)}>
                {options.cancel ?? "Bekor qilish"}
              </button>
              <button ref={okButton} type="button" className="destructive" onClick={() => done(true)}>
                {options.ok ?? "Oʻchirish"}
              </button>
            </div>
          </div>
        </div>
      ) : null}
    </ConfirmContext.Provider>
  );
}
