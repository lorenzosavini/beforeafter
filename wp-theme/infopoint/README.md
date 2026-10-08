# Infopoint — tema WordPress per info point UniMarconi

Tema classico, senza page builder e senza plugin obbligatori. Sostituisce
Elementor, Contact Form 7/Flamingo, ACF, Yoast (per l'essenziale) e il banner
cookie esterno.

**Peso:** home completa ≈ 13 KB compressi (HTML + CSS incorporato), un solo
file JS da ~5 KB, zero font esterni, zero jQuery, zero CSS dei blocchi core.

## Installazione

1. Comprimi la cartella `infopoint` in `infopoint.zip` (o usa lo zip fornito)
   e caricala da *Aspetto → Temi → Aggiungi nuovo → Carica tema*. Attivala.
2. *Infopoint → Contenuti iniziali → Crea i contenuti*: crea 9 tipologie,
   99 corsi, 10 agevolazioni, 15 pagine, menu, home e pagina "grazie".
3. *Infopoint → Impostazioni*: città, telefoni, WhatsApp, email, indirizzi,
   ragione sociale e P.IVA, destinatari delle richieste, pagina privacy,
   GTM. *Aspetto → Personalizza → Identità del sito* per il logo.
4. *Impostazioni → Permalink → Salva* (una volta, per gli indirizzi `/corsi/`).

## Cosa fa

| Funzione | Dove |
|---|---|
| Corsi con classe, CFU, durata, accesso, stato iscrizioni, in evidenza | *Corsi* (riquadro «Scheda corso») |
| Tipologie ordinabili, aree/dipartimenti | *Corsi → Tipologie / Aree* |
| Agevolazioni con retta, rata, condizioni | *Corsi → Agevolazioni* |
| Moduli: informazioni, richiamata, prevalutazione CFU con allegati | ovunque, automatici o con shortcode |
| Archivio richieste con stato, note, filtri, esportazione CSV per Excel | *Richieste* |
| Email di notifica con Reply-To e allegati, webhook JSON per CRM | *Impostazioni → Conversione* |
| Antispam (campo esca, tempo minimo firmato, limite per IP) | automatico |
| Provenienza contatto (UTM, gclid, fbclid, referrer) | salvata in ogni richiesta |
| SEO: titoli, meta description, Open Graph, JSON-LD (LocalBusiness, Course, BreadcrumbList, Article), noindex | riquadro «Anteprima su Google»; si spegne se c'è Yoast/Rank Math |
| Sitemap XML | nativa: `/wp-sitemap.xml` |
| GTM con Consent Mode v2 + banner cookie leggero | *Impostazioni → Tracciamento* |
| Eventi dataLayer: `generate_lead`, `contact_click`, `lead_thank_you` | automatici |
| Barra mobile Chiama / WhatsApp / Richiedi info | automatica |

## Shortcode

```
[ip_modulo tipo="info|callback|cfu" titolo="" testo="" pulsante=""]
[ip_corsi tipologia="laurea-triennale,laurea-magistrale" filtro="si|no"]
[ip_agevolazioni]   [ip_sedi]   [ip_contatti]   [ip_passi]   [ip_cta titolo=""]
```

Ogni pagina senza modulo ne riceve uno in fondo («Parla con un orientatore»),
così nessuna pagina resta senza conversione.

## Template di pagina

- **Predefinito** — testo editoriale + modulo finale.
- **Pagina con modulo laterale** — modulo fisso a destra (es. percorsi abilitanti).
- **Landing per campagne** — senza menu, modulo sopra la piega: per Google/Meta Ads.

## Note

- Le schede corso nascono con i soli dati essenziali: presentazione, sbocchi e
  piano di studi vanno scritti (tabelle con il blocco *Tabella*, FAQ con il
  blocco *Dettagli*). Testi originali = migliore posizionamento.
- Gli allegati CFU sono salvati in `uploads/ip-leads/<cartella casuale>/`,
  protetti da `.htaccess`; su Nginx aggiungere `location ~ /ip-leads/ { deny all; }`.
- Per la cache di pagina qualsiasi plugin di cache va bene: i moduli non usano
  nonce e funzionano anche con pagine in cache.
