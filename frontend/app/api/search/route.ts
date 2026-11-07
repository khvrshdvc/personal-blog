import { NextRequest, NextResponse } from "next/server";
import { source } from "@/lib/api/source";

export async function GET(request: NextRequest) {
  const q = (request.nextUrl.searchParams.get("q") ?? "").slice(0, 100);

  try {
    return NextResponse.json(await source.search(q));
  } catch {
    return NextResponse.json({ message: "Qidiruv ishlamayapti." }, { status: 502 });
  }
}
