"use client";

import { useRef, useState } from "react";

interface CoverUploaderProps {
  eventId: string;
  coverImageUrl: string | null;
  onUploaded: (url: string) => void;
}

export default function CoverUploader({
  eventId,
  coverImageUrl,
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

    const form = new FormData();
    form.append("file", file);
    const res = await fetch(`/api/admin/events/${eventId}/cover`, {
      method: "POST",
      body: form,
    });
    const data = await res.json();
    setUploading(false);
    if (!res.ok) {
      setError(data.error ?? "Errore nel caricamento");
      return;
    }
    onUploaded(data.event.coverImageUrl);
    if (inputRef.current) inputRef.current.value = "";
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
