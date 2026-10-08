import json, math, sys
d = json.load(open(sys.argv[1]))
LON0, LAT0, LAT_REF = 6.6, 47.1, 42.0
K = 50.0  # unità SVG per grado
cx = math.cos(math.radians(LAT_REF))
def proj(lon, lat):
    return ((lon - LON0) * cx * K, (LAT0 - lat) * K)
def dp(pts, eps):
    if len(pts) < 3: return pts
    a, b = pts[0], pts[-1]
    dmax, idx = 0, 0
    for i in range(1, len(pts) - 1):
        p = pts[i]
        if a == b: dist = math.hypot(p[0]-a[0], p[1]-a[1])
        else:
            dist = abs((b[0]-a[0])*(a[1]-p[1]) - (a[0]-p[0])*(b[1]-a[1])) / math.hypot(b[0]-a[0], b[1]-a[1])
        if dist > dmax: dmax, idx = dist, i
    if dmax > eps:
        return dp(pts[:idx+1], eps)[:-1] + dp(pts[idx:], eps)
    return [a, b]
def area(r):
    return abs(sum(r[i][0]*r[i-1][1] - r[i-1][0]*r[i][1] for i in range(len(r)))) / 2
names = {"Valle d'Aosta/Vallée d'Aoste": "Valle d'Aosta", 'Trentino-Alto Adige/Südtirol': 'Trentino-Alto Adige'}
out = {}
centers = {}
for f in d['features']:
    n = f['properties']['reg_name']; n = names.get(n, n)
    polys = f['geometry']['coordinates']
    if f['geometry']['type'] == 'Polygon': polys = [polys]
    parts = []
    best = None
    for poly in polys:
        ring = [proj(*c[:2]) for c in poly[0]]
        if area(ring) < 0.6: continue  # isolotti
        if best is None or area(ring) > area(best): best = ring
        s = dp(ring, 0.7)
        if len(s) < 4: continue
        parts.append('M' + 'L'.join('%.1f,%.1f' % p for p in s) + 'Z')
    out[n] = ''.join(parts)
    xs=[p[0] for p in best]; ys=[p[1] for p in best]
    centers[n] = [round(sum(xs)/len(xs),1), round(sum(ys)/len(ys),1)]
W = (18.6 - LON0) * cx * K; H = (LAT0 - 35.45) * K
json.dump({'lon0': LON0, 'lat0': LAT0, 'cx': round(cx, 6), 'k': K, 'w': round(W, 1), 'h': round(H, 1), 'regions': out, 'centers': centers}, open(sys.argv[2], 'w'), ensure_ascii=False, separators=(',', ':'))
print(round(W), round(H), sum(len(v) for v in out.values()), 'byte di tracciati')
