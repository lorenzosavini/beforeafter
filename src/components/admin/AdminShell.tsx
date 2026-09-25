"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import BeforeAfterLogo from "@/components/logo/BeforeAfterLogo";

export default function AdminShell({
  children,
  title,
}: {
  children: React.ReactNode;
  title: string;
}) {
  const router = useRouter();

  const handleLogout = async () => {
    await fetch("/api/admin/logout", { method: "POST" });
    router.push("/admin/login");
    router.refresh();
  };

  return (
    <div className="min-h-svh bg-night text-paper">
      <header className="flex items-center justify-between border-b border-paper/15 px-5 py-5 sm:px-8">
        <Link href="/admin" className="flex items-center gap-4">
          <BeforeAfterLogo variant="solid" className="h-6 w-auto text-paper" />
          <span className="font-mono-label text-paper/50">Admin</span>
        </Link>
        <div className="flex items-center gap-6">
          <Link href="/" className="font-mono-label text-paper/60 hover:text-paper">
            Vedi sito
          </Link>
          <button
            type="button"
            onClick={handleLogout}
            className="font-mono-label text-paper/60 hover:text-paper"
          >
            Esci
          </button>
        </div>
      </header>
      <main className="px-5 py-10 sm:px-8 sm:py-14">
        <h1 className="font-display mb-10 text-4xl sm:text-5xl">{title}</h1>
        {children}
      </main>
    </div>
  );
}
