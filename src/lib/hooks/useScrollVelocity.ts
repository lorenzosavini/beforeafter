"use client";

import { useEffect, useRef, useState } from "react";

/**
 * Tracks scroll velocity (px/frame, signed) and decays it to 0 when the
 * user stops scrolling — used for transient, self-resolving motion
 * (skew, drift) that must always return to a stable resting state.
 */
export function useScrollVelocity() {
  const [velocity, setVelocity] = useState(0);
  const lastY = useRef(0);
  const rafId = useRef<number | null>(null);
  const target = useRef(0);

  useEffect(() => {
    lastY.current = window.scrollY;

    const onScroll = () => {
      const y = window.scrollY;
      target.current = y - lastY.current;
      lastY.current = y;
    };

    window.addEventListener("scroll", onScroll, { passive: true });

    const tick = () => {
      target.current *= 0.85; // decay toward rest
      setVelocity((prev) => {
        const next = prev + (target.current - prev) * 0.2;
        return Math.abs(next) < 0.01 ? 0 : next;
      });
      rafId.current = requestAnimationFrame(tick);
    };
    rafId.current = requestAnimationFrame(tick);

    return () => {
      window.removeEventListener("scroll", onScroll);
      if (rafId.current) cancelAnimationFrame(rafId.current);
    };
  }, []);

  return velocity;
}
