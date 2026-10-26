/* Infopoint — campi elenco (aggiungi, ordina, elimina) e selettore media. */
(function () {
	'use strict';
	function renumber(rep) {
		var name = rep.getAttribute('data-name');
		if (rep.classList.contains('ip-check')) return;
		rep.querySelectorAll('.ip-rep-list > .ip-rep-row').forEach(function (row, i) {
			row.querySelectorAll('[name]').forEach(function (el) {
				var rest = el.name.slice(name.length).replace(/^\[[^\]]*\]/, '[' + i + ']');
				el.name = name + rest;
			});
		});
	}
	document.addEventListener('click', function (e) {
		var t = e.target;
		var rep = t.closest('.ip-rep');
		if (rep && t.matches('[data-ip-add]')) {
			var tpl = rep.querySelector('template');
			var list = rep.querySelector('.ip-rep-list');
			list.appendChild(tpl.content.firstElementChild.cloneNode(true));
			renumber(rep);
			var first = list.lastElementChild.querySelector('input,textarea');
			if (first) first.focus();
		}
		var row = t.closest('.ip-rep-row');
		if (row && rep) {
			if (t.matches('[data-ip-del]')) { row.remove(); renumber(rep); }
			if (t.matches('[data-ip-up]') && row.previousElementSibling) { row.parentNode.insertBefore(row, row.previousElementSibling); renumber(rep); }
			if (t.matches('[data-ip-down]') && row.nextElementSibling) { row.parentNode.insertBefore(row.nextElementSibling, row); renumber(rep); }
		}
		if (t.matches('[data-ip-media]') && window.wp && wp.media) {
			e.preventDefault();
			var input = t.parentNode.querySelector('input');
			var frame = wp.media({ title: 'Scegli un file', button: { text: 'Usa questo file' }, multiple: false });
			frame.on('select', function () {
				var a = frame.state().get('selection').first().toJSON();
				input.value = a.url;
				var label = t.closest('.ip-rep-row') && t.closest('.ip-rep-row').querySelector('input[name$="[label]"]');
				if (label && !label.value) label.value = a.title;
			});
			frame.open();
		}
		if (t.matches('[data-ip-image]') && window.wp && wp.media) {
			e.preventDefault();
			var box = t.closest('.ip-image');
			var f = wp.media({ title: 'Scegli un’immagine', library: { type: 'image' }, button: { text: 'Usa questa immagine' }, multiple: false });
			f.on('select', function () {
				var a = f.state().get('selection').first().toJSON();
				box.querySelector('input').value = a.id;
				var img = box.querySelector('img');
				img.src = (a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url); img.hidden = false;
				box.querySelector('[data-ip-image-clear]').hidden = false;
			});
			f.open();
		}
		if (t.matches('[data-ip-image-clear]')) {
			var bx = t.closest('.ip-image');
			bx.querySelector('input').value = ''; bx.querySelector('img').hidden = true; t.hidden = true;
		}
		if (t.matches('[data-ip-palette]')) {
			var pal = t.getAttribute('data-ip-palette') === 'agency'
				? { brand: '#1f3b57', brand_dark: '#152b41', brand_deep: '#0f1f2f', accent: '#d9622b', accent_dark: '#b84f1f', soft: '#f3f4f6', dark: '#1f2933' }
				: { brand: '#225e48', brand_dark: '#174f3a', brand_deep: '#0e261d', accent: '#a0300e', accent_dark: '#ca451d', soft: '#f0f0f0', dark: '#373737' };
			Object.keys(pal).forEach(function (k) {
				var el = document.getElementById('ip-color_' + k);
				if (el) { el.value = pal[k]; var c = el.parentNode.querySelector('code'); if (c) c.textContent = pal[k]; }
			});
		}
		if (t.matches('[data-ip-reset-color]')) {
			var c = t.parentNode.querySelector('input[type=color]');
			c.value = t.getAttribute('data-ip-reset-color');
			t.parentNode.querySelector('code').textContent = c.value;
		}
	});
	document.addEventListener('input', function (e) {
		if (e.target.matches('input[type=color]')) {
			var code = e.target.parentNode.querySelector('code');
			if (code) code.textContent = e.target.value;
		}
	});

	/* Impostazioni: campi che compaiono solo quando servono */
	function syncIf() {
		document.querySelectorAll('[data-ipa-if]').forEach(function (row) {
			var el = document.querySelector('[name="ip_settings[' + row.getAttribute('data-ipa-if') + ']"]');
			if (!el) return;
			row.hidden = el.type === 'checkbox' ? !el.checked : !el.value.trim();
		});
	}
	syncIf();
	document.addEventListener('change', syncIf);
	document.addEventListener('input', function (e) { if (e.target.closest('.ipa-form')) syncIf(); });

	/* Avviso se si lascia la pagina con modifiche non salvate */
	var form = document.querySelector('.ipa-form');
	if (form) {
		var dirty = false, label = form.querySelector('[data-ipa-dirty]');
		var mark = function () { if (!dirty) { dirty = true; if (label) label.hidden = false; } };
		form.addEventListener('input', mark);
		form.addEventListener('change', mark);
		form.addEventListener('click', function (e) { if (e.target.closest('[data-ip-add],[data-ip-del],[data-ip-up],[data-ip-down],[data-ip-image],[data-ip-palette]')) mark(); });
		form.addEventListener('submit', function () { dirty = false; });
		window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
	}

	/* Richieste: stato modificabile dall'elenco */
	var A = window.ipAdmin || {};
	document.querySelectorAll('select.ipa-state').forEach(function (sel) {
		sel.setAttribute('data-state', sel.value);
		sel.addEventListener('change', function () {
			var f = new FormData();
			f.append('action', 'ip_lead_state'); f.append('_ajax_nonce', A.nonce); f.append('lead', sel.getAttribute('data-lead')); f.append('state', sel.value);
			sel.disabled = true;
			fetch(A.ajax, { method: 'POST', body: f, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
				sel.disabled = false;
				if (res.success) {
					sel.setAttribute('data-state', sel.value);
					sel.classList.add('is-saved');
					setTimeout(function () { sel.classList.remove('is-saved'); }, 1200);
				} else { alert('Non sono riuscito a salvare lo stato. Ricarica la pagina e riprova.'); }
			}).catch(function () { sel.disabled = false; });
		});
	});

	/* Guide brevi: chiuse una volta, non tornano */
	document.addEventListener('click', function (e) {
		var x = e.target.closest('.ipa-tip .notice-dismiss');
		if (!x || !A.ajax) return;
		var f = new FormData();
		f.append('action', 'ip_tip_hide'); f.append('_ajax_nonce', A.nonce); f.append('tip', x.closest('.ipa-tip').getAttribute('data-ipa-tip'));
		fetch(A.ajax, { method: 'POST', body: f, credentials: 'same-origin' });
	});

	/* Copia negli appunti (indirizzi delle landing) */
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-ipa-copy]');
		if (!b) return;
		var t = b.getAttribute('data-ipa-copy'), old = b.textContent;
		var done = function () { b.textContent = 'Copiato ✓'; setTimeout(function () { b.textContent = old; }, 1500); };
		if (navigator.clipboard) navigator.clipboard.writeText(t).then(done, function () { prompt('Copia l’indirizzo:', t); });
		else prompt('Copia l’indirizzo:', t);
	});
})();
