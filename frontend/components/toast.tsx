"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from "react";

interface ToastAction {
  label: string;
  href: string;
}

interface ToastState {
  text: string;
  action?: ToastAction;
}

type Notify = (text: string, action?: ToastAction) => void;

const ToastContext = createContext<Notify>(() => {});

export function useNotify(): Notify {
  return useContext(ToastContext);
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toast, setToast] = useState<ToastState | null>(null);
  const [visible, setVisible] = useState(false);
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

  const notify = useCallback<Notify>((text, action) => {
    if (timer.current) clearTimeout(timer.current);
    setToast({ text, action });
    setVisible(true);
    timer.current = setTimeout(() => setVisible(false), 3200);
  }, []);

  useEffect(
    () => () => {
      if (timer.current) clearTimeout(timer.current);
    },
    [],
  );

  const value = useMemo(() => notify, [notify]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className={`toast${visible ? " show" : ""}`} role="status" aria-live="polite">
        {toast ? (
          <>
            <span>{toast.text}</span>
            {toast.action ? <a href={toast.action.href}>{toast.action.label}</a> : null}
          </>
        ) : null}
      </div>
    </ToastContext.Provider>
  );
}
