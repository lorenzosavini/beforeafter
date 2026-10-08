# Infopoint — tema WordPress per info point UniMarconi

Tema classico, senza page builder e senza plugin obbligatori. Sostituisce
Elementor, Contact Form 7/Flamingo, ACF, Yoast (per l'essenziale) e il banner
cookie esterno.

**Peso:** home completa ≈ 15 KB compressi (HTML + CSS incorporato), un solo
file JS da ~6 KB, font Montserrat incluso nel tema (38 KB, disattivabile),
zero jQuery, zero CSS dei blocchi core, zero richieste a server esterni.

**Palette ufficiale UniMarconi** (presa dal CSS di unimarconi.it): verde
`#225e48` (hover `#174f3a`), rosso mattone `#a0300e` (hover `#ca451d`),
grigio `#f0f0f0`, antracite `#373737`, font Montserrat. Tutti i colori sono
modificabili da *Infopoint → Impostazioni → Aspetto*.

## Installazione

1. Comprimi la cartella `infopoint` in `infopoint.zip` (o usa lo zip fornito)
   e caricala da *Aspetto → Temi → Aggiungi nuovo → Carica tema*. Attivala.
2. *Infopoint → Contenuti ufficiali → Avvia*: crea 12 tipologie,
   **159 corsi ufficiali** (lauree, master, master per la didattica,
   percorsi abilitanti, sostegno, corsi di formazione, microcredenziali,
   dottorati) con presentazione, obiettivi, sbocchi, accesso, **65 piani di
   studio** in tabella, documenti PDF ufficiali; 15 agevolazioni; 38 pagine
   (tasse, immatricolazione, trasferimenti, pagamenti, ICA, studenti
   stranieri, Dual Career, DSA, PA 110 e lode…); menu, home, «grazie».
   Poi *Scarica 20 immagini* (ripetere) per le copertine ufficiali.
   Si può rilanciare: «Solo ciò che manca», «Completa» o «Sovrascrivi».
3. *Infopoint → Impostazioni*: città, telefoni, WhatsApp, email, indirizzi,
   ragione sociale e P.IVA, destinatari delle richieste, pagina privacy,
   GTM. *Aspetto → Personalizza → Identità del sito* per il logo.
4. *Impostazioni → Permalink → Salva* (una volta, per gli indirizzi `/corsi/`).

## Cosa fa

| Funzione | Dove |
|---|---|
| Corsi: classe, CFU, durata, accesso, lingua, costo, nota in evidenza, stato iscrizioni, in evidenza in home | *Corsi* (riquadro «Scheda corso») |
| Piani di studio per curriculum (tabelle modificabili) | *Corsi → Piani di studio*, o dal riquadro nel corso |
| Documenti scaricabili (brochure, regolamento…) e FAQ del corso | riquadri nel corso, con «+ Aggiungi» e Libreria media |
| Tipologie ordinabili, dipartimenti, aree tematiche | *Corsi → Tipologie / Dipartimenti / Aree* |
| Agevolazioni con retta, rata, condizioni, dettagli | *Corsi → Agevolazioni* |
| Home: titolo, punti di forza, numeri, sezioni (attiva/ordina), recensioni, FAQ | *Infopoint → Impostazioni → Home page* |
| Passi «come funziona», fasce di contatto, testi dei moduli e dei consensi | *Impostazioni → Testi ricorrenti / Moduli* |
| Sedi d'esame ufficiali (38) | *Impostazioni → Sedi d'esame* |
| Colori e font | *Impostazioni → Aspetto* |
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

## Aggiornamenti automatici dal sito ufficiale

*Infopoint → Aggiornamenti* (impostazioni in *Impostazioni → Aggiornamenti
automatici*).

- Ogni giorno (o settimana) il sito legge le sitemap di unimarconi.it e
  rilegge **solo le pagine modificate**, 4 alla volta, in background.
- Confronta ogni corso con la scheda ufficiale: classe, CFU, durata, costo,
  stato iscrizioni, nota in evidenza (anche quando viene tolta), tipologia,
  dipartimento, testo, documenti PDF, piani di studio (nuovi, cambiati e
  tolti). Trova anche **corsi nuovi** e **corsi tolti** dall'offerta.
- **Agevolazioni**: rilegge a ogni controllo la pagina ufficiale (e le pagine
  dedicate, es. disabilità) e aggiorna retta, rata, destinatari, condizioni e
  dettagli; crea le agevolazioni nuove e mette in bozza quelle tolte.
- Controlla le pagine informative importate (tasse, immatricolazione, corsi
  singoli…) e le sedi d'esame.
- **Con approvazione** (predefinito): ogni differenza compare con «Applica» /
  «Ignora» e arriva un'email di riepilogo. **Automatico**: le modifiche
  vengono applicate subito. In entrambi i casi la versione precedente resta
  nelle *Revisioni*.
- In ogni corso, «Aggiornamento automatico»: *Aggiorna tutto*, *Solo dati,
  piani e documenti* (per i corsi di cui avete riscritto il testo) o *Non
  aggiornare*. Le pagine importate hanno la stessa opzione.
- **Nessuna cifra scritta a mano.** «Retta da … al mese» e retta standard
  si calcolano dalle agevolazioni ufficiali; nei testi (FAQ, riassunti delle
  pagine) si usano i segnaposto `{retta_std}`, `{rata_min}`, `{retta_min}`.
  Le pagine del tema con regole dell'Ateneo (corsi singoli, riconoscimento
  CFU, doppia iscrizione, requisiti) incorporano il testo ufficiale con
  `[ip_ufficiale fonte="…"]`, che si aggiorna da solo.
- Ogni agevolazione ha la stessa opzione «Aggiornamento automatico».
- «Verifica completa» rilegge tutte le ~225 pagine ufficiali (circa 5 minuti).

WP-Cron parte quando qualcuno visita il sito. Su hosting con poco traffico
conviene un cron di sistema: in `wp-config.php`
`define( 'DISABLE_WP_CRON', true );` e dal pannello dell'hosting un'attività
ogni 15 minuti su `https://tuodominio.it/wp-cron.php`.

## Landing per le campagne

*Landing → Crea landing*: una landing da un **corso**, una **tipologia**, un'**agevolazione** o generica; oppure in blocco (una per ogni corso di una tipologia, per ogni agevolazione, per ogni tipologia). Dall'elenco dei corsi e delle agevolazioni c'è anche il link «Crea landing». Indirizzo: `/lp/nome/`.

- Titolo, punti di forza, dati, costi, scheda e piani di studio si leggono dal corso o dall'agevolazione: si aggiornano con il sito ufficiale. Ogni testo si può riscrivere (campi vuoti = automatici).
- Pagina **chiusa**: nessun menu, logo non cliccabile, nessun collegamento verso altre pagine o siti. Chi siamo, privacy e cookie si aprono in finestre sulla pagina. Restano telefono, WhatsApp e moduli.
- Modulo sopra la piega, modulo finale «ti richiamiamo», barra fissa su mobile, ringraziamento **sulla pagina** (eventi `generate_lead` e `lead_thank_you` per Google Ads/GTM).
- Su computer, un solo invito «Prima di andare, ti richiamiamo noi?» quando il mouse esce dalla finestra. Il tasto Indietro **non** viene bloccato: Google Ads lo considera una pratica scorretta.
- Ogni richiesta registra la landing di provenienza; nell'elenco landing c'è il conteggio delle richieste.
- Non indicizzate di default (servono alle campagne).
- **Mappa delle sedi d'esame**: l'Italia per regioni con un punto per ogni sede (elenco ufficiale sincronizzato); un clic sulla regione apre gli indirizzi. Le sedi nuove si posizionano da sole sulla città, o al centro della regione se la città non è in elenco.
- **Scheda del corso a schede** (obiettivi, sbocchi, accesso…) letta dalla scheda ufficiale.
- **Chi ti segue**: testo, foto della sede e persone in *Infopoint → Impostazioni → Landing*. Una foto vera della sede o del gruppo è l'elemento che rende la pagina credibile: caricatela.
- Titoli in *Source Serif 4* (OFL, `assets/fonts/`), testi in Montserrat.
- Confini regionali: [openpolis/geojson-italy](https://github.com/openpolis/geojson-italy) (CC BY 4.0), semplificati con `tools/map.py`; coordinate delle città da OpenStreetMap/Nominatim (ODbL), con `tools/geo.py`.

## Shortcode

```
[ip_modulo tipo="info|callback|cfu" titolo="" testo="" pulsante=""]
[ip_corsi tipologia="laurea-triennale,laurea-magistrale" filtro="si|no"]
[ip_agevolazioni]   [ip_sedi]   [ip_contatti]   [ip_passi]   [ip_faq]   [ip_cta titolo=""]
[ip_ufficiale fonte="https://www.unimarconi.it/…/"]   testo ufficiale sempre aggiornato
```

Ogni pagina senza modulo ne riceve uno in fondo («Parla con un orientatore»),
così nessuna pagina resta senza conversione.

## Template di pagina

- **Predefinito** — testo editoriale + modulo finale.
- **Pagina con modulo laterale** — modulo fisso a destra (es. percorsi abilitanti).
- **Landing per campagne** — senza menu, modulo sopra la piega: per Google/Meta Ads.

## Note

- I testi dei corsi sono quelli ufficiali dell'Ateneo, in blocchi Gutenberg
  modificabili. Google tende a non premiare i testi identici a quelli di un
  altro sito: conviene riscrivere almeno l'introduzione dei corsi su cui si
  investe in campagne.
- Rispetto all'offerta ufficiale, 7 corsi del sito dell'Aquila non risultano
  più attivi (perfezionamento WHA/WBE/WLM, certificazioni LIM e tablet, Master
  in International Management e Banking and Finance in inglese): con
  «Metti in bozza i corsi non più in offerta» si nascondono.
- Importi e scadenze sono aggiornati a ottobre 2026: verificarli prima delle
  campagne. Gli script per riscaricarli sono in `wp-theme/tools/`.
- Gli allegati CFU sono salvati in `uploads/ip-leads/<cartella casuale>/`,
  protetti da `.htaccess`; su Nginx aggiungere `location ~ /ip-leads/ { deny all; }`.
- Per la cache di pagina qualsiasi plugin di cache va bene: i moduli non usano
  nonce e funzionano anche con pagine in cache.
