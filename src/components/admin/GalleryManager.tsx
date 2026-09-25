"use client";

import { useRef, useState } from "react";
import { upload } from "@vercel/blob/client";
import type { GalleryPhoto } from "@/lib/store/events";

interface GalleryManagerProps {
  eventId: string;
  photos: GalleryPhoto[];
  usingBlobStorage: boolean;
  onChange: (photos: GalleryPhoto[]) => void;
}

export default function GalleryManager({
  eventId,
  photos,
  usingBlobStorage,
  onChange,
}: GalleryManagerProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState<{ done: number; total: number } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [deletingId, setDeletingId] = useState<string | null>(null);

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = e.target.files;
    if (!fileList || fileList.length === 0) return;
    const files = Array.from(fileList);
    setUploading(true);
    setError(null);

    if (usingBlobStorage) {
      // One direct-to-Blob upload per file, from the browser, so a large
      // batch never has to fit inside a single serverless function's
      // request-body cap — and a failure partway through keeps whatever
      // already succeeded instead of losing the whole batch.
      setProgress({ done: 0, total: files.length });
      let currentGallery = photos;
      for (const file of files) {
        try {
          const blob = await upload(`gallery/${eventId}/${file.name}`, file, {
            access: "public",
            handleUploadUrl: "/api/admin/blob-upload",
          });
          const res = await fetch(`/api/admin/events/${eventId}/gallery`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url: blob.url, filename: file.name }),
          });
          const data = await res.json();
          if (!res.ok) throw new Error(data.error ?? "Errore nel caricamento");
          currentGallery = data.event.gallery;
          onChange(currentGallery);
        } catch (err) {
          setError(
            `${file.name}: ${err instanceof Error ? err.message : "errore nel caricamento"}`
          );
        }
        setProgress((p) => (p ? { ...p, done: p.done + 1 } : p));
      }
      setProgress(null);
      setUploading(false);
      if (inputRef.current) inputRef.current.value = "";
      return;
    }

    const form = new FormData();
    files.forEach((f) => form.append("files", f));
    const res = await fetch(`/api/admin/events/${eventId}/gallery`, {
      method: "POST",
      body: form,
    });
    const data = await res.json();
    setUploading(false);
    if (!res.ok) {
      setError(data.error ?? "Errore nel caricamento");
      return;
    }
    onChange(data.event.gallery);
    if (inputRef.current) inputRef.current.value = "";
  };

  const handleDelete = async (photoId: string) => {
    setDeletingId(photoId);
    const res = await fetch(
      `/api/admin/events/${eventId}/gallery/${photoId}`,
      { method: "DELETE" }
    );
    setDeletingId(null);
    if (res.ok) {
      const data = await res.json();
      onChange(data.event.gallery);
    }
  };

  return (
    <div className="flex flex-col gap-4">
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        multiple
        onChange={handleUpload}
        disabled={uploading}
        className="font-mono-label text-paper/60 file:mr-4 file:border file:border-paper/30 file:bg-transparent file:px-4 file:py-2 file:text-paper file:font-mono-label"
      />
      {uploading && (
        <span className="font-mono-label text-paper/50">
          {progress
            ? `Caricamento foto... ${progress.done}/${progress.total}`
            : "Caricamento foto..."}
        </span>
      )}
      {error && <span className="font-mono-label text-flame">{error}</span>}

      {photos.length > 0 && (
        <div className="grid grid-cols-3 gap-2 sm:grid-cols-5">
          {photos.map((photo) => (
            <div key={photo.id} className="group relative aspect-square">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={photo.url}
                alt={photo.filename}
                className="h-full w-full object-cover"
              />
              <button
                type="button"
                onClick={() => handleDelete(photo.id)}
                disabled={deletingId === photo.id}
                className="font-mono-label absolute inset-0 flex items-center justify-center bg-night/80 text-paper opacity-0 transition-opacity group-hover:opacity-100"
              >
                {deletingId === photo.id ? "..." : "Rimuovi"}
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
