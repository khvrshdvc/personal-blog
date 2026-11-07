import { connection } from "next/server";

export async function requestTime(): Promise<number> {
  await connection();

  return Date.now();
}
