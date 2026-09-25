const ITALIAN_DATE = new Intl.DateTimeFormat("it-IT", {
  day: "numeric",
  month: "long",
  year: "numeric",
  timeZone: "Europe/Rome",
});

const ITALIAN_DATE_SHORT = new Intl.DateTimeFormat("it-IT", {
  weekday: "short",
  day: "numeric",
  month: "short",
  timeZone: "Europe/Rome",
});

const ITALIAN_TIME = new Intl.DateTimeFormat("it-IT", {
  hour: "2-digit",
  minute: "2-digit",
  timeZone: "Europe/Rome",
});

/** Single source of truth for event date display — derived from isoDate, never stored separately. */
export function formatEventDate(isoDate: string): string {
  return ITALIAN_DATE.format(new Date(isoDate));
}

/** Compact "ven 25 set" form for card layouts. */
export function formatEventDateShort(isoDate: string): string {
  return ITALIAN_DATE_SHORT.format(new Date(isoDate));
}

/** Start time only ("23:00") — the event model has no stored end time. */
export function formatEventTime(isoDate: string): string {
  return ITALIAN_TIME.format(new Date(isoDate));
}
