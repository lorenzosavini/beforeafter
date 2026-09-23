"use client";

import { useEffect, useState } from "react";
import { motion } from "framer-motion";
import SplitLines from "@/components/motion/SplitLines";
import { smoothScrollTo } from "@/lib/scroll";

const EASE = [0.16, 1, 0.3, 1] as const;

export default function Hero() {
  const [settled, setSettled] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => setSettled(true), 1150);
    return () => clearTimeout(timer);
  }, []);

  return (
    <section
      id="top"
      className="relative flex min-h-[100svh] flex-col justify-between overflow-hidden bg-night px-5 pb-10 pt-28 text-paper sm:px-8"
    >
      <motion.div
        className="font-mono-label flex items-center justify-between text-paper/60"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.6, delay: 0.4 }}
      >
        <span>Roma · Milano · Napoli</span>
        <span>Est. 2024</span>
      </motion.div>

      <div className="flex-1 flex flex-col justify-center">
        <SplitLines
          lines={["L'esperienza inizia prima.", "Continua dopo."]}
          className="font-display text-[13vw] leading-[0.92] text-flame sm:text-[9vw] lg:text-[7vw]"
          stagger={0.12}
          delay={0.1}
        />

        <span className="reveal-line mt-8 block sm:mt-10">
          <motion.span
            className="font-serif-italic block max-w-xl text-2xl leading-tight text-paper/70 sm:text-3xl"
            initial={{ y: "100%" }}
            animate={settled ? { y: "0%" } : { y: "100%" }}
            transition={{ duration: 0.8, ease: EASE, delay: 0.1 }}
          >
            Un collettivo di eventi ed esperienze notturne.
          </motion.span>
        </span>
      </div>

      <motion.button
        type="button"
        onClick={() => smoothScrollTo("#next-event", 0)}
        className="font-mono-label group flex items-center gap-3 self-start text-paper/70"
        initial={{ opacity: 0, y: 8 }}
        animate={settled ? { opacity: 1, y: 0 } : { opacity: 0, y: 8 }}
        transition={{ duration: 0.6, delay: 0.4 }}
      >
        <span className="flex h-9 w-9 items-center justify-center rounded-full border border-paper/30 transition-colors duration-300 group-hover:border-paper">
          <motion.span
            aria-hidden
            className="block h-3 w-px origin-top bg-paper"
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
