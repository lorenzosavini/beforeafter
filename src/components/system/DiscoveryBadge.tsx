"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useDiscoveryCount } from "@/lib/hooks/useDiscovery";
import {
  DISCOVERY_TOTAL,
  DISCOVERY_UNLOCK_THRESHOLD,
} from "@/lib/discovery";

/**
 * A quiet counter, not a game HUD: tracks how many posters/frames the
 * visitor has actually looked at closely. Appears only after the first
 * discovery, settles, and marks the moment it crosses the unlock threshold
 * with a single pulse — no ongoing animation.
 */
export default function DiscoveryBadge() {
  const count = useDiscoveryCount();
  const unlocked = count >= DISCOVERY_UNLOCK_THRESHOLD;

  return (
    <AnimatePresence>
      {count > 0 && (
        <motion.div
          className="font-mono-label pointer-events-none fixed bottom-5 left-5 z-40 hidden flex-col gap-1.5 sm:flex"
          initial={{ opacity: 0, y: 8 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: 8 }}
          transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
        >
          <motion.span
            key={unlocked ? "unlocked" : "counting"}
            initial={{ scale: 0.9 }}
            animate={{ scale: 1 }}
            transition={{ duration: 0.3, ease: [0.16, 1, 0.3, 1] }}
            className={unlocked ? "text-flame" : "text-paper/60"}
          >
            {unlocked
              ? "hai visto tutto"
              : `scoperti ${count}/${DISCOVERY_TOTAL}`}
          </motion.span>
          <span className="block h-px w-16 overflow-hidden bg-paper/20">
            <motion.span
              className="block h-full bg-flame"
              initial={{ scaleX: 0 }}
              animate={{ scaleX: Math.min(count / DISCOVERY_TOTAL, 1) }}
              transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
              style={{ transformOrigin: "left" }}
            />
          </span>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
