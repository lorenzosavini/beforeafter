const ITALIAN_DATE = new Intl.DateTimeFormat("it-IT", {
  day: "numeric",
  month: "long",
  year: "numeric",
  timeZone: "Europe/Rome",
});

/** Single source of truth for event date display — derived from isoDate, never stored separately. */
export function formatEventDate(isoDate: string): string {
  return ITALIAN_DATE.format(new Date(isoDate));
}
