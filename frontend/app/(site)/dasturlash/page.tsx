import type { Metadata } from "next";
import { YozuvlarTab } from "@/components/dev/yozuvlar-tab";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Dasturlash — Utkirbek",
  alternates: pageAlternates("/dasturlash"),
};

export default function Page() {
  return <YozuvlarTab />;
}
