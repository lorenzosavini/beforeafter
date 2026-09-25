"use client";

import { useState } from "react";
import type { LineupEntry, TimelineEntry, CtaButton } from "@/lib/store/events";

export interface EventFormValues {
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
  slug: string;
}

interface EventFormProps {
  initial?: Partial<EventFormValues>;
  submitLabel: string;
  onSubmit: (values: EventFormValues) => Promise<void>;
}

const FIELD_CLASS =
  "font-sans mt-2 w-full rounded-none border border-paper/25 bg-transparent px-4 py-3 text-base text-paper outline-none transition-colors focus:border-flame";
const FIELD_CLASS_SMALL =
  "font-sans w-full rounded-none border border-paper/25 bg-transparent px-3 py-2 text-sm text-paper outline-none transition-colors focus:border-flame";
const REMOVE_BUTTON_CLASS =
  "font-mono-label shrink-0 border border-paper/25 px-3 py-2 text-paper/60 transition-colors hover:border-flame hover:text-flame";
const ADD_BUTTON_CLASS =
  "font-mono-label w-fit border border-paper/25 px-4 py-2 text-paper/70 transition-colors hover:border-paper hover:text-paper";

function toDateTimeLocal(iso?: string): string {
  if (!iso) return "";
  const d = new Date(iso);
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function newId(): string {
  return crypto.randomUUID();
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
    price: initial?.price ?? "",
    dressCode: initial?.dressCode ?? "",
    ageRestriction: initial?.ageRestriction ?? "",
    lineup: initial?.lineup ?? [],
    timeline: initial?.timeline ?? [],
    ctaButtons: initial?.ctaButtons ?? [],
    slug: initial?.slug ?? "",
  });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const set = <K extends keyof EventFormValues>(key: K, val: EventFormValues[K]) =>
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

      <div className="grid grid-cols-2 gap-5">
        <label className="font-mono-label text-paper/60">
          Prezzo
          <input
            className={FIELD_CLASS}
            value={values.price}
            onChange={(e) => set("price", e.target.value)}
            placeholder="15€ in lista, 20€ alla cassa"
          />
        </label>
        <label className="font-mono-label text-paper/60">
          Età minima
          <input
            className={FIELD_CLASS}
            value={values.ageRestriction}
            onChange={(e) => set("ageRestriction", e.target.value)}
            placeholder="18+"
          />
        </label>
      </div>

      <label className="font-mono-label text-paper/60">
        Dress code
        <input
          className={FIELD_CLASS}
          value={values.dressCode}
          onChange={(e) => set("dressCode", e.target.value)}
          placeholder="Elegante, no sportivo"
        />
      </label>

      <div className="flex flex-col gap-3">
        <span className="font-mono-label text-paper/60">Lineup (DJ, band, ospiti)</span>
        {values.lineup.map((entry) => (
          <div key={entry.id} className="flex items-center gap-2">
            <input
              className={FIELD_CLASS_SMALL}
              value={entry.name}
              placeholder="Nome"
              onChange={(e) =>
                set(
                  "lineup",
                  values.lineup.map((l) =>
                    l.id === entry.id ? { ...l, name: e.target.value } : l
                  )
                )
              }
            />
            <input
              className={`${FIELD_CLASS_SMALL} max-w-[140px]`}
              value={entry.role}
              placeholder="DJ / Band / Ospite"
              onChange={(e) =>
                set(
                  "lineup",
                  values.lineup.map((l) =>
                    l.id === entry.id ? { ...l, role: e.target.value } : l
                  )
                )
              }
            />
            <button
              type="button"
              className={REMOVE_BUTTON_CLASS}
              onClick={() => set("lineup", values.lineup.filter((l) => l.id !== entry.id))}
            >
              ✕
            </button>
          </div>
        ))}
        <button
          type="button"
          className={ADD_BUTTON_CLASS}
          onClick={() =>
            set("lineup", [...values.lineup, { id: newId(), name: "", role: "" }])
          }
        >
          + Aggiungi ospite
        </button>
      </div>

      <div className="flex flex-col gap-3">
        <span className="font-mono-label text-paper/60">Timeline della serata</span>
        {values.timeline.map((entry) => (
          <div key={entry.id} className="flex items-center gap-2">
            <input
              className={`${FIELD_CLASS_SMALL} max-w-[110px]`}
              value={entry.time}
              placeholder="22:00"
              onChange={(e) =>
                set(
                  "timeline",
                  values.timeline.map((t) =>
                    t.id === entry.id ? { ...t, time: e.target.value } : t
                  )
                )
              }
            />
            <input
              className={FIELD_CLASS_SMALL}
              value={entry.label}
              placeholder="Apertura porte"
              onChange={(e) =>
                set(
                  "timeline",
                  values.timeline.map((t) =>
                    t.id === entry.id ? { ...t, label: e.target.value } : t
                  )
                )
              }
            />
            <button
              type="button"
              className={REMOVE_BUTTON_CLASS}
              onClick={() => set("timeline", values.timeline.filter((t) => t.id !== entry.id))}
            >
              ✕
            </button>
          </div>
        ))}
        <button
          type="button"
          className={ADD_BUTTON_CLASS}
          onClick={() =>
            set("timeline", [...values.timeline, { id: newId(), time: "", label: "" }])
          }
        >
          + Aggiungi orario
        </button>
      </div>

      <div className="flex flex-col gap-3">
        <span className="font-mono-label text-paper/60">Pulsanti (biglietti, contatti, ecc.)</span>
        {values.ctaButtons.map((entry) => (
          <div key={entry.id} className="flex items-center gap-2">
            <input
              className={`${FIELD_CLASS_SMALL} max-w-[160px]`}
              value={entry.label}
              placeholder="Acquista biglietti"
              onChange={(e) =>
                set(
                  "ctaButtons",
                  values.ctaButtons.map((c) =>
                    c.id === entry.id ? { ...c, label: e.target.value } : c
                  )
                )
              }
            />
            <input
              className={FIELD_CLASS_SMALL}
              value={entry.url}
              placeholder="https://..."
              onChange={(e) =>
                set(
                  "ctaButtons",
                  values.ctaButtons.map((c) =>
                    c.id === entry.id ? { ...c, url: e.target.value } : c
                  )
                )
              }
            />
            <button
              type="button"
              className={REMOVE_BUTTON_CLASS}
              onClick={() =>
                set("ctaButtons", values.ctaButtons.filter((c) => c.id !== entry.id))
              }
            >
              ✕
            </button>
          </div>
        ))}
        <button
          type="button"
          className={ADD_BUTTON_CLASS}
          onClick={() =>
            set("ctaButtons", [...values.ctaButtons, { id: newId(), label: "", url: "" }])
          }
        >
          + Aggiungi pulsante
        </button>
      </div>

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
