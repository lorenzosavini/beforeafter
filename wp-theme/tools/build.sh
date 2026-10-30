#!/usr/bin/env bash
# Rigenera i file ottimizzati del tema dopo una modifica a JS o CSS.
#   1. assets/app.min.js        da assets/app.js (terser)
#   2. assets/critical/*.css    da style.css e assets/landing.css (serve un sito di prova acceso)
# Uso: TERSER=/percorso/terser PW=/percorso/playwright URLS=pagine.txt tools/build.sh
# pagine.txt: una riga per pagina del sito di prova, «tipo indirizzo»
# (tipi: home, corso, pagina, elenco, landing).
set -euo pipefail
THEME="$(cd "$(dirname "$0")/../infopoint" && pwd)"
"${TERSER:-terser}" "$THEME/assets/app.js" --compress passes=2 --mangle --ecma 2015 --comments false -o "$THEME/assets/app.min.js"
echo "app.min.js: $(wc -c < "$THEME/assets/app.min.js") byte"
if [ -n "${URLS:-}" ]; then
	THEME="$THEME" node "$(dirname "$0")/critical-css.js"
else
	echo "CSS critico non rigenerato (manca URLS)."
fi
