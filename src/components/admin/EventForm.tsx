"use client";

import { useState } from "react";

export interface EventFormValues {
  title: string;
  edition: string;
  isoDate: string;
  city: string;
  venue: string;
  coordinates: string;
  summary: string;
  slug: string;
}

interface EventFormProps {
  initial?: Partial<EventFormValues>;
  submitLabel: string;
  onSubmit: (values: EventFormValues) => Promise<void>;
}

const FIELD_CLASS =
  "font-sans mt-2 w-full rounded-none border border-paper/25 bg-transparent px-4 py-3 text-base text-paper outline-none transition-colors focus:border-flame";

function toDateTimeLocal(iso?: string): string {
  if (!iso) return "";
  const d = new Date(iso);
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

export default function EventForm({
  initial,
  submitLabel,
  onSubmit,
}: EventFormProps) {
  const [values, setValues] = useState<EventFormValues>({
    title: initial?.title ?? "",
    edition: initial?.edition ?? "",
    isoDate: toDateTimeLocal(initial?.isoDate),
    city: initial?.city ?? "",
    venue: initial?.venue ?? "",
    coordinates: initial?.coordinates ?? "",
    summary: initial?.summary ?? "",
    slug: initial?.slug ?? "",
  });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const set = <K extends keyof EventFormValues>(key: K, val: string) =>
    setValues((v) => ({ ...v, [key]: val }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!values.title || !values.isoDate) {
      setError("Titolo e data sono obbligatori.");
      return;
    }
    setSaving(true);
    setError(null);
    try {
      await onSubmit({
        ...values,
        isoDate: new Date(values.isoDate).toISOString(),
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : "Errore imprevisto.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="flex max-w-xl flex-col gap-5">
      <label className="font-mono-label text-paper/60">
        Titolo
        <input
          className={FIELD_CLASS}
          value={values.title}
          onChange={(e) => set("title", e.target.value)}
          placeholder="Capitolo VIII"
        />
      </label>

      <div className="grid grid-cols-2 gap-5">
        <label className="font-mono-label text-paper/60">
          Numero edizione
          <input
            className={FIELD_CLASS}
            value={values.edition}
            onChange={(e) => set("edition", e.target.value)}
            placeholder="N° 08"
          />
        </label>
        <label className="font-mono-label text-paper/60">
          Data e ora
          <input
            type="datetime-local"
            className={FIELD_CLASS}
            value={values.isoDate}
            onChange={(e) => set("isoDate", e.target.value)}
          />
        </label>
      </div>

      <div className="grid grid-cols-2 gap-5">
        <label className="font-mono-label text-paper/60">
          Città
          <input
            className={FIELD_CLASS}
            value={values.city}
            onChange={(e) => set("city", e.target.value)}
          />
        </label>
        <label className="font-mono-label text-paper/60">
          Venue
          <input
            className={FIELD_CLASS}
            value={values.venue}
            onChange={(e) => set("venue", e.target.value)}
            placeholder="Location da annunciare"
          />
        </label>
      </div>

      <label className="font-mono-label text-paper/60">
        Coordinate
        <input
          className={FIELD_CLASS}
          value={values.coordinates}
          onChange={(e) => set("coordinates", e.target.value)}
          placeholder="41.9028° N, 12.4964° E"
        />
      </label>

      <label className="font-mono-label text-paper/60">
        Riassunto
        <textarea
          className={FIELD_CLASS}
          rows={3}
          value={values.summary}
          onChange={(e) => set("summary", e.target.value)}
        />
      </label>

      <label className="font-mono-label text-paper/60">
        Slug (opzionale — generato dal titolo se vuoto)
        <input
          className={FIELD_CLASS}
          value={values.slug}
          onChange={(e) => set("slug", e.target.value)}
          placeholder="capitolo-viii"
        />
      </label>

      {error && <p className="font-mono-label text-flame">{error}</p>}

      <button
        type="submit"
        disabled={saving}
        className="font-mono-label mt-2 w-fit border border-paper/30 px-8 py-3 text-paper transition-colors hover:border-paper disabled:opacity-40"
      >
        {saving ? "Salvataggio..." : submitLabel}
      </button>
    </form>
  );
}
