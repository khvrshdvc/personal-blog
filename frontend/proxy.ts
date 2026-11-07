import { NextResponse, type NextRequest } from "next/server";
import { source } from "@/lib/api/source";

export async function proxy(request: NextRequest) {
  const slug = decodeURIComponent(request.nextUrl.pathname.split("/")[2] ?? "");

  try {
    if ((await source.getPost(slug)) === null) {
      return NextResponse.rewrite(new URL("/yoq-sahifa", request.url));
    }
  } catch {}

  return NextResponse.next();
}

export const config = {
  matcher: "/post/:slug",
};
