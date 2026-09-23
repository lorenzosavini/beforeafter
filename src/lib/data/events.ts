export interface EventRecord {
  slug: string;
  title: string;
  edition: string;
  dateLabel: string;
  isoDate: string;
  city: string;
  venue: string;
  coordinates: string;
  seed: number;
  summary: string;
}

/**
 * Placeholder content — structured to be swapped for real records from a
 * future events database/CMS without touching the components that render
 * them. Nothing here should be read as a confirmed real-world fact.
 */
export const nextEvent: EventRecord = {
  slug: "capitolo-vii",
  title: "Capitolo VII",
  edition: "N° 07",
  dateLabel: "18 Ottobre",
  isoDate: "2026-10-18",
  city: "Roma",
  venue: "Location da annunciare",
  coordinates: "41.9028° N, 12.4964° E",
  seed: 7,
  summary:
    "Il settimo capitolo di beforeafter. Dettagli e line-up in arrivo — l'attesa fa parte dell'esperienza.",
};

export const archiveEvents: EventRecord[] = [
  {
    slug: "capitolo-vi",
    title: "Capitolo VI",
    edition: "N° 06",
    dateLabel: "12 Luglio 2025",
    isoDate: "2025-07-12",
    city: "Roma",
    venue: "Spazio Industriale",
    coordinates: "41.9028° N, 12.4964° E",
    seed: 6,
    summary: "Una notte, una città, un prima e un dopo.",
  },
  {
    slug: "capitolo-v",
    title: "Capitolo V",
    edition: "N° 05",
    dateLabel: "22 Marzo 2025",
    isoDate: "2025-03-22",
    city: "Milano",
    venue: "Ex Deposito",
    coordinates: "45.4642° N, 9.1900° E",
    seed: 5,
    summary: "Cinque capitoli, una sola direzione.",
  },
  {
    slug: "capitolo-iv",
    title: "Capitolo IV",
    edition: "N° 04",
    dateLabel: "30 Dicembre 2024",
    isoDate: "2024-12-30",
    city: "Roma",
    venue: "Terrazza Nord",
    coordinates: "41.9028° N, 12.4964° E",
    seed: 4,
    summary: "L'anno finisce, il capitolo continua.",
  },
  {
    slug: "capitolo-iii",
    title: "Capitolo III",
    edition: "N° 03",
    dateLabel: "14 Settembre 2024",
    isoDate: "2024-09-14",
    city: "Napoli",
    venue: "Molo Sud",
    coordinates: "40.8518° N, 14.2681° E",
    seed: 3,
    summary: "Il mare, la città, la notte.",
  },
  {
    slug: "capitolo-ii",
    title: "Capitolo II",
    edition: "N° 02",
    dateLabel: "2 Giugno 2024",
    isoDate: "2024-06-02",
    city: "Roma",
    venue: "Cortile Aperto",
    coordinates: "41.9028° N, 12.4964° E",
    seed: 2,
    summary: "Ancora prima. Ancora dopo.",
  },
  {
    slug: "capitolo-i",
    title: "Capitolo I",
    edition: "N° 01",
    dateLabel: "9 Marzo 2024",
    isoDate: "2024-03-09",
    city: "Roma",
    venue: "Officina 21",
    coordinates: "41.9028° N, 12.4964° E",
    seed: 1,
    summary: "L'inizio di beforeafter.",
  },
];
