"use client";

import { useCallback, useState } from "react";
import { useAdmin } from "@/components/admin/session";
import { useNotify } from "@/components/toast";
import { AdminApiError, adminApi } from "@/lib/admin/api";

export function useSyncProjects() {
  const { loadProjects } = useAdmin();
  const notify = useNotify();
  const [busy, setBusy] = useState(false);

  const run = useCallback(async () => {
    if (busy) return;

    setBusy(true);

    try {
      const result = await adminApi.syncProjects();
      await loadProjects();
      notify(
        result.created > 0
          ? `${result.created} ta yangi loyiha GitHub'dan qo'shildi`
          : result.updated > 0
            ? `${result.updated} ta loyiha maʼlumoti to'ldirildi`
            : "GitHub'da yangi loyiha topilmadi",
      );
    } catch (error) {
      if (error instanceof AdminApiError && error.errors.github) {
        notify(error.first("github"), { label: "Sozlamalar", href: "/admin/settings" });
      } else if (error instanceof AdminApiError && error.status === 502) {
        notify("GitHub javob bermadi. Birozdan keyin urinib ko'ring.");
      } else {
        notify("Yuklab bo'lmadi");
      }
    } finally {
      setBusy(false);
    }
  }, [busy, loadProjects, notify]);

  return { busy, run };
}
