import type { Metadata } from "next";
import { ConfirmProvider } from "@/components/admin/confirm";
import { ToastProvider } from "@/components/toast";
import "../admin.css";

export const metadata: Metadata = {
  title: "Admin — Utkirbek",
  robots: { index: false, follow: false },
};

export default function AdminRootLayout({ children }: { children: React.ReactNode }) {
  return (
    <ToastProvider>
      <ConfirmProvider>
        <div id="admin">{children}</div>
      </ConfirmProvider>
    </ToastProvider>
  );
}
