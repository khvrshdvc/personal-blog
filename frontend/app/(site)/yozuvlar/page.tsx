import type { Metadata } from "next";
import { YozuvlarTab } from "@/components/dev/yozuvlar-tab";

export const metadata: Metadata = {
  title: "Dasturlash — Utkirbek",
};

export default function Page() {
  return <YozuvlarTab />;
}
