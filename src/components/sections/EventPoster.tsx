"use client";

import { useRef, type MouseEvent } from "react";
import { motion, useMotionValue, useSpring } from "framer-motion";
import GeneratedArtwork from "@/components/media/GeneratedArtwork";
import { setCursor } from "@/lib/cursor";
import { useIsFinePointer, useReducedMotion } from "@/lib/hooks/useReducedMotion";
import type { EventRecord } from "@/lib/data/events";

interface EventPosterProps {
  event: EventRecord;
  className?: string;
  cursorLabel?: string;
}

/** The event artwork treated as a tactile object: it tilts toward the pointer, then rests. */
export default function EventPoster({
  event,
  className,
  cursorLabel,
}: EventPosterProps) {
  const ref = useRef<HTMLDivElement>(null);
  const isFine = useIsFinePointer();
  const reducedMotion = useReducedMotion();

  const rotateX = useMotionValue(0);
  const rotateY = useMotionValue(0);
  const springX = useSpring(rotateX, { damping: 22, stiffness: 180 });
  const springY = useSpring(rotateY, { damping: 22, stiffness: 180 });

  const handleMove = (e: MouseEvent<HTMLDivElement>) => {
    if (!isFine || reducedMotion) return;
    const bounds = ref.current?.getBoundingClientRect();
    if (!bounds) return;
    const px = (e.clientX - bounds.left) / bounds.width - 0.5;
    const py = (e.clientY - bounds.top) / bounds.height - 0.5;
    rotateY.set(px * 9);
    rotateX.set(py * -9);
  };

  const handleLeave = () => {
    rotateX.set(0);
    rotateY.set(0);
    setCursor(null);
  };

  return (
    <div
      ref={ref}
      className={className}
      style={{ perspective: 1400 }}
      onMouseMove={handleMove}
      onMouseLeave={handleLeave}
      onMouseEnter={() =>
        setCursor({ variant: "event", label: cursorLabel ?? event.edition })
      }
    >
      <motion.div
        style={{ rotateX: springX, rotateY: springY }}
        className="h-full w-full will-change-transform"
      >
        <GeneratedArtwork
          seed={event.seed}
          label={event.edition}
          className="h-full w-full"
        />
      </motion.div>
    </div>
  );
}
