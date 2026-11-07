import type { Metadata } from "next";
import { LoyihalarTab } from "@/components/dev/loyihalar-tab";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Loyihalar — Utkirbek",
  alternates: pageAlternates("/loyihalar"),
};

export default function Page() {
  return <LoyihalarTab />;
}
