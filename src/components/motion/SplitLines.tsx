"use client";

import { useRef } from "react";
import { motion, useInView } from "framer-motion";
import clsx from "clsx";

interface SplitLinesProps {
  lines: string[];
  className?: string;
  lineClassName?: string;
  delay?: number;
  stagger?: number;
  once?: boolean;
}

/**
 * Renders each line inside an overflow-hidden mask and reveals it with a
 * confident upward wipe. Lines are chosen explicitly by the caller so large
 * display headlines can be composed and broken exactly where intended.
 */
export default function SplitLines({
  lines,
  className,
  lineClassName,
  delay = 0,
  stagger = 0.08,
  once = true,
}: SplitLinesProps) {
  const ref = useRef<HTMLDivElement>(null);
  const inView = useInView(ref, { once, margin: "-10% 0px -10% 0px" });

  return (
    <div ref={ref} className={className}>
      {lines.map((line, i) => (
        <span key={i} className={clsx("reveal-line", lineClassName)}>
          <motion.span
            className="inline-block will-change-transform"
            initial={{ y: "110%" }}
            animate={inView ? { y: "0%" } : { y: "110%" }}
            transition={{
              duration: 0.9,
              delay: delay + i * stagger,
              ease: [0.16, 1, 0.3, 1],
            }}
          >
            {line}
          </motion.span>
        </span>
      ))}
    </div>
  );
}
