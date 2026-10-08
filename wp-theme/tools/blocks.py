import re, html as H
def _li(items):
    return ''.join('<!-- wp:list-item -->\n<li>%s</li>\n<!-- /wp:list-item -->\n'%i for i in items)
def to_blocks(x):
    """Converte HTML pulito (p, h2-4, ul/ol, table) in blocchi Gutenberg."""
    x=re.sub(r'<(/?)h4>',r'<\1h3>',x) if False else x
    toks=re.findall(r'<(p|h2|h3|h4|ul|ol|table)>(.*?)</\1>|([^<\n][^\n]*?)(?=\n|$)',x,re.S)
    out=[]
    # parsing a livello alto con stack semplice per liste annidate
    pos=0; res=[]
    pat=re.compile(r'<(p|h2|h3|h4|ul|ol|table)>',re.S)
    while pos<len(x):
        m=pat.search(x,pos)
        if not m:
            rest=re.sub('<[^>]+>','',x[pos:]).strip()
            if rest: res.append(('p',H.escape(H.unescape(rest)) if '<' not in x[pos:] else x[pos:].strip()))
            break
        pre=x[pos:m.start()].strip()
        if pre and re.sub('<[^>]+>','',pre).strip(): res.append(('p',pre))
        tag=m.group(1); depth=0; i=m.start()
        tp=re.compile(r'<(/?)%s>'%tag)
        for mm in tp.finditer(x,m.start()):
            depth+= -1 if mm.group(1) else 1
            if depth==0: end=mm.end(); break
        else: end=len(x)
        inner=x[m.end():end-len('</%s>'%tag)]
        res.append((tag,inner.strip())); pos=end
    for tag,inner in res:
        if not re.sub('<[^>]+>','',inner).strip(): continue
        if tag=='p':
            inner=re.sub(r'</?(?:p|h[2-4]|ul|ol|li|table|tr|td|th|thead|tbody)>',' ',inner).strip()
            out.append('<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->'%inner)
        elif tag in('h2','h3','h4'):
            lv=int(tag[1]); inner=re.sub('<(?!/?(strong|em)>)[^>]+>','',inner).strip()
            out.append('<!-- wp:heading%s -->\n<%s class="wp-block-heading">%s</%s>\n<!-- /wp:heading -->'%('' if lv==2 else ' {"level":%d}'%lv,tag,inner,tag))
        elif tag in('ul','ol'):
            if re.search(r'<(ul|ol|table|h\d|p)>',inner):
                out.append('<!-- wp:html -->\n<%s>%s</%s>\n<!-- /wp:html -->'%(tag,inner,tag)); continue
            items=re.findall(r'<li>(.*?)</li>',inner,re.S)
            o='{"ordered":true} ' if tag=='ol' else ''
            out.append('<!-- wp:list %s-->\n<%s class="wp-block-list">%s</%s>\n<!-- /wp:list -->'%(o,tag,_li([i.strip() for i in items]),tag))
        elif tag=='table':
            inner=re.sub(r'\n','',inner)
            out.append('<!-- wp:table -->\n<figure class="wp-block-table"><table class="has-fixed-layout">%s</table></figure>\n<!-- /wp:table -->'%inner)
    return '\n\n'.join(out)
