"use client";

import { useEffect, useState } from "react";
import { AnimatePresence } from "framer-motion";
import Link from "next/link";
import BeforeAfterLogo from "@/components/logo/BeforeAfterLogo";
import MobileMenu from "@/components/nav/MobileMenu";
import { setCursor } from "@/lib/cursor";
import { smoothScrollTo } from "@/lib/scroll";

const LINKS = [
  { label: "Eventi", href: "#archive" },
  { label: "Memorie", href: "#memories" },
  { label: "Chi siamo", href: "#manifesto" },
  { label: "Contatti", href: "#footer" },
];

export default function Navigation() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [time, setTime] = useState<string | null>(null);

  useEffect(() => {
    const update = () =>
      setTime(
        new Intl.DateTimeFormat("it-IT", {
          hour: "2-digit",
          minute: "2-digit",
          timeZone: "Europe/Rome",
        }).format(new Date())
      );
    update();
    const id = setInterval(update, 30_000);
    return () => clearInterval(id);
  }, []);

  useEffect(() => {
    document.documentElement.style.overflow = menuOpen ? "hidden" : "";
  }, [menuOpen]);

  const handleNavClick =
    (href: string) => (e: React.MouseEvent<HTMLAnchorElement>) => {
      e.preventDefault();
      setMenuOpen(false);
      smoothScrollTo(href, 0);
    };

  return (
    <>
      <header className="fixed inset-x-0 top-0 z-50 mix-blend-difference">
        <div className="flex items-center justify-between px-5 py-5 text-white sm:px-8 sm:py-6">
          <Link
            href="/"
            onClick={handleNavClick("#top")}
            onMouseEnter={() => setCursor({ variant: "next", label: "Home" })}
            onMouseLeave={() => setCursor(null)}
          >
            <BeforeAfterLogo variant="solid" className="h-6 w-auto sm:h-7" />
          </Link>

          <nav className="hidden items-center gap-8 font-mono-label md:flex">
            {LINKS.map((link) => (
              <a
                key={link.href}
                href={link.href}
                onClick={handleNavClick(link.href)}
                onMouseEnter={() => setCursor({ variant: "view" })}
                onMouseLeave={() => setCursor(null)}
                className="relative py-1 after:absolute after:inset-x-0 after:bottom-0 after:h-px after:origin-left after:scale-x-0 after:bg-white after:transition-transform after:duration-300 after:ease-out hover:after:scale-x-100"
              >
                {link.label}
              </a>
            ))}
            {time && (
              <span className="font-mono-label opacity-70">{time} · ROMA</span>
            )}
          </nav>

          <button
            type="button"
            className="font-mono-label flex items-center gap-2 md:hidden"
            onClick={() => setMenuOpen((v) => !v)}
            aria-expanded={menuOpen}
            aria-label="Toggle menu"
          >
            {menuOpen ? "Close" : "Menu"}
          </button>
        </div>
      </header>

      <AnimatePresence>
        {menuOpen && (
          <MobileMenu
            links={LINKS}
            onNavigate={handleNavClick}
            onClose={() => setMenuOpen(false)}
          />
        )}
      </AnimatePresence>
    </>
  );
}
