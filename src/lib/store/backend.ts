import { put, del, list } from "@vercel/blob";
import { mkdir, readFile, writeFile, unlink } from "node:fs/promises";
import path from "node:path";

const hasBlob = !!process.env.BLOB_READ_WRITE_TOKEN;
const LOCAL_ROOT = path.join(process.cwd(), ".data");

async function ensureLocalDir(filePath: string) {
  await mkdir(path.dirname(filePath), { recursive: true });
}

/** Reads a JSON document by its logical key. Returns null if it doesn't exist yet. */
export async function readJSON<T>(key: string): Promise<T | null> {
  if (hasBlob) {
    const { blobs } = await list({ prefix: key, limit: 1 });
    const blob = blobs.find((b) => b.pathname === key);
    if (!blob) return null;
    const res = await fetch(blob.url, { cache: "no-store" });
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

/** Overwrites a JSON document at a fixed logical key. */
export async function writeJSON(key: string, data: unknown): Promise<void> {
  const body = JSON.stringify(data, null, 2);

  if (hasBlob) {
    await put(key, body, {
      access: "public",
      addRandomSuffix: false,
      allowOverwrite: true,
      contentType: "application/json",
    });
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
