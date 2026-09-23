"use client";

import { motion } from "framer-motion";

interface BeforeAfterLogoRevealProps {
  className?: string;
  onSettled?: () => void;
}

const EASE = [0.16, 1, 0.3, 1] as const;

/**
 * The opening-sequence mark: "before" wipes in first, holds, then "after"
 * materializes as a solid inverted panel that slides in from the trailing
 * edge — the brand's transformation enacted once, on arrival, then it
 * settles and stays still.
 */
export default function BeforeAfterLogoReveal({
  className,
  onSettled,
}: BeforeAfterLogoRevealProps) {
  return (
    <span
      className={
        "font-display inline-flex items-baseline leading-none select-none " +
        (className ?? "")
      }
    >
      <span className="reveal-line inline-block">
        <motion.span
          className="inline-block will-change-transform"
          initial={{ y: "110%" }}
          animate={{ y: "0%" }}
          transition={{ duration: 0.9, ease: EASE, delay: 0.15 }}
        >
          before
        </motion.span>
      </span>

      <motion.span
        className="ml-[0.06em] inline-block overflow-hidden bg-ink"
        initial={{ width: 0 }}
        animate={{ width: "auto" }}
        transition={{ duration: 0.7, ease: EASE, delay: 1.05 }}
        onAnimationComplete={onSettled}
      >
        <motion.span
          className="inline-block px-[0.14em] text-paper will-change-transform"
          initial={{ opacity: 0, x: 12 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, ease: EASE, delay: 1.2 }}
        >
          after
        </motion.span>
      </motion.span>
    </span>
  );
}
