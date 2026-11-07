"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { Icon } from "@/components/icon";
import { AdminApiError, adminApi } from "@/lib/admin/api";

export function LoginForm() {
  const router = useRouter();
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);
  const [shake, setShake] = useState(0);

  useEffect(() => {
    adminApi
      .me()
      .then(() => router.replace("/admin"))
      .catch(() => {});
  }, [router]);

  async function submit(event: React.FormEvent) {
    event.preventDefault();

    if (busy) return;

    setBusy(true);
    setError("");

    try {
      await adminApi.login(password);
      router.replace("/admin");
    } catch (failure) {
      setShake((value) => value + 1);

      if (failure instanceof AdminApiError && failure.status === 429) {
        setError("Juda koʻp urinish. Birozdan keyin qayta urinib koʻring.");
      } else if (failure instanceof AdminApiError) {
        setError(failure.first("password"));
      } else {
        setError("Serverga ulanib boʻlmadi.");
      }

      setBusy(false);
    }
  }

  return (
    <div className="login">
      <form key={shake} className={`c lg-c${shake ? " shake" : ""}`} onSubmit={submit} noValidate>
        <span className="lg-ic" aria-hidden="true">
          <Icon name="lock" />
        </span>
        <h1>Admin panel</h1>
        <p className="lg-s">Yozuvlar, loyihalar va sayt sozlamalarini boshqarish uchun kiring.</p>
        <label className="fld">
          <span>Parol</span>
          <span className="ipx">
            <em>
              <Icon name="lock" />
            </em>
            <input
              className="inp"
              type="password"
              id="lpw"
              name="password"
              placeholder="••••••"
              autoComplete="current-password"
              enterKeyHint="go"
              autoFocus
              required
              value={password}
              onChange={(event) => setPassword(event.target.value)}
            />
          </span>
        </label>
        <p className="lg-err" aria-live="polite">
          {error}
        </p>
        <button className="lg-btn" type="submit" disabled={busy || password === ""}>
          Kirish <Icon name="arrowUR" />
        </button>
        <div className="lg-f">
          <Link href="/">
            <Icon name="chevL" /> Saytga qaytish
          </Link>
        </div>
      </form>
    </div>
  );
}
