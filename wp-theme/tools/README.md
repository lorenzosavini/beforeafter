# Aggiornare i dati ufficiali UniMarconi

Script usati per generare `infopoint/inc/data/*.json` da www.unimarconi.it.
Servono solo a chi mantiene il tema, non vanno caricati su WordPress.

```bash
D=work; mkdir -p $D
curl -s https://www.unimarconi.it/sitemap_index.xml   # elenco delle sitemap
# salva in $D/formazione.urls, $D/piano.urls … gli URL (una riga ciascuno, senza /en/)
cat $D/*.urls | sort -u > $D/all.urls
python3 -I tools/crawl.py $D/all.urls $D/html             # scarica le pagine
python3 -I tools/extract.py $D $D/official.json            # estrae schede, curricula, PDF
python3 -I tools/build.py $D/official.json <vecchio corsi.json> infopoint/inc/data/corsi.json
python3 -I tools/pages.py $D infopoint/inc/data/pagine.json
```

`build.py` confronta i corsi con quelli già presenti (per classe o nome) così
l'importazione aggiorna i corsi esistenti invece di duplicarli. Dopo
l'aggiornamento: *Infopoint → Contenuti ufficiali* nel pannello.
