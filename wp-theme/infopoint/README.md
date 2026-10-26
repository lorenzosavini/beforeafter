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
| Email: notifica HTML a voi (dati, pagina di invio, UTM, pulsanti Chiama/WhatsApp) e conferma alla persona che scrive, SMTP integrato senza plugin, email di prova, esito di ogni invio salvato nella richiesta e avviso se le email non partono; webhook JSON per CRM | *Impostazioni → Email e notifiche* |
| Antispam (campo esca, tempo minimo firmato, limite per IP) | automatico |
| Provenienza del contatto: pagina di invio, pagina di ingresso, canale (Google Ads, Meta Ads, ricerca organica, social, diretto…), utm_source/medium/campaign/term/content, gclid/gbraid/wbraid/fbclid/msclkid, sito di provenienza e primo contatto (90 giorni) | colonne e filtro per canale in *Richieste*, scheda, email, CSV e webhook |
| SEO: titoli, meta description, Open Graph, JSON-LD (LocalBusiness, Course, BreadcrumbList, Article), noindex | riquadro «Anteprima su Google»; si spegne se c'è Yoast/Rank Math |
| Presentazione dei corsi | il testo introduttivo della scheda ufficiale (obiettivi e descrizione) viene importato come sezione «Presentazione», senza avvisi, scadenze e contatti dell’Ateneo |
| Sitemap XML | nativa: `/wp-sitemap.xml`, senza ciò che è nascosto in Visibilità, le pagine «non mostrare nei motori», il ringraziamento, le landing non indicizzate e le categorie rimaste senza corsi visibili |
| Tag Google Tag Manager, Google Analytics 4 e Google Ads (conversione «Richiesta» con ID anti-doppioni, conversione «Clic su telefono/WhatsApp», conversioni avanzate facoltative) con modalità di consenso v2; banner cookie a categorie (necessari, statistiche, marketing) con Rifiuta / Personalizza / Accetta tutti, riproposto dopo 6 mesi o quando cambia la cookie policy; registro dei consensi esportabile | *Impostazioni → Privacy, cookie e tracciamento*, *Infopoint → Registro consensi* |
| Informativa privacy e cookie policy generate dai dati della società (`[ip_privacy]`, `[ip_cookie]`): si aggiornano da sole se cambiano ragione sociale, recapiti o strumenti attivi | Pagine «Informativa privacy» e «Cookie policy»; tempi di conservazione, fornitori e DPO in *Privacy, cookie e tracciamento* |
| Eventi dataLayer: `generate_lead` (con `lead_id`, valore e, se attive, i dati per le conversioni avanzate), `contact_click`, `lead_thank_you`, `exit_intent_shown`, `ip_consent_ready`, `ip_consent_update` | automatici |
| Barra mobile Chiama / WhatsApp / Richiedi info | automatica |
| Banner d'uscita con richiesta di richiamata: da computer quando il mouse va verso la chiusura, da telefono quando si torna su di scatto dopo aver letto. Una volta per visita, mai dopo una richiesta inviata né nella pagina di ringraziamento. Evento `exit_intent_shown` | *Impostazioni → Moduli e richieste* (attiva, solo computer, titolo, testo, punti) |

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
- Gli aggiornamenti dal sito ufficiale si applicano **in automatico** (Impostazioni → Aggiornamenti automatici → «Applica in automatico»): ogni modifica passa prima dalle esclusioni qui sotto, anche al momento di scrivere.
- **Cosa non riportare dal sito ufficiale** (resta valido a ogni aggiornamento):
  - in ogni pagina o corso importato, riquadro «Aggiornamento dal sito
    ufficiale»: *Mostra solo queste sezioni* e *Non importare queste sezioni*
    (un titolo per riga). Le sezioni escluse spariscono subito e non tornano;
  - *Impostazioni → Aggiornamenti automatici → Frasi da non importare mai*:
    ogni paragrafo o voce di elenco che contiene una di queste frasi viene
    scartato ovunque (predefinita: il rimando alla doppia iscrizione);
  - una pagina o un corso ufficiale messo nel cestino finisce in *Pagine e
    corsi ufficiali da non importare*: non viene più ricreato dall'importazione
    né riproposto dagli aggiornamenti. Togliendo la riga torna disponibile.
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

## Il pannello

- **Infopoint → Bacheca**: richieste da contattare, di oggi, degli ultimi 7 e 30 giorni, ultime richieste con il pulsante per chiamare, canali e pagine che portano più contatti, lista «Da completare» (email, logo, dati legali, privacy, tag) e scorciatoie.
- **Infopoint → Impostazioni**: dieci gruppi (Azienda e contatti, Home page, Moduli e testi, Email e notifiche, Tracciamento e cookie, Privacy, Landing e sedi, Colori e font, Aggiornamenti corsi, Avanzate) divisi in schede; i campi secondari compaiono solo quando servono (es. i dati SMTP dopo aver attivato l’SMTP) e la pagina avvisa se si esce senza salvare.
- **Richieste**: pagina dedicata con ricerca istantanea (nome, telefono anche scritto in modo diverso, email, città, corso, messaggio, campagna; tasto «/» per cercare), filtri combinabili (periodo con date a scelta, canale, corso, campagna, landing, modulo, consenso marketing, ordine) con le etichette dei filtri attivi, schede per stato con i conteggi, stato modificabile dall’elenco, scheda laterale con pulsanti Chiama / WhatsApp / Email, stato, note interne e storico, selezione multipla (segna come, esporta, cestino, ripristina), esportazione CSV dei risultati filtrati; il link «Apri nel pannello» delle email apre direttamente la scheda.
- **Corsi** con l’editor classico, così scheda, piani di studio, documenti e FAQ restano sempre in vista; filtro per tipologia.
- **Infopoint → Visibilità**: mostra o nasconde con un interruttore corsi singoli e tipologie intere (spariscono da elenchi, ricerca, menu, landing e mappa del sito; chi apre l’indirizzo va al livello superiore), le sezioni a tendina dei corsi per titolo in tutti i corsi, le parti della scheda corso (foto, scheda dati, retta, stato, piani di studio, documenti, FAQ, passi, altri corsi), agevolazioni e convenzioni (escluse anche dal «retta da … al mese»), le pagine (privacy, cookie, home e ringraziamento esclusi), le sezioni della home e gli elementi comuni (barra mobile, WhatsApp, telefono e pulsante nel menu, modulo in fondo, fascia di contatto, percorso di navigazione, banner d’uscita). Ricerca nelle liste, «Mostra / Nascondi tutto», «Solo i nascosti». Ogni corso ha il riquadro «Visibilità di questo corso» per le eccezioni. Nulla viene cancellato e gli aggiornamenti dal sito ufficiale continuano.
- **Versione essenziale dei corsi** (Infopoint → Visibilità, prima scheda): pensata per un infopoint, non per l’università. Ogni corso mostra ciò che serve per decidere di contattarvi, in breve: presentazione e obiettivo, a chi è rivolto e requisiti, i nomi dei moduli, sbocchi, titolo, durata e costo. Gli approfondimenti da dare dopo il contatto (docenti, pagamenti e rate, stage, esami, normative, procedura di iscrizione, riconoscimento crediti, sezioni non riconosciute) escono dal testo e diventano pulsanti nel riquadro «Da approfondire con un orientatore»: ogni pulsante apre il modulo con il messaggio già scritto («Vorrei informazioni su: Come pagare a rate – nome del corso»). Le sezioni accorciate hanno inviti mirati («Ricevi il piano di studi completo», «Verifichiamo insieme i tuoi requisiti»…) e la brochure si riceve su richiesta. Il testo completo resta salvato e aggiornato; ogni corso può mostrare sempre la versione completa.
  Ogni sezione mostra una quantità di testo uniforme (circa 80 parole la presentazione, 60 obiettivi e sbocchi, 45 costi, durata e titolo, 70 ogni curriculum), chiusa sempre a fine frase; le frasi che annunciano un elenco («Il corso prepara a:») portano con sé le prime voci, le voci lunghissime si accorciano alla prima frase, nei programmi restano solo i nomi dei moduli e i titoli rimasti senza testo o le frasi sospese si tolgono.
- **Versione essenziale delle pagine informative** (stessa scheda, interruttore separato): le sezioni di approfondimento (certificati e more, normative, procedure, pagamenti…) diventano pulsanti nello stesso riquadro, ma mai l’argomento della pagina stessa; nelle altre sezioni si accorcia il testo descrittivo, mentre importi, date e righe brevi restano. Le sezioni che sono elenchi lunghissimi (es. il prezzo di ogni master) mostrano solo l’apertura con l’invito. Moduli, schede delle agevolazioni, elenchi dei corsi e contatti restano interi. Home, privacy, cookie, contatti, ringraziamento e landing non vengono mai toccate; nell’elenco «Pagine da accorciare» si spengono le pagine da lasciare complete.
- **Landing**: si creano solo dal generatore; nell’elenco c’è il pulsante per copiare l’indirizzo da usare negli annunci.

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
