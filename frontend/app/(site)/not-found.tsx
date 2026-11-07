import type { Metadata } from "next";
import { ErrorPage } from "@/components/error-page";

export const metadata: Metadata = {
  title: "Topilmadi — Utkirbek",
  robots: { index: false, follow: false },
};

export default function NotFound() {
  return <ErrorPage heading="Sahifa topilmadi" text="Bu manzilda hech narsa yoʻq yoki yozuv oʻchirilgan." />;
}
