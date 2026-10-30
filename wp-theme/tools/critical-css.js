/**
 * CSS critico del tema Infopoint.
 *
 * Apre le pagine di un sito di prova (un indirizzo per riga in URLS) a tre
 * larghezze e tiene, di style.css e assets/landing.css, solo le regole che
 * servono a ciò che si vede all'apertura (o agli elementi fissi: testata,
 * barra in basso, banner cookie), più font, animazioni e regole di base.
 * Un file per tipo di pagina, assets/critical/<tipo>.css: il tema lo mette
 * nella pagina e carica il foglio di stile completo subito dopo.
 *
 * URLS: una riga per pagina, «tipo indirizzo» (tipi: home, corso, pagina,
 * elenco, landing; più pagine per tipo = regole unite).
 * Uso: URLS=elenco.txt THEME=/percorso/del/tema PW=/percorso/playwright node critical-css.js
 * Da rifare quando cambia il CSS del tema.
 */
const { chromium } = require(process.env.PW || 'playwright');
const fs = require('fs');
const path = require('path');

const urls = fs.readFileSync(process.env.URLS, 'utf8').split('\n').map(s => s.trim()).filter(Boolean).map(l => l.split(/\s+/));
const theme = process.env.THEME;
const sizes = [[375, 812], [820, 1180], [1366, 900]];

// Raccoglie, nella pagina aperta, le regole usate «sopra la piega» di un foglio.
function collect(sheetId) {
	const sheet = document.getElementById(sheetId) && document.getElementById(sheetId).sheet;
	if (!sheet) return null;
	const fold = window.innerHeight + 200;
	const dyn = /::?(hover|focus|focus-visible|focus-within|active|visited|before|after|placeholder|marker|selection|first-line|first-letter|-webkit-[a-z-]+|-moz-[a-z-]+)(\([^)]*\))?/g;
	// «Sopra la piega»: anche ciò che è spostato fuori schermo (es. «Vai al
	// contenuto») e ciò che il CSS nasconde (sottomenu, finestre), giudicato dal
	// primo contenitore visibile: le regole che li nascondono servono subito.
	const visible = el => {
		for (let x = el; x && x.nodeType === 1; x = x.parentElement) {
			const p = getComputedStyle(x).position;
			if (p === 'fixed' || p === 'sticky') return true;
		}
		let x = el, r = x.getBoundingClientRect();
		while (x.parentElement && !r.width && !r.height) { x = x.parentElement; r = x.getBoundingClientRect(); }
		return r.top < fold;
	};
	const split = sel => { // virgole di primo livello (non dentro :is(), :not()…)
		const out = []; let depth = 0, cur = '';
		for (const ch of sel) {
			if (ch === '(') depth++;
			if (ch === ')') depth--;
			if (ch === ',' && !depth) { out.push(cur); cur = ''; } else cur += ch;
		}
		out.push(cur);
		return out.map(s => s.trim()).filter(Boolean);
	};
	const used = sel => split(sel).some(part => {
		const base = part.replace(dyn, '').trim() || '*';
		if (/^(html|body|:root|\*)/.test(base)) return true;
		let els;
		try { els = document.querySelectorAll(base); } catch (e) { return true; } // nel dubbio si tiene
		for (let i = 0; i < els.length && i < 400; i++) if (visible(els[i])) return true;
		return false;
	});
	const keep = [];
	const walk = (rules, prefix) => {
		Array.from(rules).forEach((r, i) => {
			const key = prefix + i;
			if (r.type === CSSRule.STYLE_RULE) {
				if (used(r.selectorText)) keep.push(key);
			} else if (r.type === CSSRule.MEDIA_RULE) {
				if (matchMedia(r.conditionText || r.media.mediaText).matches) walk(r.cssRules, key + '.');
			} else if (r.type === CSSRule.SUPPORTS_RULE) {
				walk(r.cssRules, key + '.');
			} else {
				keep.push(key); // @font-face, @keyframes, @import…
			}
		});
	};
	walk(sheet.cssRules, '');
	return keep;
}

// Ricostruisce il testo CSS delle sole regole tenute, con i loro @media.
function rebuild({ sheetId, keys }) {
	const sheet = document.getElementById(sheetId).sheet;
	const set = new Set(keys);
	const out = (rules, prefix) => Array.from(rules).map((r, i) => {
		const key = prefix + i;
		if (r.type === CSSRule.MEDIA_RULE || r.type === CSSRule.SUPPORTS_RULE) {
			const inner = out(r.cssRules, key + '.');
			if (!inner) return '';
			const head = r.type === CSSRule.MEDIA_RULE ? '@media ' + (r.conditionText || r.media.mediaText) : '@supports ' + r.conditionText;
			return head + '{' + inner + '}';
		}
		return set.has(key) ? r.cssText : '';
	}).join('');
	return out(sheet.cssRules, '');
}

(async () => {
	const b = await chromium.launch();
	const found = {}; // tipo -> foglio -> regole
	const sample = {}; // tipo -> indirizzo
	for (const [w, h] of sizes) {
		const ctx = await b.newContext({ viewport: { width: w, height: h } });
		await ctx.route(/googletagmanager\.com|google-analytics/, r => r.abort());
		const p = await ctx.newPage();
		for (const [type, u] of urls) {
			await p.goto(u + (u.includes('?') ? '&' : '?') + 'ip_full_css=1', { waitUntil: 'load' });
			await p.waitForTimeout(250);
			found[type] = found[type] || { 'ip-css': new Set(), 'ip-lp-css': new Set() };
			sample[type] = sample[type] || u;
			for (const id of ['ip-css', 'ip-lp-css']) {
				const keys = await p.evaluate(collect, id);
				if (keys) keys.forEach(k => found[type][id].add(k));
			}
		}
		await ctx.close();
	}
	const ctx = await b.newContext({ viewport: { width: 1366, height: 900 } });
	await ctx.route(/googletagmanager\.com|google-analytics/, r => r.abort());
	const p = await ctx.newPage();
	fs.mkdirSync(path.join(theme, 'assets/critical'), { recursive: true });
	for (const type of Object.keys(found)) {
		await p.goto(sample[type] + (sample[type].includes('?') ? '&' : '?') + 'ip_full_css=1', { waitUntil: 'load' });
		let css = '';
		for (const id of ['ip-css', 'ip-lp-css']) {
			if (found[type][id].size) css += await p.evaluate(rebuild, { sheetId: id, keys: Array.from(found[type][id]) });
		}
		// Indirizzi dei font di nuovo relativi al tema (il tema li completa).
		css = css.replace(/url\("?[^")]*\/themes\/[^/]+\/(assets\/[^")]+)"?\)/g, 'url("$1")');
		const file = path.join(theme, 'assets/critical', type + '.css');
		fs.writeFileSync(file, '/* Generato da tools/critical-css.js: non modificare a mano. */\n' + css + '\n');
		console.log('assets/critical/' + type + '.css', Math.round(css.length / 1024) + ' KB');
	}
	await b.close();
})();
