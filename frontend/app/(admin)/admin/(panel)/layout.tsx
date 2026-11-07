import { Suspense } from "react";
import { AdminProvider } from "@/components/admin/session";
import { AdminShell } from "@/components/admin/shell";

export default function PanelLayout({ children }: { children: React.ReactNode }) {
  return (
    <Suspense fallback={<div className="adm" data-pane="detail" aria-busy="true" />}>
      <AdminProvider>
        <AdminShell>{children}</AdminShell>
      </AdminProvider>
    </Suspense>
  );
}
