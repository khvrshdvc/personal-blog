import type { Metadata } from "next";
import { FaollikTab } from "@/components/dev/faollik-tab";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Faollik — Utkirbek",
  alternates: pageAlternates("/faollik"),
};

export default function Page() {
  return <FaollikTab />;
}
