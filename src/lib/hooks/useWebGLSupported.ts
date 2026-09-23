"use client";

import { useSyncExternalStore } from "react";

let cached: boolean | null = null;

function detect(): boolean {
  if (cached !== null) return cached;
  try {
    const canvas = document.createElement("canvas");
    cached = !!(canvas.getContext("webgl2") || canvas.getContext("webgl"));
  } catch {
    cached = false;
  }
  return cached;
}

function subscribe() {
  // WebGL support can't change mid-session; nothing to subscribe to.
  return () => {};
}

/** Feature-detects WebGL once per session; false during SSR/first paint. */
export function useWebGLSupported() {
  return useSyncExternalStore(subscribe, detect, () => false);
}
