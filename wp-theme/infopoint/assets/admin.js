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
})();
