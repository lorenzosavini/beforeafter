export type CursorVariant =
  | "default"
  | "view"
  | "open"
  | "next"
  | "drag"
  | "event"
  | "image";

export interface CursorPayload {
  variant: CursorVariant;
  label?: string;
}

export const CURSOR_EVENT = "ba:cursor";

export function setCursor(payload: CursorPayload | null) {
  if (typeof window === "undefined") return;
  window.dispatchEvent(
    new CustomEvent<CursorPayload | null>(CURSOR_EVENT, { detail: payload })
  );
}
