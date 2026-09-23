"use client";

import { motion } from "framer-motion";

interface MobileMenuProps {
  links: { label: string; href: string }[];
  onNavigate: (
    href: string
  ) => (e: React.MouseEvent<HTMLAnchorElement>) => void;
  onClose: () => void;
}

const EASE = [0.76, 0, 0.24, 1] as const;

export default function MobileMenu({
  links,
  onNavigate,
  onClose,
}: MobileMenuProps) {
  return (
    <motion.div
      className="fixed inset-0 z-40 flex flex-col justify-between bg-ink px-6 pb-10 pt-28 text-paper md:hidden"
      initial={{ clipPath: "circle(2.5% at calc(100% - 40px) 40px)" }}
      animate={{ clipPath: "circle(150% at calc(100% - 40px) 40px)" }}
      exit={{ clipPath: "circle(2.5% at calc(100% - 40px) 40px)" }}
      transition={{ duration: 0.7, ease: EASE }}
    >
      <nav className="flex flex-col gap-1">
        {links.map((link, i) => (
          <span key={link.href} className="reveal-line">
            <motion.a
              href={link.href}
              onClick={(e) => {
                onNavigate(link.href)(e);
                onClose();
              }}
              className="font-display block py-2 text-[15vw] leading-[0.95] text-paper"
              initial={{ y: "100%" }}
              animate={{ y: "0%" }}
              transition={{ duration: 0.6, delay: 0.15 + i * 0.06, ease: EASE }}
            >
              {link.label}
            </motion.a>
          </span>
        ))}
      </nav>

      <motion.div
        className="font-mono-label flex items-center justify-between border-t border-paper/20 pt-6 opacity-70"
        initial={{ opacity: 0 }}
        animate={{ opacity: 0.7 }}
        transition={{ duration: 0.5, delay: 0.5 }}
      >
        <span>Roma · Italia</span>
        <span>@beforeafter</span>
      </motion.div>
    </motion.div>
  );
}
