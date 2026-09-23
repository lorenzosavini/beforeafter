"use client";

import BeforeAfterMark from "@/components/logo/BeforeAfterMark";
import { motion } from "framer-motion";

interface BeforeAfterLogoRevealProps {
  className?: string;
  onSettled?: () => void;
}

const EASE = [0.76, 0, 0.24, 1] as const;

/**
 * The opening-sequence mark: the logo is revealed left-to-right, as if the
 * stroke is being laid down — echoing the mark's own hand-drawn, dripping
 * ink construction — then settles and stays still.
 */
export default function BeforeAfterLogoReveal({
  className,
  onSettled,
}: BeforeAfterLogoRevealProps) {
  return (
    <span className={"inline-block overflow-hidden " + (className ?? "")}>
      <motion.span
        className="block"
        initial={{ clipPath: "inset(0 100% 0 0)" }}
        animate={{ clipPath: "inset(0 0% 0 0)" }}
        transition={{ duration: 1.2, ease: EASE, delay: 0.2 }}
        onAnimationComplete={onSettled}
      >
        <BeforeAfterMark className="block h-auto w-full" />
      </motion.span>
    </span>
  );
}
