import sys,os,re,json,html as H
sys.path.insert(0,os.path.dirname(os.path.abspath(__file__)))
from clean import clean, sections
D=sys.argv[1]
def slug(u): return u.replace('https://www.unimarconi.it/','').strip('/').replace('/','__')
form=[l.strip() for l in open(D+'/formazione.urls') if l.strip()]
piano=set(slug(l.strip()) for l in open(D+'/piano.urls') if l.strip())
BL=re.compile(r'gruppi aq|elenco documenti|archivio cicli|dicono di noi|modulistica|^documenti$|pubblicazioni|dottori di ricerca|opinioni|sei interessat|per informazioni|segreteria|ufficio|informazioni utili|testimonianz|sala studio|seminari|^eventi$|docenti|comitato|assicurazione|gruppo aq|pagamento|scegli come|corso in breve|regolamento|quota di iscri|perch[eé] scegliere|open day|pagopa|addebito|prestito|iscriviti ora|contatt|^$',re.I)
def txt(x): return H.unescape(re.sub('<[^>]+>','',x)).strip()
def header(h,seg):
    f={}
    for k,v in re.findall(r'<strong>\s*(Classe|Titolo|Durata|CFU|Dipartimento|Area|Ore|Modalit[àa] di erogazione|Lingua)\s*</strong>\s*(?:<br\s*/?>)?\s*<(?:span|p)[^>]*>(.*?)</(?:span|p)>',seg,re.S):
        f.setdefault(k.lower(),txt(v))
    return f
def piano_extract(s):
    p=D+'/html/'+s+'.html'
    if not os.path.exists(p): return None
    h=open(p,errors='ignore').read()
    a=h.find('</h1>')
    if a<0: a=h.find('<main')
    b=h.find('</main>')
    x,_,_=clean(h[a:b]); intro,secs=sections(x)
    name=None; body=[]
    for t,c in secs:
        if t.lower().startswith('curriculum') or t.lower().startswith('orientamento') or t.lower().startswith('piano'):
            if not name: name=t; body.append(c); continue
        if BL.search(t): continue
        if name: body.append('<h2>%s</h2>%s'%(H.escape(t),c))
    if not name:
        m=re.search(r'<h1[^>]*>(.*?)</h1>',h,re.S); name=txt(m.group(1)) if m else s
    return {'slug':s,'name':name,'html':'\n'.join(body)}
out=[]
for u in form:
    s=slug(u)
    if s in ('formazione',) or s.startswith('en__'): continue
    p=D+'/html/'+s+'.html'
    if not os.path.exists(p): continue
    h=open(p,errors='ignore').read()
    i=h.find('<h1'); b=h.find('</main>')
    if i<0: continue
    m=re.search(r'<h1[^>]*>(.*?)</h1>',h[i:],re.S)
    hh=m.group(1)
    cat=re.search(r'<small[^>]*>(.*?)</small>',hh,re.S); cat=txt(cat.group(1)) if cat else ''
    name=txt(re.sub(r'<small[^>]*>.*?</small>','',hh,flags=re.S))
    pre=h[max(0,i-1500):i]
    dep=re.search(r'<span>\s*(Dipartimento[^<]*)</span>',pre)
    img=re.search(r'<img[^>]*class="[^"]*wp-post-image[^"]*"[^>]*src="([^"]+)"',h[i:b]) or re.search(r'<img[^>]*src="([^"]+)"[^>]*class="[^"]*wp-post-image',h[i:b])
    seg=h[i:b]
    f=header(h,seg)
    a=h.find('CONTENUTO STANDARD',i)
    x,pdfs,links=clean(h[a if a>0 else i+len(m.group(0)):b])
    for k_,v_ in re.findall(r'<strong>\s*(Dipartimento|Area)\s*</strong>\s*<p>(.*?)</p>',x):
        f.setdefault(k_.lower(),txt(v_))
    intro,secs=sections(x)
    keep=[];quota=''
    for t,c in secs:
        if re.search('quota di (iscri|partec)|^costi?$',t,re.I) and not quota: quota=txt(c.split('</p>')[0])+(' '+txt(c.split('</p>')[1]) if c.count('</p>')>1 else '')
        if BL.search(t): continue
        if re.search(r'perch[eé]',t,re.I): continue
        keep.append([t,c])
    note=' '.join(txt(p_) for p_ in re.findall(r'<p>(.*?)</p>',intro) if not re.match(r'(Scienze|Area|Dipartimento|Corso di formazione|CONTENUTO)',txt(p_)))
    curr=[]
    for t_,l in links:
        ls=slug(l.split('#')[0]) if l.startswith('https://www.unimarconi.it/') else ''
        if ls in piano and ls not in [c['slug'] for c in curr]:
            pe=piano_extract(ls)
            if pe: curr.append(pe)
    docs=[]
    for t_,l in pdfs:
        if l in [d[1] for d in docs]: continue
        lab=t_.strip()
        if not lab or lab.lower() in ('download','visualizza la brochure'): lab='Brochure' if 'brochure' in (t_.lower()+l.lower()) else os.path.basename(l).rsplit('.',1)[0].replace('_',' ')
        docs.append([lab,l])
    out.append(dict(slug=s,url=u,cat=cat,name=name,dep=(dep.group(1).strip() if dep else f.get('dipartimento','')),area=f.get('area',''),classe=f.get('classe',''),titolo=f.get('titolo',''),durata=f.get('durata',f.get('ore','')),cfu=f.get('cfu',''),image=img.group(1) if img else '',note=note[:400],quota=quota,sections=keep,docs=docs,curricula=curr))
json.dump(out,open(sys.argv[2],'w'),ensure_ascii=False,indent=0)
print(len(out))
from collections import Counter
print(Counter(o['cat'] for o in out))
