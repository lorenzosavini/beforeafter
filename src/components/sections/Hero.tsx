"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import BeforeAfterLogoReveal from "@/components/logo/BeforeAfterLogoReveal";
import { smoothScrollTo } from "@/lib/scroll";

const EASE = [0.16, 1, 0.3, 1] as const;

export default function Hero() {
  const [settled, setSettled] = useState(false);

  return (
    <section
      id="top"
      className="relative flex min-h-[100svh] flex-col justify-between overflow-hidden bg-paper px-5 pb-10 pt-28 text-ink sm:px-8"
    >
      <motion.div
        className="font-mono-label flex items-center justify-between text-ink-soft"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.6, delay: 1.6 }}
      >
        <span>Roma · Milano · Napoli</span>
        <span>Est. 2024</span>
      </motion.div>

      <div className="flex-1 flex flex-col justify-center">
        <BeforeAfterLogoReveal
          className="w-[92vw] text-ink sm:w-[70vw] lg:w-[58vw]"
          onSettled={() => setSettled(true)}
        />

        <div className="mt-8 max-w-xl sm:mt-10">
          <span className="reveal-line block">
            <motion.span
              className="font-serif-italic block text-2xl leading-tight text-ink-soft sm:text-3xl"
              initial={{ y: "100%" }}
              animate={settled ? { y: "0%" } : { y: "100%" }}
              transition={{ duration: 0.8, ease: EASE }}
            >
              Un collettivo di eventi ed esperienze notturne.
            </motion.span>
          </span>
          <span className="reveal-line mt-1 block">
            <motion.span
              className="font-serif-italic block text-2xl leading-tight text-ink-soft sm:text-3xl"
              initial={{ y: "100%" }}
              animate={settled ? { y: "0%" } : { y: "100%" }}
              transition={{ duration: 0.8, ease: EASE, delay: 0.08 }}
            >
              L&apos;esperienza inizia prima. Continua dopo.
            </motion.span>
          </span>
        </div>
      </div>

      <motion.button
        type="button"
        onClick={() => smoothScrollTo("#next-event", 0)}
        className="font-mono-label group flex items-center gap-3 self-start text-ink-soft"
        initial={{ opacity: 0, y: 8 }}
        animate={settled ? { opacity: 1, y: 0 } : { opacity: 0, y: 8 }}
        transition={{ duration: 0.6, delay: 0.4 }}
      >
        <span className="flex h-9 w-9 items-center justify-center rounded-full border border-ink-soft/30 transition-colors duration-300 group-hover:border-ink-soft">
          <motion.span
            aria-hidden
            className="block h-3 w-px origin-top bg-ink-soft"
            initial={{ scaleY: 0 }}
            animate={settled ? { scaleY: 1 } : { scaleY: 0 }}
            transition={{ duration: 0.5, delay: 0.7, ease: EASE }}
          />
        </span>
        Scorri
      </motion.button>
    </section>
  );
}
