export function isPastEvent(isoDate: string): boolean {
  return new Date(isoDate).getTime() < Date.now();
}
