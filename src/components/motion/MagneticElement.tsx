"use client";

import { useRef, type ReactNode, type MouseEvent } from "react";
import { motion, useMotionValue, useSpring } from "framer-motion";
import { useIsFinePointer, useReducedMotion } from "@/lib/hooks/useReducedMotion";

interface MagneticElementProps {
  children: ReactNode;
  className?: string;
  strength?: number;
}

/** Pulls toward the pointer on approach, releases back to rest on leave. */
export default function MagneticElement({
  children,
  className,
  strength = 0.35,
}: MagneticElementProps) {
  const ref = useRef<HTMLDivElement>(null);
  const isFine = useIsFinePointer();
  const reducedMotion = useReducedMotion();

  const x = useMotionValue(0);
  const y = useMotionValue(0);
  const springX = useSpring(x, { damping: 18, stiffness: 220, mass: 0.6 });
  const springY = useSpring(y, { damping: 18, stiffness: 220, mass: 0.6 });

  if (!isFine || reducedMotion) {
    return <div className={className}>{children}</div>;
  }

  const handleMove = (e: MouseEvent<HTMLDivElement>) => {
    const bounds = ref.current?.getBoundingClientRect();
    if (!bounds) return;
    const relX = e.clientX - (bounds.left + bounds.width / 2);
    const relY = e.clientY - (bounds.top + bounds.height / 2);
    x.set(relX * strength);
    y.set(relY * strength);
  };

  const handleLeave = () => {
    x.set(0);
    y.set(0);
  };

  return (
    <motion.div
      ref={ref}
      className={className}
      style={{ x: springX, y: springY }}
      onMouseMove={handleMove}
      onMouseLeave={handleLeave}
    >
      {children}
    </motion.div>
  );
}
