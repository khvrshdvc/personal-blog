"use client";

import { useEffect, useState } from "react";

function tashkent(): string {
  return new Date().toLocaleTimeString("en-GB", { timeZone: "Asia/Tashkent", hour: "2-digit", minute: "2-digit" });
}

export function Clock({ initial }: { initial: string }) {
  const [time, setTime] = useState(initial);

  useEffect(() => {
    const interval = setInterval(() => setTime(tashkent()), 5000);

    return () => clearInterval(interval);
  }, []);

  return (
    <span className="tm" aria-label={`Toshkentda soat ${time}`}>
      {time}
    </span>
  );
}
