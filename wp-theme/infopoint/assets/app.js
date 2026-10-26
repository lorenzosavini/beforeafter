/* Infopoint — tutto il JavaScript del sito. Nessuna dipendenza. */
(function () {
	'use strict';

	var d = document;
	var dl = (window.dataLayer = window.dataLayer || []);
	var store = {
		get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
		set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
	};

	/* Menu mobile */
	var toggle = d.querySelector('[data-nav-toggle]');
	if (toggle) {
		toggle.addEventListener('click', function () {
			var open = toggle.getAttribute('aria-expanded') !== 'true';
			toggle.setAttribute('aria-expanded', open);
			d.getElementById('nav').classList.toggle('open', open);
		});
	}

	/* Menu del telefono: sottomenu chiusi, si aprono con la freccia (uno alla volta). */
	d.addEventListener('click', function (e) {
		var b = e.target.closest('.sub-toggle');
		if (!b) return;
		var li = b.parentNode, open = !li.classList.contains('sub-open');
		li.parentNode.querySelectorAll(':scope > li.sub-open').forEach(function (x) {
			x.classList.remove('sub-open');
			var t = x.querySelector(':scope > .sub-toggle');
			if (t) t.setAttribute('aria-expanded', 'false');
		});
		li.classList.toggle('sub-open', open);
		b.setAttribute('aria-expanded', open ? 'true' : 'false');
	});

	/* Link "Richiedi informazioni": portano al primo modulo della pagina */
	d.addEventListener('click', function (e) {
		var a = e.target.closest('[data-scroll-form]');
		if (!a) return;
		var box = d.getElementById('richiedi');
		if (!box) return;
		e.preventDefault();
		var nav = d.getElementById('nav');
		if (nav) { nav.classList.remove('open'); if (toggle) toggle.setAttribute('aria-expanded', 'false'); }
		var setI = a.getAttribute('data-set-interest');
		var interest = a.getAttribute('data-interest');
		var preset = a.getAttribute('data-msg');
		if (preset) {
			var more2 = box.querySelector('details.more');
			var msg2 = box.querySelector('textarea[name="ip_messaggio"]');
			if (more2) more2.open = true;
			if (msg2) msg2.value = preset;
		} else if (setI) {
			/* Landing: il corso scelto diventa l'interesse del modulo */
			box.querySelectorAll('[data-interest-field]').forEach(function (f) { f.value = setI; });
			box.querySelectorAll('[data-interest-label]').forEach(function (f) { f.textContent = setI; });
		} else if (interest) {
			var more = box.querySelector('details.more');
			var msg = box.querySelector('textarea[name="ip_messaggio"]');
			if (more) more.open = true;
			if (msg && !msg.value) msg.value = 'Vorrei sapere se ho diritto all’agevolazione «' + interest + '».';
		}
		box.scrollIntoView({ block: 'start' });
		var first = box.querySelector('input:not([type=hidden]):not([tabindex="-1"])');
		if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
	});

	/* Filtro elenco corsi */
	d.querySelectorAll('[data-clist]').forEach(function (list) {
		var q = list.querySelector('[data-cf-q]');
		var chips = list.querySelectorAll('[data-cf-t]');
		var rows = list.querySelectorAll('.crow');
		var groups = list.querySelectorAll('.cgroup');
		var empty = list.querySelector('.cempty');
		var tip = '';
		var norm = function (s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };

		function apply() {
			var terms = q ? norm(q.value.trim()).split(/\s+/).filter(Boolean) : [];
			var shown = 0;
			rows.forEach(function (r) {
				var s = r.getAttribute('data-s');
				var ok = (!tip || r.getAttribute('data-tip') === tip) && terms.every(function (t) { return s.indexOf(t) > -1; });
				r.hidden = !ok;
				if (ok) shown++;
			});
			groups.forEach(function (g) { g.hidden = !g.querySelector('.crow:not([hidden])'); });
			if (empty) empty.hidden = shown > 0;
		}
		if (q) q.addEventListener('input', apply);
		chips.forEach(function (c) {
			c.addEventListener('click', function () {
				tip = c.getAttribute('data-cf-t');
				chips.forEach(function (x) { x.setAttribute('aria-pressed', x === c); });
				apply();
			});
		});
	});

	/* Provenienza del contatto: primo accesso e ultimo accesso da una fonte esterna
	   (UTM, gclid, referrer). Resta nel browser e parte solo con un modulo inviato. */
	var attr = (function () {
		var p = new URLSearchParams(location.search);
		var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid', 'fbclid', 'msclkid'];
		// Senza consenso la provenienza resta solo per la visita (sessionStorage);
		// con il consenso alle statistiche o al marketing viene ricordata per 90 giorni.
		var C0 = window.ipConsent || {};
		var keep = !!(C0.ok && (C0.a || C0.m));
		var ses = { get: function (k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } }, set: function (k, v) { try { sessionStorage.setItem(k, v); } catch (e) {} } };
		var read = function (k) { try { return JSON.parse((keep ? store.get(k) : null) || ses.get(k) || 'null'); } catch (e) { return null; } };
		var save = function (k, v) { ses.set(k, v); if (keep) store.set(k, v); };
		if (!keep) { try { localStorage.removeItem('ip_ft'); localStorage.removeItem('ip_lt'); } catch (e) {} }
		var now = Date.now(), t = {}, hit = false;
		keys.forEach(function (k) { var v = p.get(k); if (v) { t[k] = v.slice(0, 200); hit = true; } });
		var ref = d.referrer && d.referrer.indexOf(location.host) === -1 ? d.referrer.split('?')[0].slice(0, 200) : '';
		var ft = read('ip_ft'), lt = read('ip_lt');
		if (ft && now - ft.ts > 90 * 864e5) ft = null;
		if (hit || ref || !lt) {
			t.ref = ref;
			t.land = location.href.split('#')[0].slice(0, 400);
			t.ts = now;
			// Una visita diretta non cancella una campagna recente.
			if (hit || ref || !lt) { lt = t; save('ip_lt', JSON.stringify(t)); }
			if (!ft) { ft = t; save('ip_ft', JSON.stringify(t)); }
		}
		return { ft: ft, lt: lt, persist: function (on) {
			try {
				if (on) { if (ft) localStorage.setItem('ip_ft', JSON.stringify(ft)); if (lt) localStorage.setItem('ip_lt', JSON.stringify(lt)); }
				else { localStorage.removeItem('ip_ft'); localStorage.removeItem('ip_lt'); }
			} catch (e) {}
		} };
	})();

	/* Allegati: nomi dei file scelti e evidenza durante il trascinamento */
	d.querySelectorAll('[data-file-drop]').forEach(function (box) {
		var input = box.querySelector('input[type=file]'), out = box.querySelector('[data-file-names]'), def = out.textContent;
		input.addEventListener('change', function () {
			var names = [].map.call(input.files, function (f) { return f.name; });
			out.textContent = names.length ? names.length + (names.length === 1 ? ' file: ' : ' file: ') + names.join(', ') : def;
			box.classList.toggle('has-files', names.length > 0);
		});
		['dragenter', 'dragover'].forEach(function (t) { box.addEventListener(t, function () { box.classList.add('is-over'); }); });
		['dragleave', 'drop'].forEach(function (t) { box.addEventListener(t, function () { box.classList.remove('is-over'); }); });
	});

	/* Moduli: invio senza ricaricare la pagina, con ripiego sull'invio classico */
	d.querySelectorAll('[data-ip-form]').forEach(function (form) {
		var msg = form.querySelector('.form-msg');
		var btn = form.querySelector('button[type=submit]');
		form.querySelector('[name=ip_page]').value = location.href.split('#')[0];
		form.querySelector('[name=ip_attr]').value = JSON.stringify(attr);

		function show(text) {
			msg.textContent = text;
			msg.hidden = false;
		}

		form.addEventListener('submit', function (e) {
			msg.hidden = true;
			if (!form.checkValidity()) {
				e.preventDefault();
				var bad = form.querySelector(':invalid');
				var label = bad && form.querySelector('label[for="' + bad.id + '"]');
				show(bad && bad.type === 'checkbox' ? 'Per inviare la richiesta serve il consenso privacy.' : 'Controlla il campo «' + (label ? label.firstChild.textContent.trim() : 'evidenziato') + '».');
				if (bad) bad.focus();
				return;
			}
			if (!window.fetch || !window.FormData) return;
			e.preventDefault();
			btn.disabled = true;
			var label = btn.textContent;
			btn.textContent = 'Invio in corso…';
			fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { 'X-IP-Ajax': '1' }, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.ok) {
						var done = false;
						var go = function () {
							if (done) return;
							done = true;
							if (form.hasAttribute('data-inline')) thanks(form);
							else location.href = res.redirect;
						};
						leadConversion(form, res.id, go);
						setTimeout(go, 900);
					} else {
						show(res.message);
						btn.disabled = false;
						btn.textContent = label;
					}
				})
				.catch(function () { form.submit(); });
		});
	});

	/* Conversioni: dataLayer per GTM, GA4 e Google Ads diretti (rispettano il consenso) */
	function phoneE164(v) {
		var n = String(v || '').replace(/[^\d+]/g, '');
		if (!n) return '';
		if (n.indexOf('00') === 0) n = '+' + n.slice(2);
		if (n.charAt(0) !== '+') n = '+39' + n;
		return n;
	}
	function leadConversion(form, id, cb) {
		var T = window.ipTrack || {}, C = window.ipConsent || {};
		var type = form.ip_type.value;
		var email = form.ip_email ? form.ip_email.value.trim().toLowerCase() : '';
		var phone = form.ip_telefono ? phoneE164(form.ip_telefono.value) : '';
		var ev = { event: 'generate_lead', lead_type: type, lead_id: String(id || ''), form_id: form.id || '', page_path: location.pathname };
		if (T.value) { ev.value = T.value; ev.currency = 'EUR'; }
		if (T.enh && C.m) {
			ev.user_data = {};
			if (email) ev.user_data.email = email;
			if (phone) ev.user_data.phone_number = phone;
		}
		dl.push(ev);
		if (typeof window.gtag !== 'function' || (!T.ga4 && !T.ads)) return;
		if (T.ga4) {
			gtag('event', 'generate_lead', { send_to: T.ga4, lead_type: type, value: T.value || undefined, currency: T.value ? 'EUR' : undefined });
		}
		if (T.ads && T.lead) {
			if (T.enh && C.m && (email || phone)) {
				var ud = {};
				if (email) ud.email = email;
				if (phone) ud.phone_number = phone;
				gtag('set', 'user_data', ud);
			}
			gtag('event', 'conversion', { send_to: T.ads + '/' + T.lead, value: T.value || 1, currency: 'EUR', transaction_id: String(id || ''), event_callback: cb });
		}
	}

	/* Ringraziamento sul posto (landing): chi ha scritto resta sulla pagina */
	function thanks(form) {
		var tpl = d.getElementById('ip-thanks');
		var box = form.closest('.lead');
		var name = form.ip_nome ? form.ip_nome.value.trim().split(' ')[0] : '';
		if (!tpl || !box) return;
		box.innerHTML = tpl.innerHTML.replace('{nome}', name ? ', ' + name.replace(/[<>&"]/g, '') : '');
		box.classList.add('lead-done');
		box.scrollIntoView({ block: 'center' });
		store.set('ip_lead_sent', String(Date.now()));
		d.querySelectorAll('dialog[open]').forEach(function (x) { x.close(); });
		dl.push({ event: 'lead_thank_you', lead_type: form.ip_type.value });
	}

	/* Finestre della landing: chi siamo, privacy, cookie restano nella pagina */
	d.addEventListener('click', function (e) {
		var o = e.target.closest('[data-lp-open]');
		var c = e.target.closest('[data-lp-close]');
		var link = e.target.closest('a[href]');
		var dlg = null;
		if (o) dlg = d.getElementById(o.getAttribute('data-lp-open'));
		if (!o && link) {
			d.querySelectorAll('dialog[data-url]').forEach(function (x) { if (x.getAttribute('data-url') === link.href.split('#')[0]) dlg = x; });
		}
		if (dlg && dlg.showModal) { e.preventDefault(); dlg.showModal(); }
		if (c) { e.preventDefault(); c.closest('dialog').close(); }
	});
	d.querySelectorAll('dialog.lp-dialog, dialog.exit-dlg').forEach(function (x) {
		x.addEventListener('click', function (e) { if (e.target === x) x.close(); });
	});

	/* Landing: «Leggi tutta la scheda» apre il testo sul posto */
	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-lp-expand]');
		if (!b) return;
		var t = d.getElementById(b.getAttribute('data-lp-expand'));
		if (t) t.classList.remove('lp-clip');
		b.hidden = true;
	});

	/* Landing: scheda del corso a schede */
	d.querySelectorAll('[data-lp-tabs]').forEach(function (box) {
		var tabs = box.querySelectorAll('[role=tab]');
		var pans = box.querySelectorAll('[role=tabpanel]');
		box.classList.add('js');
		function sel(i, focus) {
			tabs.forEach(function (t, j) { t.setAttribute('aria-selected', i === j); t.tabIndex = i === j ? 0 : -1; });
			pans.forEach(function (p, j) { p.hidden = i !== j; });
			if (focus) tabs[i].focus();
		}
		tabs.forEach(function (t, i) {
			t.addEventListener('click', function () { sel(i); });
			t.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowRight') sel((i + 1) % tabs.length, true);
				if (e.key === 'ArrowLeft') sel((i - 1 + tabs.length) % tabs.length, true);
			});
		});
		if (tabs.length) sel(0);
	});

	/* Landing: mappa delle sedi, una regione alla volta */
	d.querySelectorAll('[data-lp-map]').forEach(function (map) {
		var list = map.parentNode.querySelector('.lp-regs');
		function mark(reg) {
			map.querySelectorAll('[data-region]').forEach(function (el) { el.classList.toggle('sel', el.getAttribute('data-region') === reg); });
		}
		map.addEventListener('click', function (e) {
			var el = e.target.closest('[data-region]');
			if (!el || !list) return;
			var reg = el.getAttribute('data-region');
			list.querySelectorAll('details').forEach(function (x) {
				x.open = x.getAttribute('data-region') === reg;
				if (x.open) x.scrollIntoView({ block: 'nearest' });
			});
			mark(reg);
		});
		if (list) list.addEventListener('toggle', function (e) {
			if (e.target.open) mark(e.target.getAttribute('data-region'));
		}, true);
	});

	/* Landing: un solo invito prima di uscire (solo con il mouse, mai sul tasto Indietro) */
	/* Banner d'uscita: una volta per visita, mai dopo una richiesta inviata.
	   Da computer: il mouse esce dal bordo alto. Da telefono: dopo aver letto, si torna su di scatto. */
	var exitDlg = d.querySelector('dialog[data-lp-exit]');
	if (exitDlg && exitDlg.showModal) {
		var ss = { get: function (k) { try { return sessionStorage.getItem(k); } catch (e) { return '1'; } }, set: function (k) { try { sessionStorage.setItem(k, '1'); } catch (e) {} } };
		// ?prova-banner nell'indirizzo: compare subito al primo segnale, anche se già visto o dopo una richiesta.
		var test = /[?&]prova-banner/.test(location.search);
		var armed = test;
		var leadAge = Date.now() - (parseInt(store.get('ip_lead_sent'), 10) || 0);
		var showExit = function (how) {
			if (!armed || d.querySelector('dialog[open]')) return;
			if (!test && (ss.get('ip_exit') || leadAge < 30 * 864e5)) return;
			var f = d.activeElement;
			if (f && f.closest && f.closest('form') && /INPUT|SELECT|TEXTAREA/.test(f.tagName)) return;
			ss.set('ip_exit'); exitDlg.showModal(); dl.push({ event: 'exit_intent_shown', exit_trigger: how });
		};
		if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
			if (!test) setTimeout(function () { armed = true; }, 8000);
			d.addEventListener('mouseout', function (e) {
				if (!e.relatedTarget && e.clientY <= 0) showExit('mouse');
			});
		} else if (exitDlg.hasAttribute('data-mobile') || d.body.classList.contains('lp')) {
			if (!test) setTimeout(function () { armed = true; }, 20000);
			var lastY = window.scrollY, upY = lastY, upT = Date.now(), maxDepth = 0;
			window.addEventListener('scroll', function () {
				var y = window.scrollY, t = Date.now(), h = d.documentElement.scrollHeight - window.innerHeight;
				if (h > 0) maxDepth = Math.max(maxDepth, y / h);
				if (y >= lastY || t - upT > 700) { upY = y; upT = t; }
				if (y < lastY && maxDepth > 0.4 && upY - y > 350 && t - upT < 700) showExit('scroll_up');
				lastY = y;
			}, { passive: true });
		}
	}

	/* Misurazione dei contatti diretti */
	d.addEventListener('click', function (e) {
		var a = e.target.closest('[data-track]');
		if (a) {
			var method = a.getAttribute('data-track');
			dl.push({ event: 'contact_click', contact_method: method, link_url: a.href });
			var T = window.ipTrack || {};
			if (typeof window.gtag === 'function') {
				if (T.ga4) gtag('event', 'contact_click', { send_to: T.ga4, contact_method: method });
				if (T.ads && T.call) gtag('event', 'conversion', { send_to: T.ads + '/' + T.call });
			}
		}
	});
	if (/[?&]grazie=/.test(location.search)) {
		dl.push({ event: 'lead_thank_you', lead_type: new URLSearchParams(location.search).get('grazie') });
	}

	/* Schede lunghe (corsi, master, pagine informative): ogni sezione «h2»
	   diventa una tendina, aperta solo la prima. Da telefono anche
	   l'introduzione si accorcia, così il modulo arriva subito. Il testo resta
	   tutto nella pagina. */
	var small = window.matchMedia && window.matchMedia('(max-width: 960px)').matches;
	(function () {
		var first = true;
		var widgets = 'form, div.lead, .clist, .agev, .atable-wrap, .contacts, .sedi, .whois, .steps, .smart-box, .smart-docs';
		var fold = function (box) {
			var kids = Array.prototype.slice.call(box.children), cur = null, intro = [];
			kids.forEach(function (el) {
				if (el.matches(widgets) || el.querySelector(widgets)) {
					/* Moduli, elenchi corsi, passi, agevolazioni, contatti: sempre in vista, al loro posto.
					   Se il titolo sopra era già diventato una tendina vuota, torna un titolo normale. */
					if (cur && cur.children.length === 1) {
						var h = d.createElement('h2');
						if (cur.id) { h.id = cur.id; }
						h.innerHTML = cur.firstChild.innerHTML;
						var oi = h.querySelector('.acc-i'); if (oi) oi.remove();
						box.insertBefore(h, cur);
						box.removeChild(cur);
						if (cur.open) { first = true; }
					}
					cur = null;
					intro.stop = true;
				} else if (el.tagName === 'H2') {
					cur = d.createElement('details');
					cur.className = 'm-sec';
					if (first) { cur.open = true; first = false; }
					if (el.id) { cur.id = el.id; }
					var s = d.createElement('summary');
					s.innerHTML = el.innerHTML;
					cur.appendChild(s);
					box.insertBefore(cur, el);
					box.removeChild(el);
				} else if (cur) {
					cur.appendChild(el);
				} else if (!intro.stop && el.matches('p, ul, ol, h3, h4, blockquote')) {
					intro.push(el);
				} else if (!el.classList.contains('ufficiale')) {
					intro.stop = true;
				}
			});
			if (!cur || !intro.length || !small) return;
			var wrap = d.createElement('div');
			wrap.className = 'm-clamp';
			box.insertBefore(wrap, intro[0]);
			intro.forEach(function (el) { wrap.appendChild(el); });
			if (wrap.scrollHeight < 340) { wrap.classList.add('open'); return; }
			var more = d.createElement('button');
			more.type = 'button';
			more.className = 'm-more';
			more.textContent = 'Continua a leggere';
			more.addEventListener('click', function () { wrap.classList.add('open'); more.remove(); });
			box.insertBefore(more, wrap.nextSibling);
		};
		d.querySelectorAll('.with-aside > .prose, .sec > .wrap.prose').forEach(function (pr) {
			pr.querySelectorAll('.ufficiale').forEach(fold);
			fold(pr);
		});
		/* Un link a una sezione chiusa (es. #piani-di-studio) la apre. */
		var openHash = function () {
			var t = location.hash && d.getElementById(location.hash.slice(1));
			if (t && t.tagName === 'DETAILS') { t.open = true; t.scrollIntoView({ block: 'start' }); }
		};
		window.addEventListener('hashchange', openHash);
		openHash();
	})();

	/* Icone a sinistra di ogni tendina del sito, scelte dall'argomento del titolo. */
	var accIcons = d.getElementById('ip-acc-icons');
	if (accIcons) {
		var rules = [
			[/obiettiv|finalit/, 'target'], [/sbocch|lavor|occupaz|profession/, 'briefcase'],
			[/piano|piani|insegnament|curricul|programm/, 'book'], [/document|bando|bandi|regolament|modulist/, 'doc'],
			[/accesso|requisit|ammission|titolo/, 'key'], [/cost|tass|rett|pagament|agevol|bonus|prezz|import|€/, 'euro'],
			[/iscri|immatricol/, 'check'], [/condizion|dettagl/, 'list'], [/esam|sede|sedi/, 'pin'],
			[/durata|tempo|orari/, 'clock'], [/scadenz|calendar|date/, 'calendar'], [/famigl|student/, 'users'],
			[/riconosciment|cfu|credit/, 'percent'], [/laurea|master|corso|corsi/, 'cap']
		];
		var pickIcon = function (s) {
			var t = s.textContent.toLowerCase().trim();
			if (/\?$/.test(t)) return 'question';
			for (var i = 0; i < rules.length; i++) { if (rules[i][0].test(t)) return rules[i][1]; }
			return 'info';
		};
		d.querySelectorAll('details > summary').forEach(function (s) {
			var det = s.parentNode;
			if (det.classList.contains('more') || det.hasAttribute('data-region') || s.querySelector('.acc-i')) return;
			var src = accIcons.content.querySelector('[data-i="' + pickIcon(s) + '"]');
			if (!src) return;
			var ic = d.createElement('span');
			ic.className = 'acc-i';
			ic.setAttribute('aria-hidden', 'true');
			ic.innerHTML = src.innerHTML;
			s.insertBefore(ic, s.firstChild);
			s.classList.add('has-i');
		});
	}

	/* Animazioni leggere: gli elementi entrano piano quando arrivano sullo
	   schermo, i numeri contano fino al valore. Niente se il visitatore ha
	   chiesto di ridurre il movimento o se il browser non le supporta. */
	var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!calm && 'IntersectionObserver' in window) {
		var sel = '.facts p, .cards > *, .review, .steps > li, .sec-head, .band-in, .agev-item, .crows > li, .cgroup-title, details.m-sec, .faq details, .contacts > *, .whois-dl, .whois-cols > *,'
			+ ' .lp-figs p, .lp-sh, .lp-card, .lp-steps > li, .lp-quotes figure, .lp-index a, .lp-rules li, .lp-acc details, .lp-team-call, .lp-office, .lp-map, .lp-std, .lp-tab-foot';
		var items = Array.prototype.slice.call(d.querySelectorAll(sel)).filter(function (el) {
			return !el.closest('.lp-hero, .hero, .phead, dialog, .nav');
		});
		var reveal = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) return;
				en.target.classList.add('in');
				reveal.unobserve(en.target);
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
		items.forEach(function (el) {
			var r = el.getBoundingClientRect();
			if (r.top < window.innerHeight && r.bottom > 0) return; /* già in vista: nessuna animazione */
			var sib = el.parentNode ? Array.prototype.indexOf.call(el.parentNode.children, el) : 0;
			el.style.transitionDelay = Math.min(sib % 4, 3) * 70 + 'ms';
			el.classList.add('rv');
			reveal.observe(el);
		});

		var count = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) return;
				count.unobserve(en.target);
				var el = en.target, txt = el.textContent, m = txt.match(/^(\D*)(\d[\d.]*)(.*)$/);
				if (!m) return;
				var to = parseInt(m[2].replace(/\./g, ''), 10), t0 = null;
				if (!to || to > 100000) return;
				var fmt = function (n) { return m[1] + (to >= 1000 ? n.toLocaleString('it-IT') : n) + m[3]; };
				var step = function (t) {
					if (!t0) t0 = t;
					var k = Math.min(1, (t - t0) / 900), e = 1 - Math.pow(1 - k, 3);
					el.textContent = fmt(Math.round(to * e));
					if (k < 1) requestAnimationFrame(step); else el.textContent = txt;
				};
				requestAnimationFrame(step);
			});
		}, { threshold: 0.6 });
		d.querySelectorAll('.lp-figs strong, .band-stats dd, .facts strong').forEach(function (el) {
			if (/^\D{0,2}\d[\d.]*\D{0,8}$/.test(el.textContent.trim())) count.observe(el);
		});
	}

	/* Banner cookie: necessari sempre, statistiche e marketing solo se scelti */
	var banner = d.querySelector('[data-consent]');
	if (banner && window.ipConsent) {
		var C = window.ipConsent;
		var prefs = banner.querySelector('[data-consent-prefs]');
		var boxA = banner.querySelector('[data-ck="a"]'), boxM = banner.querySelector('[data-ck="m"]');
		var saveBtn = banner.querySelector('[data-consent-set="save"]'), customBtn = banner.querySelector('[data-consent-custom]');
		var open = function (custom) {
			d.querySelectorAll('dialog[open]').forEach(function (x) { x.close(); });
			if (boxA) boxA.checked = !!C.a;
			if (boxM) boxM.checked = !!C.m;
			prefs.hidden = !custom; saveBtn.hidden = !custom; customBtn.hidden = !!custom;
			banner.hidden = false;
		};
		var g = function (x) { return x ? 'granted' : 'denied'; };
		var set = function (choice) {
			var a = choice === 'accept' || (choice === 'save' && !!(boxA && boxA.checked));
			var m = choice === 'accept' || (choice === 'save' && !!(boxM && boxM.checked));
			var id = C.id || (Date.now().toString(36) + Math.random().toString(36).slice(2, 10));
			store.set('ip_consent', JSON.stringify({ v: C.v, t: Date.now(), a: a ? 1 : 0, m: m ? 1 : 0, id: id }));
			C.ok = true; C.a = a; C.m = m; C.id = id;
			gtag('consent', 'update', { ad_storage: g(m), ad_user_data: g(m), ad_personalization: g(m), analytics_storage: g(a) });
			dl.push({ event: 'ip_consent_update', consent_analytics: g(a), consent_marketing: g(m) });
			attr.persist(a || m);
			banner.hidden = true;
			if (window.ipConsentLog) {
				var f = new FormData();
				f.append('action', 'ip_consent'); f.append('choice', choice); f.append('a', a ? 1 : 0); f.append('m', m ? 1 : 0);
				f.append('v', C.v); f.append('cid', id); f.append('url', location.href.split('#')[0]);
				if (navigator.sendBeacon) navigator.sendBeacon(window.ipConsentLog, f);
				else if (window.fetch) fetch(window.ipConsentLog, { method: 'POST', body: f, keepalive: true });
			}
		};
		d.addEventListener('click', function (e) {
			if (e.target.closest('[data-consent-open]')) { e.preventDefault(); open(true); }
		});
		customBtn.addEventListener('click', function () { open(true); });
		banner.addEventListener('click', function (e) {
			var b = e.target.closest('[data-consent-set]');
			if (b) set(b.getAttribute('data-consent-set'));
		});
		if (!C.ok) open(false);
	}
})();
