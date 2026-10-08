import re, html as H
from html.parser import HTMLParser
KEEP={'h2','h3','h4','p','ul','ol','li','table','thead','tbody','tfoot','tr','th','td','strong','b','em','a','br'}
DROP={'script','style','noscript','svg','video','form','iframe','select','textarea','nav','object'}
VOID={'img','source','input','hr','meta','link','wbr'}
class C(HTMLParser):
    def __init__(s):
        super().__init__(convert_charrefs=True); s.o=[]; s.drop=0; s.stack=[]; s.btn=0; s.pdfs=[]; s.links=[]; s.cur_a=None; s.dtag=None
    def handle_starttag(s,t,a):
        d=dict(a)
        if t in VOID: return
        if s.drop:
            if t==s.dtag: s.drop+=1
            return
        cls=d.get('class','') or ''
        if t in DROP or (t=='ul' and ((d.get('id') or '').startswith('menu-') or 'menu-formativa' in cls)) or (t=='div' and 'wh-video' in cls):
            s.drop=1; s.dtag=t; return
        if t=='button' and 'accordion-button' in cls: s.o.append('<h3>'); s.btn=1; return
        if t=='div' and 'card-header' in cls: s.o.append('<h3>'); s.stack.append('h3card'); return
        if t=='div': s.stack.append('div'); return
        if t=='a':
            href=d.get('href','') or ''
            s.cur_a=[href,'']
            if href.lower().split('?')[0].endswith('.pdf'): pass
            s.o.append('<a href="%s">'%H.escape(href)); return
        if t in KEEP:
            s.o.append('<%s>'%('strong' if t=='b' else t))
    def handle_endtag(s,t):
        if s.drop:
            if t==s.dtag: s.drop-=1
            return
        if t=='button' and s.btn: s.o.append('</h3>'); s.btn=0; return
        if t=='div':
            if s.stack:
                x=s.stack.pop()
                if x=='h3card': s.o.append('</h3>')
            return
        if t=='a':
            if s.cur_a:
                href,txt=s.cur_a; txt=txt.strip()
                if href.lower().split('?')[0].endswith('.pdf'): s.pdfs.append((txt,href))
                else: s.links.append((txt,href))
            s.cur_a=None; s.o.append('</a>'); return
        if t in KEEP and t!='br': s.o.append('</%s>'%('strong' if t=='b' else t))
    def handle_data(s,d):
        if s.drop: return
        if s.cur_a is not None: s.cur_a[1]+=d
        s.o.append(H.escape(d))
def clean(fragment):
    c=C(); c.feed(fragment); x=''.join(c.o)
    x=x.replace('\ufeff','').replace('\u200b','')
    x=re.sub(r'\s+',' ',x)
    x=re.sub(r'<h2>\s*<h3>(.*?)</h3>\s*</h2>',r'<h2>\1</h2>',x)
    x=re.sub(r'<a href="https://www\.unimarconi\.it/externalplatformprogram/[^"]*">(.*?)</a>',r'\1',x)
    x=re.sub(r'<a href="(?:javascript|#)[^"]*">(.*?)</a>',r'\1',x)
    for _ in range(3):
        x=re.sub(r'<(p|li|h2|h3|h4|strong|em|td|th|a[^>]*)>\s*</(p|li|h2|h3|h4|strong|em|td|th|a)>','',x)
        x=re.sub(r'<(ul|ol)>\s*</\1>','',x)
    x=re.sub(r'\s*(</?(?:h2|h3|h4|p|ul|ol|li|table|thead|tbody|tr)>)\s*',r'\1',x)
    x=re.sub(r'(<(?:h2|h3|h4|p|ul|ol|table|tr)>)',r'\n\1',x)
    return x.strip(), c.pdfs, c.links
def sections(x):
    x=unwrap_heading_lists(x)
    x=re.sub(r'(<(?:h2|h3|h4|p|ul|ol|table|tr)>)',r'\n\1',x)
    parts=re.split(r'\n?<h2>(.*?)</h2>',x)
    intro=parts[0]; out=[]
    for i in range(1,len(parts),2):
        out.append([re.sub('<[^>]+>','',parts[i]).strip(), parts[i+1].strip()])
    return intro.strip(), out

def unwrap_heading_lists(x):
    """Liste usate come layout (li che contengono h2/h3): toglie ul/li."""
    out=[];pos=0
    pat=re.compile(r'<(ul|ol)>')
    while True:
        m=pat.search(x,pos)
        if not m: out.append(x[pos:]); break
        out.append(x[pos:m.start()]); depth=0
        for mm in re.finditer(r'<(/?)(?:ul|ol)>',x[m.start():]):
            depth+= -1 if mm.group(1) else 1
            if depth==0: end=m.start()+mm.end(); break
        else: end=len(x)
        blk=x[m.start():end]
        if re.search(r'<h[23]>',blk):
            # toglie solo i tag del livello esterno, conserva le liste vere annidate
            res=[];d=0;last=0
            for mm in re.finditer(r'<(/?)(ul|ol|li)>',blk):
                tag=mm.group(2); close=mm.group(1)=='/'
                if tag in('ul','ol'):
                    if not close: d+=1
                    lvl=d
                    if close: d-=1
                else:
                    lvl=d
                if lvl==1:
                    res.append(blk[last:mm.start()]); last=mm.end()
            res.append(blk[last:]); blk=''.join(res)
            blk=unwrap_heading_lists(blk)
        out.append(blk); pos=end
    return ''.join(out)
