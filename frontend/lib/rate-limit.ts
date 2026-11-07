interface Bucket {
  count: number;
  reset: number;
}

const buckets = new Map<string, Bucket>();

export function clientKey(request: Request): string {
  return request.headers.get("x-forwarded-for")?.split(",")[0]?.trim() || "local";
}

export function limited(key: string, limit: number, windowMs = 60000, now = Date.now()): boolean {
  if (buckets.size > 5000) {
    for (const [name, bucket] of buckets) {
      if (bucket.reset <= now) buckets.delete(name);
    }
  }

  const bucket = buckets.get(key);

  if (!bucket || bucket.reset <= now) {
    buckets.set(key, { count: 1, reset: now + windowMs });

    return false;
  }

  bucket.count += 1;

  return bucket.count > limit;
}
