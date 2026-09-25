"use client";

import { Suspense, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import BeforeAfterLogo from "@/components/logo/BeforeAfterLogo";

function LoginForm() {
  const router = useRouter();
  const params = useSearchParams();
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    const res = await fetch("/api/admin/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ password }),
    });
    setLoading(false);
    if (!res.ok) {
      setError("Password errata.");
      return;
    }
    router.push(params.get("next") || "/admin");
    router.refresh();
  };

  return (
    <div className="flex min-h-svh flex-col items-center justify-center bg-night px-5 text-paper">
      <BeforeAfterLogo variant="solid" className="mb-10 h-10 w-auto text-paper" />
      <form
        onSubmit={handleSubmit}
        className="flex w-full max-w-xs flex-col gap-4"
      >
        <label className="font-mono-label text-paper/60">
          Password admin
          <input
            type="password"
            autoFocus
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="font-sans mt-2 w-full rounded-none border border-paper/25 bg-transparent px-4 py-3 text-base text-paper outline-none transition-colors focus:border-flame"
          />
        </label>
        {error && (
          <p className="font-mono-label text-flame">{error}</p>
        )}
        <button
          type="submit"
          disabled={loading || !password}
          className="font-mono-label mt-2 border border-paper/30 py-3 text-paper transition-colors hover:border-paper disabled:opacity-40"
        >
          {loading ? "..." : "Entra"}
        </button>
      </form>
    </div>
  );
}

export default function AdminLoginPage() {
  return (
    <Suspense fallback={null}>
      <LoginForm />
    </Suspense>
  );
}
