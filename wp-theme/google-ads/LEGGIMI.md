# Campagna Search – Infopoint Lanciano (Google Ads Editor)

File: `campagna-search-infopoint-lanciano.csv` (UTF-8, una campagna, tre gruppi di annunci, tutto verso la home).

## Importare
1. Google Ads Editor → scarica l'account aggiornato.
2. **Account → Importa → Da file…** → scegli il CSV.
3. Controlla l'anteprima (colonne riconosciute, nessun errore) → **Elabora** → **Mantieni le modifiche proposte**.
4. Completa le impostazioni qui sotto, poi **Pubblica**. La campagna arriva **in pausa**: la attivi tu quando è tutto pronto.

## Cosa contiene
| Gruppo | Per chi | Parole chiave |
|---|---|---|
| Generico – Università online | chi cerca università online / telematica in zona | 17 × (frase + esatta) |
| Brand – Marconi Lanciano | chi cerca già l'Università Marconi in zona | 12 × (frase + esatta) |
| Lavoratori e crediti CFU | lavoratori, seconda laurea, riconoscimento crediti | 9 × (frase + esatta) |

- Un annuncio adattabile per gruppo: 15 titoli (5 con le parole chiave principali, in forma informativa) e 3 descrizioni, percorso `/Orientamento/Lanciano`. Testi sobri: dicono solo cosa fate (orientamento, informazioni, assistenza all'iscrizione, valutazione dei crediti). Niente «Infopoint» nei testi, niente prezzi, superlativi o promesse sulle caratteristiche dei corsi (frequenza, valore legale, tempi). In ogni gruppo una descrizione dice «Centro di orientamento accreditato Università G. Marconi. Non siamo la sede dell'Ateneo.»
- 55 parole chiave escluse a livello di campagna: studenti già iscritti (login, esami, piattaforma), altre università telematiche, lavoro, materiale gratis, omonimie «Marconi» (radio, via, liceo, aeroporto…), corsi non offerti (medicina…).
- Offerta: **Massimizza conversioni**. Budget **20 €/giorno** (segnaposto: su tutta Italia si esaurisce presto, mettilo tu). Rete: solo Ricerca Google, lingua italiano.
- Suffisso URL finale con UTM, così le richieste arrivano nel pannello con campagna, gruppo e parola chiave.

## Asset (file separati, da importare dopo la campagna)
Stessa procedura: **Account → Importa → Da file…**, un file alla volta. Tutti gli asset sono collegati alla campagna.

| File | Contenuto |
|---|---|
| `asset-sitelink.csv` | 12 sitelink: Corsi di laurea online · Master I e II livello · Corsi per insegnanti · Percorsi abilitanti · Riconoscimento CFU · Costi e agevolazioni · Come iscriversi · Corsi singoli · Doppia iscrizione · Formazione e dottorati · Chi siamo · Contatti e orari |
| `asset-callout.csv` | 8 callout: Orientamento ai corsi · Assistenza all'iscrizione · Valutazione dei crediti · Consulenza senza impegno · Sede a Lanciano · Lun–ven 10–13 e 15–18 · Contatto anche WhatsApp · Gestito da ZDM S.r.l. |
| `asset-snippet.csv` | 2 snippet strutturati: *Corsi di laurea* (Lauree triennali, Lauree magistrali, Master I e II livello, Percorsi abilitanti, Corsi singoli, Corsi di formazione) e *Catalogo dei servizi* (Orientamento, Informazioni sui corsi, Assistenza iscrizione, Valutazione crediti) |
| `asset-call.csv` | Chiamata: 0872 676609 (Italia) |

Tutti i link sono stati verificati (pagine attive). Testi sobri, nessun prezzo: la pagina Costi e agevolazioni è solo linkata.
Se l'Editor non riconosce l'intestazione di uno snippet (`Degree programs`, `Service catalog`), sceglila dall'elenco: «Corsi di laurea» e «Catalogo dei servizi».

## Da impostare a mano nell'Editor
- **Località** (Campagna → Località): **Italia**; *Opzioni località* → «Presenza: persone che si trovano o si trovano regolarmente nelle località scelte» (non «interesse», che porta anche clic dall'estero). Le parole chiave con Lanciano, Chieti e Abruzzo restano utili per chi cerca in zona; quelle generiche (università telematica, laurea online, lavoratori, CFU) lavorano su tutta Italia. Il gruppo Brand ha volutamente solo ricerche «Marconi + zona»: le ricerche nazionali sull'Università Marconi sono dell'Ateneo e degli altri centri, e puntarci aumenta il rischio di sembrare l'Università.
- **Orari della chiamata**: nell'asset chiamata imposta la pianificazione lun–ven 10–13 e 15–18, così il pulsante compare solo quando rispondete.
- **Obiettivi**: conversioni principali = Richiesta dal modulo + Chiamate dagli annunci (≥ 60 s).
- **Asset che non si importano da file** (da Google Ads, Asset):
  - *Nome e logo dell'attività*: lo stesso nome con cui vi presentate sul sito e nella verifica dell'inserzionista, con il vostro logo (non quello dell'Ateneo).
  - *Località*: collega il Profilo dell'attività su Google della sede di Lanciano, se ce l'avete.
  - *Immagini*: 2–4 foto vostre (sede, persone al lavoro), non foto o loghi dell'Università.
- **Da non usare per ora**: asset prezzo, promozione e modulo per i lead (coerenza con la home senza prezzi e meno elementi da far revisionare).

## Note
- Se un titolo con «Marconi» viene limitato per marchio, serve l'autorizzazione dell'Università (modulo di autorizzazione marchi di Google).
- Con l'account sospeso l'Editor può non pubblicare: puoi preparare tutto e pubblicare dopo la riattivazione.
