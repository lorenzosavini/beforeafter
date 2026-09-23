interface LenisLike {
  scrollTo: (
    target: string | HTMLElement | number,
    opts?: { offset?: number; duration?: number }
  ) => void;
}

declare global {
  interface Window {
    __lenis?: LenisLike;
  }
}

export function smoothScrollTo(target: string, offset = 0) {
  if (typeof window === "undefined") return;
  const lenis = window.__lenis;
  if (lenis) {
    lenis.scrollTo(target, { offset, duration: 1.3 });
    return;
  }
  const el = document.querySelector(target);
  el?.scrollIntoView({ behavior: "smooth" });
}
