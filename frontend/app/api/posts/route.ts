import { NextRequest, NextResponse } from "next/server";
import { source } from "@/lib/api/source";
import { clientKey, limited } from "@/lib/rate-limit";

export async function GET(request: NextRequest) {
  if (limited(`posts:${clientKey(request)}`, 120)) {
    return NextResponse.json({ message: "Juda koʻp soʻrov." }, { status: 429 });
  }

  const params = request.nextUrl.searchParams;
  const category = params.get("category");
  const q = (params.get("q") ?? "").slice(0, 100);

  try {
    const page = await source.getPosts({
      category: category === "fikr" || category === "dev" ? category : undefined,
      q,
      per_page: 50,
    });

    return NextResponse.json(page.data.map((post) => post.slug));
  } catch {
    return NextResponse.json({ message: "Qidiruv ishlamayapti." }, { status: 502 });
  }
}
