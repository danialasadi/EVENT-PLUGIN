/**
 * Shaparak Events — front-end behaviour.
 * - Shamsi countdown ticking (values are rendered server-side in Persian digits)
 * - Toolbar niceties (search clear button, calendar/list toggle state)
 * - Filter panel: hides/shows the event cards of every widget on the page
 * - Report slider: endless loop (cloned edge cards) + timed rotation
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

	/**
	 * Report slider: endless scrolling + timed rotation.
	 *
	 * The first/last cards are cloned to both edges, so scrolling past either
	 * end jumps by one full round of real cards and looks continuous. Every
	 * data-shaparak-autoplay seconds the row advances one card (0 = no timer).
	 * All positions are measured from the content start edge, which keeps the
	 * math identical in RTL and LTR.
	 */
	function reportSliders() {
		var tracks = document.querySelectorAll('[data-shaparak-autoplay]:not([data-shaparak-slider])');
		Array.prototype.forEach.call(tracks, initReportSlider);
	}

	function initReportSlider(track) {
		if (track.getAttribute('data-shaparak-slider')) return;

		var rtl = 'rtl' === getComputedStyle(track).direction;
		var delay = parseInt(track.getAttribute('data-shaparak-autoplay'), 10) || 0;
		var timer = null;
		var resizeTimer = null;
		var step = 0;   // one card + gap
		var origin = 0; // where the real cards start
		var edge = 0;   // width of one full round of real cards
		var real = [];

		function progress() {
			return rtl ? 0 - track.scrollLeft : track.scrollLeft;
		}

		function setProgress(value) {
			track.scrollLeft = rtl ? 0 - value : value;
		}

		// Distance from the content start edge (independent of scroll position).
		function startOffset(node) {
			var rect = node.getBoundingClientRect();
			var trackRect = track.getBoundingClientRect();
			return rtl
				? trackRect.right - rect.right - track.scrollLeft
				: rect.left - trackRect.left + track.scrollLeft;
		}

		function realCards() {
			var nodes = [];
			Array.prototype.forEach.call(track.children, function (node) {
				if (!node.hasAttribute('data-shaparak-clone')) nodes.push(node);
			});
			return nodes;
		}

		real = realCards();
		if (real.length < 2) return; // one card: nothing to loop

		step = Math.abs(startOffset(real[1]) - startOffset(real[0]));
		if (step <= 0) return;
		if (!track.clientWidth) return; // not laid out yet — retried on the next scan

		// Head: a copy of the last card, shown when scrolling backwards.
		var head = real[real.length - 1].cloneNode(true);
		head.setAttribute('data-shaparak-clone', '1');
		track.insertBefore(head, track.firstChild);

		// Tails: copies of the first cards, together wider than the viewport,
		// so the forward jump always has an identical view to land on.
		var count = Math.max(1, Math.ceil(track.clientWidth / step) + 1);
		for (var i = 0; i < count; i++) {
			var tail = real[i % real.length].cloneNode(true);
			tail.setAttribute('data-shaparak-clone', '1');
			track.appendChild(tail);
		}
		track.setAttribute('data-shaparak-cloned', '1');

		origin = startOffset(real[0]);
		var firstTail = real[real.length - 1].nextElementSibling;
		edge = firstTail ? Math.abs(startOffset(firstTail) - origin) : 0;
		setProgress(origin); // start at the first real card

		function wrap() {
			if (!edge) return;
			var p = progress();
			if (p < origin) {
				setProgress(p + edge);
			} else if (p >= origin + edge) {
				setProgress(p - edge);
			}
		}

		function advance() {
			if (!track.isConnected) {
				cleanup();
				return;
			}
			var delta = rtl ? 0 - step : step;
			if (track.scrollBy) {
				track.scrollBy({ left: delta, behavior: 'smooth' });
			} else {
				setProgress(progress() + step);
			}
		}

		function start() {
			if (delay > 0 && !timer) timer = setInterval(advance, delay * 1000);
		}

		function stop() {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		}

		function onScroll() {
			wrap();
		}

		function onEnter() {
			stop();
		}

		function onLeave() {
			start();
		}

		function onResize() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				cleanup();
				initReportSlider(track);
			}, 250);
		}

		function cleanup() {
			stop();
			clearTimeout(resizeTimer);
			track.removeEventListener('scroll', onScroll);
			track.removeEventListener('mouseenter', onEnter);
			track.removeEventListener('mouseleave', onLeave);
			track.removeEventListener('touchstart', onEnter);
			track.removeEventListener('touchend', onLeave);
			window.removeEventListener('resize', onResize);
			Array.prototype.slice.call(track.querySelectorAll('[data-shaparak-clone]')).forEach(function (node) {
				if (node.parentNode) node.parentNode.removeChild(node);
			});
			track.removeAttribute('data-shaparak-cloned');
			track.removeAttribute('data-shaparak-slider');
			track.scrollLeft = 0;
		}

		track.setAttribute('data-shaparak-slider', '1');
		track.addEventListener('scroll', onScroll, { passive: true });
		track.addEventListener('mouseenter', onEnter);
		track.addEventListener('mouseleave', onLeave);
		track.addEventListener('touchstart', onEnter, { passive: true });
		track.addEventListener('touchend', onLeave, { passive: true });
		window.addEventListener('resize', onResize);
		start();
	}

	document.addEventListener('DOMContentLoaded', function () {
		tick();
		reportSliders();
		setInterval(function () {
			tick();
			reportSliders();
		}, 1000);
		toolbar();
		filters();
	});
})();
