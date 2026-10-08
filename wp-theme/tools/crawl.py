import sys,subprocess,os
from concurrent.futures import ThreadPoolExecutor
urls=[u.strip() for u in open(sys.argv[1]) if u.strip()]
out=sys.argv[2]; os.makedirs(out,exist_ok=True)
def f(u):
    n=u.split('://',1)[1].split('/',1)[1].strip('/').replace('/','__') or 'home'
    p=os.path.join(out,n+'.html')
    if os.path.exists(p) and os.path.getsize(p)>2000: return
    subprocess.run(['curl','-sS','-L','-m','60','-A','Mozilla/5.0 (X11; Linux x86_64) Chrome/120',u,'-o',p])
with ThreadPoolExecutor(10) as e: list(e.map(f,urls))
