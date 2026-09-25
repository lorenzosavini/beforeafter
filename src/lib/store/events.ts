import { readJSON, writeJSON, uploadFile, deleteFile } from "@/lib/store/backend";

const EVENTS_KEY = "data/events";

export interface GalleryPhoto {
  id: string;
  url: string;
  filename: string;
  uploadedAt: string;
}

/** A DJ, band, or other performer/guest billed at the event. */
export interface LineupEntry {
  id: string;
  name: string;
  role: string;
}

/** One entry in the event's running order, e.g. "22:00" / "Apertura porte". */
export interface TimelineEntry {
  id: string;
  time: string;
  label: string;
}

/** A custom call-to-action button (tickets, WhatsApp, booking, etc). */
export interface CtaButton {
  id: string;
  label: string;
  url: string;
}

export interface EventRecord {
  id: string;
  slug: string;
  title: string;
  edition: string;
  isoDate: string;
  city: string;
  venue: string;
  coordinates: string;
  summary: string;
  price: string;
  dressCode: string;
  ageRestriction: string;
  lineup: LineupEntry[];
  timeline: TimelineEntry[];
  ctaButtons: CtaButton[];
  coverImageUrl: string | null;
  seed: number;
  gallery: GalleryPhoto[];
  createdAt: string;
  updatedAt: string;
}

export type EventInput = Omit<
  EventRecord,
  | "id"
  | "gallery"
  | "createdAt"
  | "updatedAt"
  | "coverImageUrl"
  | "seed"
  | "price"
  | "dressCode"
  | "ageRestriction"
  | "lineup"
  | "timeline"
  | "ctaButtons"
> & {
  coverImageUrl?: string | null;
  seed?: number;
  price?: string;
  dressCode?: string;
  ageRestriction?: string;
  lineup?: LineupEntry[];
  timeline?: TimelineEntry[];
  ctaButtons?: CtaButton[];
};

function slugify(title: string): string {
  return title
    .toLowerCase()
    .normalize("NFD")
    .replace(/[̀-ͯ]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
}

function id(): string {
  return crypto.randomUUID();
}

async function readAll(): Promise<EventRecord[]> {
  const events = await readJSON<EventRecord[]>(EVENTS_KEY);
  return events ?? [];
}

async function writeAll(events: EventRecord[]): Promise<void> {
  await writeJSON(EVENTS_KEY, events);
}

export async function getAllEvents(): Promise<EventRecord[]> {
  return readAll();
}

export async function getEventBySlug(
  slug: string
): Promise<EventRecord | null> {
  const events = await readAll();
  return events.find((e) => e.slug === slug) ?? null;
}

export async function getEventById(
  eventId: string
): Promise<EventRecord | null> {
  const events = await readAll();
  return events.find((e) => e.id === eventId) ?? null;
}

/** All events that haven't happened yet, soonest first. */
export async function getUpcomingEvents(): Promise<EventRecord[]> {
  const now = Date.now();
  const events = await readAll();
  return events
    .filter((e) => new Date(e.isoDate).getTime() >= now)
    .sort(
      (a, b) => new Date(a.isoDate).getTime() - new Date(b.isoDate).getTime()
    );
}

/** The single soonest event that hasn't happened yet, or null if none is scheduled. */
export async function getUpcomingEvent(): Promise<EventRecord | null> {
  const upcoming = await getUpcomingEvents();
  return upcoming[0] ?? null;
}

/** Past events, most recent first — the archive. */
export async function getPastEvents(): Promise<EventRecord[]> {
  const now = Date.now();
  const events = await readAll();
  return events
    .filter((e) => new Date(e.isoDate).getTime() < now)
    .sort(
      (a, b) => new Date(b.isoDate).getTime() - new Date(a.isoDate).getTime()
    );
}

export async function createEvent(input: EventInput): Promise<EventRecord> {
  const events = await readAll();
  const baseSlug = input.slug?.trim() ? slugify(input.slug) : slugify(input.title);
  let slug = baseSlug;
  let n = 2;
  while (events.some((e) => e.slug === slug)) {
    slug = `${baseSlug}-${n++}`;
  }

  const now = new Date().toISOString();
  const record: EventRecord = {
    id: id(),
    slug,
    title: input.title,
    edition: input.edition,
    isoDate: input.isoDate,
    city: input.city,
    venue: input.venue,
    coordinates: input.coordinates,
    summary: input.summary,
    price: input.price ?? "",
    dressCode: input.dressCode ?? "",
    ageRestriction: input.ageRestriction ?? "",
    lineup: input.lineup ?? [],
    timeline: input.timeline ?? [],
    ctaButtons: input.ctaButtons ?? [],
    coverImageUrl: input.coverImageUrl ?? null,
    seed: input.seed ?? Math.floor(Math.random() * 1000),
    gallery: [],
    createdAt: now,
    updatedAt: now,
  };

  events.push(record);
  await writeAll(events);
  return record;
}

export async function updateEvent(
  eventId: string,
  input: Partial<EventInput>
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  const current = events[idx];
  let slug = current.slug;
  if (input.slug && input.slug.trim()) {
    const candidate = slugify(input.slug);
    if (candidate !== current.slug) {
      let uniqueSlug = candidate;
      let n = 2;
      while (events.some((e) => e.id !== eventId && e.slug === uniqueSlug)) {
        uniqueSlug = `${candidate}-${n++}`;
      }
      slug = uniqueSlug;
    }
  }

  const updated: EventRecord = {
    ...current,
    ...input,
    slug,
    updatedAt: new Date().toISOString(),
  };
  events[idx] = updated;
  await writeAll(events);
  return updated;
}

export async function deleteEvent(eventId: string): Promise<void> {
  const events = await readAll();
  const target = events.find((e) => e.id === eventId);
  if (!target) return;

  if (target.coverImageUrl) {
    await deleteFile(target.coverImageUrl).catch(() => {});
  }
  await Promise.all(
    target.gallery.map((p) => deleteFile(p.url).catch(() => {}))
  );

  await writeAll(events.filter((e) => e.id !== eventId));
}

export async function setCoverImage(
  eventId: string,
  file: Buffer,
  filename: string,
  contentType: string
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  const ext = filename.split(".").pop() ?? "jpg";
  const { url } = await uploadFile(
    `covers/${eventId}-${Date.now()}.${ext}`,
    file,
    contentType
  );

  if (events[idx].coverImageUrl) {
    await deleteFile(events[idx].coverImageUrl!).catch(() => {});
  }

  events[idx] = {
    ...events[idx],
    coverImageUrl: url,
    updatedAt: new Date().toISOString(),
  };
  await writeAll(events);
  return events[idx];
}

/** Records a cover image already uploaded directly to storage by the client
 * (bypasses the serverless function's request-body size cap). */
export async function setCoverImageFromUrl(
  eventId: string,
  url: string
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  if (events[idx].coverImageUrl) {
    await deleteFile(events[idx].coverImageUrl!).catch(() => {});
  }

  events[idx] = {
    ...events[idx],
    coverImageUrl: url,
    updatedAt: new Date().toISOString(),
  };
  await writeAll(events);
  return events[idx];
}

/** Records gallery photos already uploaded directly to storage by the
 * client (bypasses the serverless function's request-body size cap).
 * Takes the whole batch from one bulk upload in a single read-modify-write:
 * the store is one shared JSON document with no locking, so doing N
 * separate writes in quick succession (one per photo) risks each one
 * reading a snapshot that doesn't yet include the previous write and
 * silently clobbering it on save. */
export async function addGalleryPhotosFromUrls(
  eventId: string,
  photos: { url: string; filename: string }[]
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  const uploaded: GalleryPhoto[] = photos.map((photo) => ({
    id: id(),
    url: photo.url,
    filename: photo.filename,
    uploadedAt: new Date().toISOString(),
  }));

  events[idx] = {
    ...events[idx],
    gallery: [...events[idx].gallery, ...uploaded],
    updatedAt: new Date().toISOString(),
  };
  await writeAll(events);
  return events[idx];
}

export async function addGalleryPhotos(
  eventId: string,
  files: { buffer: Buffer; filename: string; contentType: string }[]
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  const uploaded: GalleryPhoto[] = [];
  for (const file of files) {
    const ext = file.filename.split(".").pop() ?? "jpg";
    const { url } = await uploadFile(
      `gallery/${eventId}/${id()}.${ext}`,
      file.buffer,
      file.contentType
    );
    uploaded.push({
      id: id(),
      url,
      filename: file.filename,
      uploadedAt: new Date().toISOString(),
    });
  }

  events[idx] = {
    ...events[idx],
    gallery: [...events[idx].gallery, ...uploaded],
    updatedAt: new Date().toISOString(),
  };
  await writeAll(events);
  return events[idx];
}

export async function removeGalleryPhoto(
  eventId: string,
  photoId: string
): Promise<EventRecord> {
  const events = await readAll();
  const idx = events.findIndex((e) => e.id === eventId);
  if (idx === -1) throw new Error("Event not found");

  const photo = events[idx].gallery.find((p) => p.id === photoId);
  if (photo) await deleteFile(photo.url).catch(() => {});

  events[idx] = {
    ...events[idx],
    gallery: events[idx].gallery.filter((p) => p.id !== photoId),
    updatedAt: new Date().toISOString(),
  };
  await writeAll(events);
  return events[idx];
}
