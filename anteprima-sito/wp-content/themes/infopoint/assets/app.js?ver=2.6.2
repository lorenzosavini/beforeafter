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
		if (setI) {
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

	/* Provenienza del contatto (primo accesso): UTM, gclid, fbclid, referrer */
	(function () {
		var p = new URLSearchParams(location.search);
		var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
		var found = keys.filter(function (k) { return p.get(k); }).map(function (k) { return k + '=' + p.get(k); });
		if (found.length) {
			store.set('ip_attr', found.join(' · '));
		} else if (!store.get('ip_attr') && d.referrer && d.referrer.indexOf(location.host) === -1) {
			store.set('ip_attr', 'referrer=' + d.referrer.split('?')[0]);
		}
	})();

	/* Moduli: invio senza ricaricare la pagina, con ripiego sull'invio classico */
	d.querySelectorAll('[data-ip-form]').forEach(function (form) {
		var msg = form.querySelector('.form-msg');
		var btn = form.querySelector('button[type=submit]');
		form.querySelector('[name=ip_page]').value = location.href.split('#')[0];
		form.querySelector('[name=ip_attr]').value = store.get('ip_attr') || '';

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
						dl.push({ event: 'generate_lead', lead_type: form.ip_type.value });
						if (form.hasAttribute('data-inline')) {
							thanks(form);
						} else {
							location.href = res.redirect;
						}
					} else {
						show(res.message);
						btn.disabled = false;
						btn.textContent = label;
					}
				})
				.catch(function () { form.submit(); });
		});
	});

	/* Ringraziamento sul posto (landing): chi ha scritto resta sulla pagina */
	function thanks(form) {
		var tpl = d.getElementById('ip-thanks');
		var box = form.closest('.lead');
		var name = form.ip_nome ? form.ip_nome.value.trim().split(' ')[0] : '';
		if (!tpl || !box) return;
		box.innerHTML = tpl.innerHTML.replace('{nome}', name ? ', ' + name.replace(/[<>&"]/g, '') : '');
		box.classList.add('lead-done');
		box.scrollIntoView({ block: 'center' });
		store.set('ip_lead_sent', '1');
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
		if (!o && link && d.body.classList.contains('lp')) {
			d.querySelectorAll('dialog[data-url]').forEach(function (x) { if (x.getAttribute('data-url') === link.href.split('#')[0]) dlg = x; });
		}
		if (dlg && dlg.showModal) { e.preventDefault(); dlg.showModal(); }
		if (c) { e.preventDefault(); c.closest('dialog').close(); }
	});
	d.querySelectorAll('dialog.lp-dialog').forEach(function (x) {
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
	var exitDlg = d.querySelector('dialog[data-lp-exit]');
	if (exitDlg && exitDlg.showModal && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		var ss = { get: function (k) { try { return sessionStorage.getItem(k); } catch (e) { return '1'; } }, set: function (k) { try { sessionStorage.setItem(k, '1'); } catch (e) {} } };
		var armed = false;
		setTimeout(function () { armed = true; }, 8000);
		d.addEventListener('mouseout', function (e) {
			if (!armed || e.relatedTarget || e.clientY > 0 || ss.get('ip_exit') || store.get('ip_lead_sent') || d.querySelector('dialog[open]')) return;
			ss.set('ip_exit');
			exitDlg.showModal();
			dl.push({ event: 'exit_intent_shown' });
		});
	}

	/* Misurazione dei contatti diretti */
	d.addEventListener('click', function (e) {
		var a = e.target.closest('[data-track]');
		if (a) dl.push({ event: 'contact_click', contact_method: a.getAttribute('data-track'), link_url: a.href });
	});
	if (/[?&]grazie=/.test(location.search)) {
		dl.push({ event: 'lead_thank_you', lead_type: new URLSearchParams(location.search).get('grazie') });
	}

	/* Banner cookie */
	var banner = d.querySelector('[data-consent]');
	if (banner) {
		if (!store.get('ip_consent')) banner.hidden = false;
		banner.addEventListener('click', function (e) {
			var b = e.target.closest('[data-consent-set]');
			if (!b) return;
			var v = b.getAttribute('data-consent-set');
			store.set('ip_consent', v);
			if (typeof window.gtag === 'function') {
				window.gtag('consent', 'update', { ad_storage: v, ad_user_data: v, ad_personalization: v, analytics_storage: v });
			}
			banner.hidden = true;
		});
	}
})();
