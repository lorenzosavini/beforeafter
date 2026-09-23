"use client";

import { useRef, type ReactNode } from "react";
import { motion, useInView } from "framer-motion";
import clsx from "clsx";

interface RevealImageProps {
  children: ReactNode;
  className?: string;
  direction?: "up" | "down" | "left" | "right";
  once?: boolean;
  delay?: number;
}

const CLIP_FROM: Record<NonNullable<RevealImageProps["direction"]>, string> = {
  up: "inset(100% 0 0 0)",
  down: "inset(0 0 100% 0)",
  left: "inset(0 100% 0 0)",
  right: "inset(0 0 0 100%)",
};

/** Mask-reveal wrapper for imagery/media — the frame opens once, then settles. */
export default function RevealImage({
  children,
  className,
  direction = "up",
  once = true,
  delay = 0,
}: RevealImageProps) {
  const ref = useRef<HTMLDivElement>(null);
  const inView = useInView(ref, { once, margin: "-5% 0px -5% 0px" });

  return (
    <div ref={ref} className={clsx("overflow-hidden", className)}>
      <motion.div
        className="h-full w-full"
        initial={{ clipPath: CLIP_FROM[direction], scale: 1.08 }}
        animate={
          inView
            ? { clipPath: "inset(0 0 0 0)", scale: 1 }
            : { clipPath: CLIP_FROM[direction], scale: 1.08 }
        }
        transition={{
          duration: 1.1,
          delay,
          ease: [0.76, 0, 0.24, 1],
        }}
      >
        {children}
      </motion.div>
    </div>
  );
}
