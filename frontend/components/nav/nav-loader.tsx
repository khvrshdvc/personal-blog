import { connection } from "next/server";
import { SiteNav, type NavData } from "@/components/nav/site-nav";
import { api } from "@/lib/api";

async function load(): Promise<NavData> {
  try {
    const [settings, counts] = await Promise.all([api.getSettings(), api.getCounts()]);

    return { counts, open: settings.open };
  } catch {
    return { counts: null, open: null };
  }
}

export async function NavLoader() {
  await connection();

  return <SiteNav {...(await load())} />;
}
