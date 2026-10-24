# Sito Infopoint Lanciano: pacchetto completo WordPress

Il pacchetto contiene il sito com'è nell'anteprima, già pronto:

- WordPress 7.1.3 in italiano.
- Tema **Infopoint 3.0.6** attivo e configurato.
- Dati di ZDM S.r.l. già inseriti: contatti, orari, sede legale e PEC.
- 159 corsi con 65 piani di studio, documenti e 158 immagini.
- 15 agevolazioni, 12 tipologie, 39 pagine, menu e home.
- Informativa privacy e cookie policy.
- 4 landing per le campagne.
- `database.sql`: il database completo.
- `installa.php`: l'installazione guidata per l'hosting.

**Accesso al pannello:** `/wp-admin`, utente `admin`, password `Infopoint2026!`
(con l'installazione guidata la scegli tu).

---

## A) Su un hosting (consigliato: installazione guidata)

1. Dal pannello dell'hosting crea un **database MySQL vuoto** e annota
   nome, utente, password e server (di solito `localhost`).
2. Estrai lo zip sul computer e carica **tutto il contenuto della cartella
   `infopoint-site`** nella cartella del dominio (di solito `public_html`
   o `httpdocs`), via FTP o dal file manager dell'hosting. Se il file
   manager lo permette, carica lo zip ed estrailo direttamente lì.
3. Apri nel browser **`https://tuodominio.it/installa.php`**.
4. Inserisci i dati del database e una nuova password per `admin`, poi
   premi **Installa**.

   L'installatore:
   - scrive i dati in `wp-config.php` e crea chiavi di sicurezza nuove;
   - importa il database;
   - sostituisce l'indirizzo di prova con il tuo dominio;
   - imposta la password;
   - cancella `database.sql`, `LEGGIMI.md` e se stesso.
5. Entra nel pannello e vai su **Impostazioni → Permalink → Salva** una volta.

Se il dominio usa https, controlla che l'indirizzo nel campo «Indirizzo del
sito» inizi con `https://`.

## B) LocalWP (sul tuo computer)

1. *File → Import site* e scegli **infopoint-per-LocalWP.zip** (il pacchetto
   più leggero, fatto apposta per Local).
2. Local trova da solo `wp-content` e `database.sql`, crea il database e
   sostituisce gli indirizzi.
3. Apri il sito con *Open site* e il pannello con *WP Admin*.

## C) XAMPP / MAMP

1. Estrai lo zip in `htdocs`, in modo da avere `htdocs/infopoint-site`.
2. Crea un database vuoto in phpMyAdmin.
3. Apri `http://localhost/infopoint-site/installa.php` (MAMP:
   `http://localhost:8888/infopoint-site/installa.php`).
4. Inserisci i dati: XAMPP usa utente `root` senza password; MAMP usa
   utente `root` e password `root`.

## Installazione a mano (se non puoi usare installa.php)

1. Importa `database.sql` con phpMyAdmin in un database vuoto.
2. In `wp-config.php` inserisci nome database, utente, password e server.
3. Sostituisci `http://localhost/infopoint-site` con il tuo dominio usando
   il plugin *Better Search Replace*.
4. Vai su *Impostazioni → Permalink → Salva*.
5. Cancella `database.sql`, `installa.php` e `LEGGIMI.md` dal server.

---

## Dopo l'installazione

- **Email delle richieste:** *Infopoint → Impostazioni → Moduli e
  richieste*. Le richieste restano comunque salvate in *Richieste*. Se le
  email non arrivano, installa un plugin SMTP (es. WP Mail SMTP).
- **Logo:** *Aspetto → Personalizza → Identità del sito*.
- **Google Tag Manager:** *Impostazioni → Privacy, cookie e tracciamento*.
- **Banner d'uscita:** *Impostazioni → Moduli e richieste*. Per provarlo
  aggiungi `?prova-banner` all'indirizzo di una pagina.
- **Aggiornamenti dei corsi dal sito ufficiale:** sono automatici ogni
  giorno, con le esclusioni già impostate. Si controllano in
  *Infopoint → Aggiornamenti*.
- **Backup:** installa un plugin di backup (es. UpdraftPlus) appena il
  sito è online.
