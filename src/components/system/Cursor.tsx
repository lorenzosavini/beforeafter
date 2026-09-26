"use client";

import { useEffect, useRef, useState } from "react";
import { usePathname } from "next/navigation";
import {
  motion,
  useMotionValue,
  useSpring,
  useTransform,
  AnimatePresence,
} from "framer-motion";
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
  const pathname = usePathname();
  const isFine = useIsFinePointer();
  const reducedMotion = useReducedMotion();
  const [payload, setPayload] = useState<CursorPayload | null>(null);
  const [visible, setVisible] = useState(false);

  const x = useMotionValue(-100);
  const y = useMotionValue(-100);
  const springX = useSpring(x, { damping: 30, stiffness: 400, mass: 0.4 });
  const springY = useSpring(y, { damping: 30, stiffness: 400, mass: 0.4 });

  // velocity-driven stretch: fast movement elongates the dot along its
  // direction of travel; it relaxes back to a circle the moment it stops.
  const stretch = useMotionValue(0);
  const angle = useMotionValue(0);
  const springStretch = useSpring(stretch, {
    damping: 18,
    stiffness: 320,
    mass: 0.5,
  });
  const lastPos = useRef<{ x: number; y: number; t: number } | null>(null);
  const relaxTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (!isFine || reducedMotion) return;

    const onMove = (e: MouseEvent) => {
      x.set(e.clientX);
      y.set(e.clientY);
      setVisible(true);

      const now = performance.now();
      const last = lastPos.current;
      if (last) {
        const dt = Math.max(now - last.t, 1);
        const dx = e.clientX - last.x;
        const dy = e.clientY - last.y;
        const dist = Math.hypot(dx, dy);
        const velocity = dist / dt;
        stretch.set(Math.min(velocity * 10, 0.85));
        if (dist > 0.5) {
          angle.set((Math.atan2(dy, dx) * 180) / Math.PI);
        }
      }
      lastPos.current = { x: e.clientX, y: e.clientY, t: now };

      if (relaxTimer.current) clearTimeout(relaxTimer.current);
      relaxTimer.current = setTimeout(() => stretch.set(0), 80);
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
      if (relaxTimer.current) clearTimeout(relaxTimer.current);
    };
  }, [isFine, reducedMotion, x, y, stretch, angle]);

  const variant = payload?.variant ?? "default";
  const label = payload?.label ?? LABELS[variant];
  const isActive = variant !== "default";

  const scaleX = useTransform(springStretch, (v) => 1 + (isActive ? 0 : v));
  const scaleY = useTransform(springStretch, (v) =>
    Math.max(1 - (isActive ? 0 : v * 0.5), 0.4)
  );
  const counterRotate = useTransform(angle, (a) => -a);

  if (!isFine || reducedMotion || pathname?.startsWith("/admin")) return null;

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
        style={{ rotate: angle, scaleX, scaleY }}
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
              style={{ rotate: counterRotate }}
            >
              {label}
            </motion.span>
          )}
        </AnimatePresence>
      </motion.div>
    </motion.div>
  );
}
