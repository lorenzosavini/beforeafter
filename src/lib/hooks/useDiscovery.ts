"use client";

import { useSyncExternalStore } from "react";
import { DISCOVERY_EVENT, getDiscoveredCount } from "@/lib/discovery";

function subscribe(onChange: () => void) {
  window.addEventListener(DISCOVERY_EVENT, onChange);
  return () => window.removeEventListener(DISCOVERY_EVENT, onChange);
}

export function useDiscoveryCount() {
  return useSyncExternalStore(subscribe, getDiscoveredCount, () => 0);
}
