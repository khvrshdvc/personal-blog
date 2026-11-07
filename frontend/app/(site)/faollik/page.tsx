import type { Metadata } from "next";
import { FaollikTab } from "@/components/dev/faollik-tab";

export const metadata: Metadata = {
  title: "Faollik — Utkirbek",
};

export default function Page() {
  return <FaollikTab />;
}
