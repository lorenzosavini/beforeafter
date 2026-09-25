"use client";

import { useRef, useState } from "react";
import type { GalleryPhoto } from "@/lib/store/events";

interface GalleryManagerProps {
  eventId: string;
  photos: GalleryPhoto[];
  onChange: (photos: GalleryPhoto[]) => void;
}

export default function GalleryManager({
  eventId,
  photos,
  onChange,
}: GalleryManagerProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [deletingId, setDeletingId] = useState<string | null>(null);

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;
    setUploading(true);
    setError(null);

    const form = new FormData();
    Array.from(files).forEach((f) => form.append("files", f));
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
        <span className="font-mono-label text-paper/50">Caricamento foto...</span>
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
