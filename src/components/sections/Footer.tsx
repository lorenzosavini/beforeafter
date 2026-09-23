"use client";

import BeforeAfterLogo from "@/components/logo/BeforeAfterLogo";
import MagneticElement from "@/components/motion/MagneticElement";
import { smoothScrollTo } from "@/lib/scroll";

export default function Footer() {
  return (
    <footer
      id="footer"
      className="relative flex min-h-[80svh] flex-col justify-between bg-ink px-5 pb-8 pt-20 text-paper sm:px-8 sm:pt-28"
    >
      <div className="font-mono-label flex items-center justify-between text-paper/60">
        <span>beforeafter © {new Date().getFullYear()}</span>
        <span>Roma · Italia</span>
      </div>

      <div className="flex-1 flex flex-col items-start justify-center gap-8 py-14">
        <p className="font-serif-italic max-w-lg text-2xl leading-snug text-paper/80 sm:text-3xl">
          Il prossimo capitolo è già in scrittura.
        </p>

        <MagneticElement>
          <button
            type="button"
            onClick={() => smoothScrollTo("#top", 0)}
            className="font-mono-label group flex items-center gap-3 rounded-full border border-paper/30 px-6 py-3 transition-colors duration-300 hover:border-paper"
          >
            Torna all&apos;inizio
            <span className="transition-transform duration-300 group-hover:-translate-y-1">
              ↑
            </span>
          </button>
        </MagneticElement>
      </div>

      <BeforeAfterLogo className="w-[92vw] h-auto text-flame sm:w-[48vw]" />

      <div className="font-mono-label mt-10 flex flex-col gap-2 border-t border-paper/15 pt-6 text-paper/50 sm:mt-14 sm:flex-row sm:items-center sm:justify-between">
        <span>hello@beforeafter.it</span>
        <div className="flex gap-6">
          <a href="#" className="hover:text-paper">
            Instagram
          </a>
          <a href="#" className="hover:text-paper">
            TikTok
          </a>
        </div>
      </div>
    </footer>
  );
}
