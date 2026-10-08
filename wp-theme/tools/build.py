import sys,os,re,json,html as H,unicodedata
sys.path.insert(0,os.path.dirname(os.path.abspath(__file__)))
from blocks import to_blocks
src,old_path,out_path=sys.argv[1:4]
d=json.load(open(src)); old=json.load(open(old_path))
CAT={'Laurea Triennale':'laurea-triennale','Laurea Magistrale':'laurea-magistrale','Laurea Magistrale a Ciclo Unico':'laurea-magistrale-a-ciclo-unico','Master di I livello':'master-di-i-livello','Master di II livello':'master-di-ii-livello','Master didattica I livello':'master-di-i-livello-discipline-per-la-didattica','Master didattica II livello':'master-di-ii-livello-discipline-per-la-didattica','Formazione iniziale abilitazione docente':'percorsi-abilitanti','Percorsi di specializzazione sul sostegno':'specializzazione-sostegno','Corso di formazione':'corsi-di-formazione','Microcredenziali':'microcredenziali','Dottorato di ricerca':'dottorato-di-ricerca'}
PREFIX={'laurea-triennale':'Laurea triennale in','laurea-magistrale':'Laurea magistrale in','laurea-magistrale-a-ciclo-unico':'Laurea magistrale a ciclo unico in','master-di-i-livello':'Master di I livello in','master-di-ii-livello':'Master di II livello in','master-di-i-livello-discipline-per-la-didattica':'Master di I livello in','master-di-ii-livello-discipline-per-la-didattica':'Master di II livello in','corsi-di-formazione':'Corso di formazione in','microcredenziali':'Microcredenziale in','dottorato-di-ricerca':'Dottorato di ricerca in','percorsi-abilitanti':'','specializzazione-sostegno':''}
DEP={'scienze economico-aziendali, giuridiche e politiche':'Dipartimento di Scienze Economico-Aziendali, Giuridiche e Politiche','scienze umane':'Dipartimento di Scienze Umane','scienze ingegneristiche':'Dipartimento di Scienze Ingegneristiche'}
def norm(s): return re.sub(r'[^a-z0-9]+',' ',unicodedata.normalize('NFKD',s.replace('’',"'")).encode('ascii','ignore').decode().lower()).strip()
def slugify(s): return norm(s).replace(' ','-')
def dep(s):
    k=norm(re.sub(r'\(.*?\)','',s)).replace('dipartimento di ','').replace('dipartimento ','')
    for a,b in DEP.items():
        if norm(a)==k: return b
    return s
def doclabel(lab,url):
    f=os.path.basename(url).lower()
    if 'in_breve' in f: return 'Il corso in breve'
    if 'regolamento_didattico' in f or 'regolamento didattico' in lab.lower(): return 'Regolamento didattico'
    if 'brochure' in f or 'brochure' in lab.lower(): return 'Brochure'
    if re.search(r'_b6_',f): return None
    if 'cpds' in f or 'gaq' in f: return None
    if lab.lower() in ('download','scarica','qui'): lab=os.path.basename(url).rsplit('.',1)[0].replace('_',' ').replace('-',' ')
    return lab[:1].upper()+lab[1:]
def clean_html(c):
    c=re.sub(r'<h3>\s*Piani di Studio\s*</h3>\s*<ul>.*?</ul>','',c,flags=re.S|re.I)
    c=re.sub(r'<a href="[^"]+\.pdf">\s*Visualizza la brochure\s*</a>','',c,flags=re.I)
    c=re.sub(r'Riproduci video','',c)
    c=re.sub(r'<p>\s*Presentazione del Corso di Laurea[^<]*</p>','',c)
    c=re.sub(r'<a href="(?!https?://[^"]+\.pdf)[^"]*">(.*?)</a>',r'\1',c)
    c=re.sub(r'<h3>\s*<p>(.*?)</p>\s*</h3>',r'<h3>\1</h3>',c)
    c=re.sub(r'<p>[^<]*(@unimarconi\.it|06-377|\+39-06|tel\.)[^<]*</p>','',c,flags=re.I)
    c=re.sub(r'<li>[^<]*(@unimarconi\.it|06-377|\+39-06)[^<]*</li>','',c,flags=re.I)
    return c
oldmap={norm(o['short']):o for o in old}
out=[];seen=set()
for o in d:
    t=CAT.get(o['cat'])
    if not t: continue
    name=o['name']
    code=o['classe'].strip()
    mcode=re.search(r'\(([0-9A-Z]{2,5})\)\s*$',name)
    if not code and mcode: code=mcode.group(1); name=name[:mcode.start()].strip()
    short=re.sub(r'\s*\b(L-?\d+|LM-?\d+|LMG-?\d+)\b.*$','',name).strip() if o['classe'].strip() else name
    short=re.sub(r'^(Corso di formazione in |Master di I+ livello in )','',short).replace('\xa0',' ').strip()
    short=re.sub(r'\s+interclasse$','',short)
    code=code.replace(' ',' / ') if re.match(r'L-7 L-9',code) else code
    pre=PREFIX[t]
    if re.match(r'(Corso|Percors)',short): pre=''
    title=(pre+' '+short).strip() if pre else short
    if code: title+=' (%s)'%code
    s=o['slug']
    if s in seen: continue
    seen.add(s)
    secs=[]
    for h,c in o['sections']:
        c=clean_html(c)
        if not re.sub('<[^>]+>','',c).strip(): continue
        secs.append('<h2>%s</h2>\n%s'%(H.escape(h),c))
    html='\n'.join(secs)
    note=o['note'].strip()
    evid=note.strip('() ') if note and len(note)<200 else ''
    stato='chiuse' if re.search(r'iscrizioni chiuse|bandi sono attualmente chiusi',evid+o['note']+o['quota'],re.I) else ''
    if re.match(r'(iscrizioni chiuse|da un.idea di)$',evid,re.I): evid=''
    evid=re.sub(r'Periodo apertura Bandi','Periodo apertura bandi: ',evid).replace('.Seguiranno','. Seguiranno')
    content=to_blocks(html)
    docs=[]
    for lab,u in o['docs']:
        l=doclabel(lab,u)
        if l and u not in [x[1] for x in docs]: docs.append([l,u])
    q=re.search(r'€\s?([\d\.]+(?:,\d+)?)',o['quota'])
    retta='€ '+q.group(1) if q else ''
    oldm=None
    if code:
        for v in old:
            if v['code'].replace(' ','').replace('/','|').replace('|','')==code.replace(' ','').replace('/','') and v['tipologia'][:6]==t[:6] and not (code.startswith('L') and v['tipologia']!=t): oldm=v;break
    oldm=oldm or oldmap.get(norm(short))
    if not oldm:
        for k,v in oldmap.items():
            a,b=norm(short),k
            if a and (b.startswith(a) or a.startswith(b) or a.split()[:3]==b.split()[:3]) and v['tipologia'][:6]==t[:6]: oldm=v;break
    curr=[{'name':re.sub(r'\s*\((?:L|LM|LMG)-?[\d /L-]+\)$','',c['name']).strip(),'slug':c['slug'],'content':to_blocks(clean_html(c['html']))} for c in o['curricula']]
    lingua='Inglese' if re.search(r'english|inglese',name,re.I) else ''
    out.append(dict(slug=s,old=oldm['slug'] if oldm else '',title=title,short=short,code=code,tipologia=t,dipartimento=dep(o['dep']) if o['dep'] else '',area=o['area'],cfu=o['cfu'],durata=o['durata'],retta=retta,lingua=lingua,evidenza=evid,stato=stato,source=o['url'],image=o['image'],docs=docs,content=content,curricula=curr))
json.dump(out,open(out_path,'w'),ensure_ascii=False,separators=(',',':'))
import collections
print(len(out),collections.Counter(x['tipologia'] for x in out))
used=[x['old'] for x in out if x['old']]; print('dup',[u for u in used if used.count(u)>1]); print('matched old:',sum(1 for x in out if x['old']),'/',len(old))
olds=set(x['old'] for x in out)
print('L\'Aquila non presenti nell\'ufficiale:',[o['short'] for o in old if o['slug'] not in olds])
