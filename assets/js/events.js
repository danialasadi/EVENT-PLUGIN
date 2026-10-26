/**
 * Shaparak Events — front-end behaviour.
 * - Shamsi countdown ticking (values are rendered server-side in Persian digits)
 * - Toolbar niceties (search clear button, calendar/list toggle state)
 * - Filter panel: hides/shows the event cards of every widget on the page
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

	/**
	 * Filter panel: hides/shows every event card on the page (any widget).
	 */
	function filters() {
		var panels = document.querySelectorAll('[data-shaparak-filters]');
		if (!panels.length) return;

		function panelState(panel) {
			var select = panel.querySelector('[data-filter-province]');
			var boxes = panel.querySelectorAll('[data-filter-category]');
			var checked = [];

			Array.prototype.forEach.call(boxes, function (box) {
				if (box.checked) checked.push(box.value);
			});

			return {
				province: select ? select.value : '',
				checked: checked,
				hasCategories: boxes.length > 0
			};
		}

		function apply() {
			var states = [];
			Array.prototype.forEach.call(panels, function (panel) {
				states.push(panelState(panel));
			});

			Array.prototype.forEach.call(document.querySelectorAll('[data-shaparak-card]'), function (card) {
				var province = card.getAttribute('data-province') || '';
				var categories = (card.getAttribute('data-categories') || '').split(/\s+/).filter(Boolean);
				var visible = true;

				states.forEach(function (state) {
					if (!visible) return;

					if (state.province && province !== state.province) {
						visible = false;
						return;
					}

					if (!state.hasCategories) return;

					// Cards without a category cannot be filtered out.
					var match = categories.length === 0;
					categories.forEach(function (slug) {
						if (state.checked.indexOf(slug) !== -1) match = true;
					});
					if (!match) visible = false;
				});

				card.classList.toggle('is-filtered-out', !visible);
			});
		}

		Array.prototype.forEach.call(panels, function (panel) {
			panel.addEventListener('change', apply);
		});

		apply();
	}

	document.addEventListener('DOMContentLoaded', function () {
		tick();
		setInterval(tick, 1000);
		toolbar();
		filters();
	});
})();
