"use client";

import { useEffect, useState } from "react";
import { motion, useMotionValue, useSpring, AnimatePresence } from "framer-motion";
import { CURSOR_EVENT, type CursorPayload } from "@/lib/cursor";
import { useIsFinePointer, useReducedMotion } from "@/lib/hooks/useReducedMotion";

const LABELS: Record<CursorPayload["variant"], string> = {
  default: "",
  view: "View",
  open: "Open",
  next: "Next",
  drag: "Drag",
  event: "Event",
  image: "Expand",
};

export default function Cursor() {
  const isFine = useIsFinePointer();
  const reducedMotion = useReducedMotion();
  const [payload, setPayload] = useState<CursorPayload | null>(null);
  const [visible, setVisible] = useState(false);

  const x = useMotionValue(-100);
  const y = useMotionValue(-100);
  const springX = useSpring(x, { damping: 30, stiffness: 400, mass: 0.4 });
  const springY = useSpring(y, { damping: 30, stiffness: 400, mass: 0.4 });

  useEffect(() => {
    if (!isFine || reducedMotion) return;

    const onMove = (e: MouseEvent) => {
      x.set(e.clientX);
      y.set(e.clientY);
      setVisible(true);
    };
    const onLeave = () => setVisible(false);
    const onCursorEvent = (e: Event) => {
      const detail = (e as CustomEvent<CursorPayload | null>).detail;
      setPayload(detail ?? null);
    };

    window.addEventListener("mousemove", onMove);
    document.addEventListener("mouseleave", onLeave);
    window.addEventListener(CURSOR_EVENT, onCursorEvent);

    return () => {
      window.removeEventListener("mousemove", onMove);
      document.removeEventListener("mouseleave", onLeave);
      window.removeEventListener(CURSOR_EVENT, onCursorEvent);
    };
  }, [isFine, reducedMotion, x, y]);

  if (!isFine || reducedMotion) return null;

  const variant = payload?.variant ?? "default";
  const label = payload?.label ?? LABELS[variant];
  const isActive = variant !== "default";

  return (
    <motion.div
      aria-hidden
      className="pointer-events-none fixed left-0 top-0 z-[999] mix-blend-difference"
      style={{
        x: springX,
        y: springY,
        translateX: "-50%",
        translateY: "-50%",
        opacity: visible ? 1 : 0,
      }}
    >
      <motion.div
        className="flex items-center justify-center rounded-full bg-paper"
        animate={{
          width: isActive ? 84 : 8,
          height: isActive ? 84 : 8,
        }}
        transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      >
        <AnimatePresence mode="wait">
          {isActive && label && (
            <motion.span
              key={label}
              initial={{ opacity: 0, scale: 0.6 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.6 }}
              transition={{ duration: 0.2 }}
              className="font-mono-label text-ink"
            >
              {label}
            </motion.span>
          )}
        </AnimatePresence>
      </motion.div>
    </motion.div>
  );
}
