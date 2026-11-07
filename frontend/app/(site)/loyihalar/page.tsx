import type { Metadata } from "next";
import { LoyihalarTab } from "@/components/dev/loyihalar-tab";

export const metadata: Metadata = {
  title: "Loyihalar — Utkirbek",
};

export default function Page() {
  return <LoyihalarTab />;
}
