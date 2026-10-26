/**
 * Shaparak Events — front-end behaviour.
 * - Shamsi countdown ticking (values are rendered server-side in Persian digits)
 * - Toolbar niceties (search clear button, calendar/list toggle state)
 */
(function () {
	'use strict';

	var FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

	function toFa(value) {
		return String(value).replace(/[0-9]/g, function (d) {
			return FA[parseInt(d, 10)];
		});
	}

	function pad(value) {
		return value < 10 ? '0' + value : String(value);
	}

	function tick() {
		var nodes = document.querySelectorAll('[data-shaparak-countdown]');
		if (!nodes.length) return;

		Array.prototype.forEach.call(nodes, function (node) {
			var target = parseInt(node.getAttribute('data-target'), 10) || 0;
			var diff = Math.max(0, target - Math.floor(Date.now() / 1000));

			var days = Math.floor(diff / 86400);
			var hours = Math.floor((diff % 86400) / 3600);
			var mins = Math.floor((diff % 3600) / 60);
			var secs = Math.floor((diff % 60) / 60);

			var units = node.querySelectorAll('.shaparak-countdown__unit strong');
			if (units.length >= 3) {
				units[0].textContent = toFa(days);
				units[1].textContent = toFa(pad(hours));
				units[2].textContent = toFa(pad(mins));
			}

			var secondsNode = node.querySelector('[data-countdown-seconds]');
			if (secondsNode) {
				secondsNode.textContent = toFa(pad(secs));
			}

			if (diff === 0 && node.classList.contains('shaparak-countdown--pending')) {
				node.classList.add('is-done');
			}
		});
	}

	function toolbar() {
		var forms = document.querySelectorAll('.shaparak-toolbar__search');
		Array.prototype.forEach.call(forms, function (form) {
			var input = form.querySelector('input[type="search"]');
			if (!input) return;
			form.addEventListener('submit', function () {
				var value = input.value.trim();
				if (!value) {
					// Empty search returns to the plain list view.
					form.classList.add('is-empty');
				}
			});
		});
	}

	function filters() {
		var panels = document.querySelectorAll('[data-se-filters]');
		if (!panels.length) return;

		function apply() {
			var types = [];
			var province = '';
			Array.prototype.forEach.call(panels, function (panel) {
				Array.prototype.forEach.call(panel.querySelectorAll('[data-se-filter-type]'), function (cb) {
					var v = cb.getAttribute('data-se-filter-type');
					if (cb.checked && v) types.push(v);
				});
				var sel = panel.querySelector('[data-se-filter-province]');
				if (sel && sel.value) province = sel.value;
			});

			var typeActive = types.length > 0;
			Array.prototype.forEach.call(document.querySelectorAll('[data-se-type]'), function (card) {
				var show = true;
				var ctype = card.getAttribute('data-se-type') || '';
				if (typeActive && types.indexOf(ctype) === -1) show = false;
				if (province && (card.getAttribute('data-se-city') || '') !== province) show = false;
				card.classList.toggle('is-filtered', !show);
			});
		}

		document.addEventListener('change', function (e) {
			if (e.target && e.target.closest && e.target.closest('[data-se-filters]')) apply();
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		tick();
		setInterval(tick, 1000);
		toolbar();
		filters();
	});
})();
