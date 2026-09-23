const STORAGE_KEY = "ba:discovered";
export const DISCOVERY_EVENT = "ba:discovery";
export const DISCOVERY_TOTAL = 13;
export const DISCOVERY_UNLOCK_THRESHOLD = 5;

function readSet(): Set<string> {
  if (typeof window === "undefined") return new Set();
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    return new Set(raw ? (JSON.parse(raw) as string[]) : []);
  } catch {
    return new Set();
  }
}

function writeSet(set: Set<string>) {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify([...set]));
  } catch {
    // localStorage unavailable (private mode, quota) — discovery just won't persist.
  }
}

/** Marks a poster/tile as discovered (hover on desktop, tap on touch). Idempotent. */
export function markDiscovered(id: string) {
  if (typeof window === "undefined") return;
  const set = readSet();
  if (set.has(id)) return;
  set.add(id);
  writeSet(set);
  window.dispatchEvent(new CustomEvent(DISCOVERY_EVENT));
}

export function getDiscoveredCount(): number {
  return readSet().size;
}
