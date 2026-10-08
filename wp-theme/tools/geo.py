import json, time, urllib.request, urllib.parse, sys
cities = """L'Aquila,Pescara,Chieti,Teramo,Potenza,Matera,Catanzaro,Cosenza,Crotone,Reggio Calabria,Vibo Valentia,Napoli,Avellino,Benevento,Caserta,Salerno,Capaccio Paestum,Bologna,Ferrara,Forlì,Cesena,Modena,Parma,Piacenza,Ravenna,Reggio Emilia,Rimini,Trieste,Gorizia,Pordenone,Udine,Roma,Frosinone,Latina,Rieti,Viterbo,Genova,Imperia,La Spezia,Savona,Chiavari,Milano,Bergamo,Brescia,Como,Cremona,Lecco,Lodi,Mantova,Monza,Pavia,Sondrio,Varese,Ancona,Ascoli Piceno,Fermo,Macerata,Pesaro,Urbino,Campobasso,Isernia,Torino,Alessandria,Asti,Biella,Cuneo,Novara,Verbania,Vercelli,Bari,Barletta,Andria,Trani,Brindisi,Foggia,Lecce,Taranto,Cagliari,Nuoro,Oristano,Sassari,Olbia,Carbonia,Palermo,Agrigento,Caltanissetta,Catania,Enna,Messina,Ragusa,Siracusa,Trapani,Firenze,Arezzo,Grosseto,Livorno,Lucca,Massa,Carrara,Pisa,Pistoia,Prato,Siena,Chiusi,Trento,Bolzano,Perugia,Terni,Aosta,Venezia,Mestre,Belluno,Padova,Rovigo,Treviso,Verona,Vicenza""".split(',')
out = {}
for c in cities:
    q = urllib.parse.urlencode({'city': c, 'country': 'Italy', 'format': 'json', 'limit': 1})
    req = urllib.request.Request('https://nominatim.openstreetmap.org/search?' + q, headers={'User-Agent': 'infopoint-theme-build/1.0'})
    try:
        r = json.load(urllib.request.urlopen(req, timeout=30))
        if r:
            out[c] = [round(float(r[0]['lat']), 4), round(float(r[0]['lon']), 4)]
    except Exception as e:
        print('ERR', c, e, file=sys.stderr)
    time.sleep(1.1)
json.dump(out, open(sys.argv[1], 'w'), ensure_ascii=False, indent=0)
print(len(out), 'città')
