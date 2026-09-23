"use client";

import { useRef } from "react";
import { motion, useInView } from "framer-motion";
import clsx from "clsx";

interface AnimatedWordsProps {
  text: string;
  className?: string;
  delay?: number;
  stagger?: number;
  once?: boolean;
}

/** Word-by-word reveal for running text (manifesto, editorial copy). */
export default function AnimatedWords({
  text,
  className,
  delay = 0,
  stagger = 0.028,
  once = true,
}: AnimatedWordsProps) {
  const ref = useRef<HTMLParagraphElement>(null);
  const inView = useInView(ref, { once, margin: "-10% 0px -10% 0px" });
  const words = text.split(" ");

  return (
    <p ref={ref} className={clsx("flex flex-wrap", className)}>
      {words.map((word, i) => (
        <span key={i} className="reveal-line mr-[0.28em]">
          <motion.span
            className="inline-block will-change-transform"
            initial={{ y: "100%", opacity: 0 }}
            animate={inView ? { y: "0%", opacity: 1 } : { y: "100%", opacity: 0 }}
            transition={{
              duration: 0.6,
              delay: delay + i * stagger,
              ease: [0.16, 1, 0.3, 1],
            }}
          >
            {word}
          </motion.span>
        </span>
      ))}
    </p>
  );
}
