import { createHash, timingSafeEqual } from "node:crypto";
import { revalidateTag } from "next/cache";
import { NextResponse } from "next/server";
import { clientKey, limited } from "@/lib/rate-limit";

const TAG = /^(posts|projects|settings|post:[A-Za-z0-9._-]{1,200})$/;

function digest(value: string): Buffer {
  return createHash("sha256").update(value).digest();
}

export async function POST(request: Request) {
  const expected = process.env.REVALIDATE_SECRET;

  if (!expected) {
    return NextResponse.json({ message: "Revalidate oʻchirilgan." }, { status: 503 });
  }

  if (limited(`revalidate:${clientKey(request)}`, 60)) {
    return NextResponse.json({ message: "Juda koʻp soʻrov." }, { status: 429 });
  }

  let body: { secret?: unknown; tags?: unknown };

  try {
    const parsed: unknown = await request.json();

    if (typeof parsed !== "object" || parsed === null || Array.isArray(parsed)) throw new Error("body");

    body = parsed as typeof body;
  } catch {
    return NextResponse.json({ message: "Notoʻgʻri soʻrov." }, { status: 400 });
  }

  if (typeof body.secret !== "string" || !timingSafeEqual(digest(body.secret), digest(expected))) {
    return NextResponse.json({ message: "Ruxsat yoʻq." }, { status: 401 });
  }

  const tags = body.tags;

  if (!Array.isArray(tags) || tags.length === 0 || tags.length > 20 || !tags.every((tag) => typeof tag === "string" && TAG.test(tag))) {
    return NextResponse.json({ message: "Teglar notoʻgʻri." }, { status: 400 });
  }

  for (const tag of tags as string[]) {
    revalidateTag(tag, { expire: 0 });
  }

  return NextResponse.json({ revalidated: tags });
}
