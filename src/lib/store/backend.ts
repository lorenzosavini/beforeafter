import { put, del, list } from "@vercel/blob";
import { mkdir, readFile, writeFile, unlink } from "node:fs/promises";
import path from "node:path";

const hasBlob = !!process.env.BLOB_READ_WRITE_TOKEN;
const LOCAL_ROOT = path.join(process.cwd(), ".data");

async function ensureLocalDir(filePath: string) {
  await mkdir(path.dirname(filePath), { recursive: true });
}

function parseVersionTimestamp(pathname: string): number {
  const filename = pathname.split("/").pop() ?? "";
  return Number(filename.split("-")[0]) || 0;
}

/**
 * Reads a JSON document by its logical key. Returns null if it doesn't
 * exist yet.
 *
 * Each write creates a brand-new, never-before-fetched blob rather than
 * overwriting one fixed URL — see writeJSON for why. list() metadata
 * (verified via direct testing: size/uploadedAt update instantly on every
 * write) finds the newest version reliably; a fixed overwritten URL's
 * *content*, by contrast, was observed staying stale for 20+ seconds after
 * a write, apparently cached at a layer that ignores cache-control and
 * cache-busting query strings.
 */
export async function readJSON<T>(key: string): Promise<T | null> {
  if (hasBlob) {
    const { blobs } = await list({ prefix: `${key}/` });
    if (blobs.length === 0) {
      // One-time migration path: this key used to be written as a single
      // fixed-pathname blob (`${key}.json`, overwritten in place) before
      // the versioned scheme above. Fall back to it so existing data
      // isn't orphaned; the next write moves it into the new scheme.
      const legacy = await list({ prefix: `${key}.json` });
      const legacyBlob = legacy.blobs.find((b) => b.pathname === `${key}.json`);
      if (!legacyBlob) return null;
      const res = await fetch(legacyBlob.url, { cache: "no-store" });
      if (!res.ok) return null;
      return (await res.json()) as T;
    }
    const latest = blobs.reduce((a, b) =>
      parseVersionTimestamp(b.pathname) > parseVersionTimestamp(a.pathname) ? b : a
    );
    const res = await fetch(latest.url, { cache: "no-store" });
    if (!res.ok) return null;
    return (await res.json()) as T;
  }

  const filePath = path.join(LOCAL_ROOT, key);
  try {
    const raw = await readFile(filePath, "utf-8");
    return JSON.parse(raw) as T;
  } catch {
    return null;
  }
}

/**
 * Writes a JSON document under a fixed logical key, as a new versioned
 * blob rather than an overwrite (see readJSON for why). Older versions are
 * cleaned up afterward, best-effort, without blocking the write — and
 * never deletes anything as new or newer than what was just written, so a
 * genuinely concurrent write's blob is never at risk of being deleted.
 */
export async function writeJSON(key: string, data: unknown): Promise<void> {
  const body = JSON.stringify(data, null, 2);

  if (hasBlob) {
    const now = Date.now();
    const pathname = `${key}/${now}-${Math.random().toString(36).slice(2)}.json`;
    await put(pathname, body, {
      access: "public",
      contentType: "application/json",
    });

    list({ prefix: `${key}/` })
      .then(({ blobs }) => {
        const staleUrls = blobs
          .filter((b) => b.pathname !== pathname && parseVersionTimestamp(b.pathname) < now)
          .map((b) => b.url);
        if (staleUrls.length > 0) return del(staleUrls);
      })
      .catch(() => {});
    return;
  }

  const filePath = path.join(LOCAL_ROOT, key);
  await ensureLocalDir(filePath);
  await writeFile(filePath, body, "utf-8");
}

/** Uploads a binary file (photo/cover image) and returns its public URL. */
export async function uploadFile(
  key: string,
  data: Buffer,
  contentType: string
): Promise<{ url: string }> {
  if (hasBlob) {
    const blob = await put(key, data, {
      access: "public",
      addRandomSuffix: true,
      contentType,
    });
    return { url: blob.url };
  }

  const filePath = path.join(LOCAL_ROOT, "uploads", key);
  await ensureLocalDir(filePath);
  await writeFile(filePath, data);
  return { url: `/api/local-uploads/${encodeURIComponent(key)}` };
}

/** Deletes a previously uploaded file by its public URL (or local key). */
export async function deleteFile(url: string): Promise<void> {
  if (hasBlob && url.includes("blob.vercel-storage.com")) {
    await del(url);
    return;
  }

  if (url.startsWith("/api/local-uploads/")) {
    const key = decodeURIComponent(url.replace("/api/local-uploads/", ""));
    const filePath = path.join(LOCAL_ROOT, "uploads", key);
    try {
      await unlink(filePath);
    } catch {
      // already gone — fine
    }
  }
}

export const usingBlobStorage = hasBlob;
