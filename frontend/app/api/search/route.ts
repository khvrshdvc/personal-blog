import { NextRequest, NextResponse } from "next/server";
import { source } from "@/lib/api/source";
import { clientKey, limited } from "@/lib/rate-limit";

export async function GET(request: NextRequest) {
  if (limited(`search:${clientKey(request)}`, 120)) {
    return NextResponse.json({ message: "Juda koʻp soʻrov." }, { status: 429 });
  }

  const q = (request.nextUrl.searchParams.get("q") ?? "").slice(0, 100);

  try {
    return NextResponse.json(await source.search(q));
  } catch {
    return NextResponse.json({ message: "Qidiruv ishlamayapti." }, { status: 502 });
  }
}
