"use client";

import { useRef, useState } from "react";
import { upload } from "@vercel/blob/client";

interface CoverUploaderProps {
  eventId: string;
  coverImageUrl: string | null;
  usingBlobStorage: boolean;
  onUploaded: (url: string) => void;
}

export default function CoverUploader({
  eventId,
  coverImageUrl,
  usingBlobStorage,
  onUploaded,
}: CoverUploaderProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploading(true);
    setError(null);

    try {
      if (usingBlobStorage) {
        // Uploads straight from the browser to Blob storage, bypassing the
        // ~4.5MB request-body cap a relayed upload through our own API
        // route would hit.
        const blob = await upload(`covers/${eventId}-${file.name}`, file, {
          access: "public",
          handleUploadUrl: "/api/admin/blob-upload",
        });
        const res = await fetch(`/api/admin/events/${eventId}/cover`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ url: blob.url }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error ?? "Errore nel caricamento");
        onUploaded(data.event.coverImageUrl);
      } else {
        const form = new FormData();
        form.append("file", file);
        const res = await fetch(`/api/admin/events/${eventId}/cover`, {
          method: "POST",
          body: form,
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error ?? "Errore nel caricamento");
        onUploaded(data.event.coverImageUrl);
      }
      if (inputRef.current) inputRef.current.value = "";
    } catch (err) {
      setError(err instanceof Error ? err.message : "Errore nel caricamento");
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="flex flex-col gap-3">
      <span className="font-mono-label text-paper/60">Immagine di copertina</span>
      {coverImageUrl && (
        // eslint-disable-next-line @next/next/no-img-element
        <img
          src={coverImageUrl}
          alt=""
          className="h-48 w-36 object-cover"
        />
      )}
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        onChange={handleChange}
        disabled={uploading}
        className="font-mono-label text-paper/60 file:mr-4 file:border file:border-paper/30 file:bg-transparent file:px-4 file:py-2 file:text-paper file:font-mono-label"
      />
      {uploading && <span className="font-mono-label text-paper/50">Caricamento...</span>}
      {error && <span className="font-mono-label text-flame">{error}</span>}
    </div>
  );
}
