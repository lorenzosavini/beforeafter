import sys,os,re,json,html as H
sys.path.insert(0,os.path.dirname(os.path.abspath(__file__)))
from clean import clean, sections
from blocks import to_blocks
D=sys.argv[1]
PAGES=[
 ('tasse-di-iscrizione','Tasse di iscrizione','tasse-di-iscrizione',None),
 ('modalita-di-pagamento','Modalità di pagamento','modalita-di-pagamento',None),
 ('immatricolazione','Come immatricolarsi','immatricolazione-corso-di-laurea',None),
 ('trasferimento-da-altro-ateneo','Trasferimento da un altro ateneo','immatricolazione-con-trasferimento-da-altro-ateneo',None),
 ('iscrizione-master','Iscrizione ai master','iscrizione-master',None),
 ('integrazioni-curriculari-ica','Integrazioni curriculari per le magistrali (ICA)','iscrizione-integrazioni-curriculari-accesso-magistrali-ica',None),
 ('studenti-stranieri','Studenti stranieri e titoli esteri','studenti-stranieri-e-titoli-esteri',None),
 ('dual-career','Programma Dual Career per atleti','programma-dual-career',None),
 ('carriere-alias','Carriere Alias','carriere-alias',None),
 ('studenti-con-disabilita-e-dsa','Studenti con disabilità e DSA','orientamento-accoglienza-studenti-disabili',None),
 ('agevolazione-disabilita','Agevolazioni per studenti con disabilità','agevolazioni-studenti-con-disabilita-pari-o-superiore-al-45',None),
 ('pa-110-e-lode','PA 110 e lode','pa-110-e-lode',None),
 ('prestito-per-merito','Prestito per Merito','prestito-per-merito-investi-nel-tuo-futuro-con-unimarconi-e-intesa-sanpaolo',None),
 ('perche-scegliere-unimarconi','Perché scegliere UniMarconi','perche-scegliere-unimarconi',None),
 ('test-orientativo','Test orientativo','test-orientativo',None),
 ('esame-di-laurea','Esame di laurea','esame-di-laurea',None),
 ('doppia-iscrizione-normativa','Doppia iscrizione: la normativa','iscrizione-contemporanea-a-due-corsi-di-studio-di-istruzione-superiore',None),
 ('corsi-singoli-ufficiale','Corsi singoli: regolamento','iscrizione-ai-corsi-singoli',None),
 ('riconoscimento-cfu-ufficiale','Riconoscimento CFU: regole d\'Ateneo','riconoscimento-cfu',None),
]
BL=re.compile(r'contatt|per informazioni|informazioni$|link utili|orari|segreteria|ufficio|^$',re.I)
def strip(c):
    c=re.sub(r'<a href="(?!https?://[^"]+\.pdf)[^"]*">(.*?)</a>',r'\1',c)
    c=re.sub(r'<(p|li)>[^<]*(@unimarconi\.it|06-377|\+39-06|\+39 06)[^<]*</\1>','',c,flags=re.I)
    return c
out={}
for slug,title,src,_ in PAGES:
    p=D+'/html/'+src+'.html'
    if not os.path.exists(p): print('missing',src); continue
    h=open(p,errors='ignore').read()
    a=h.find('</h1>'); b=h.find('</main>')
    if a<0: continue
    x,pdfs,_=clean(h[a:b]); intro,secs=sections(x)
    parts=[strip(intro)] if re.sub('<[^>]+>','',intro).strip() else []
    for t,c in secs:
        if BL.search(t): continue
        parts.append('<h2>%s</h2>\n%s'%(H.escape(t),strip(c)))
    html='\n'.join(parts)
    html=re.sub(r'(<h[2-4]>[^<]*</h[2-4]>\s*)+(?=<h2>|$)',lambda m: m.group(0) if False else '',html) if False else html
    out[slug]={'title':title,'source':'https://www.unimarconi.it/'+src+'/','content':to_blocks(html)}
    print(slug,len(out[slug]['content']))
json.dump(out,open(sys.argv[2],'w'),ensure_ascii=False,separators=(',',':'))
