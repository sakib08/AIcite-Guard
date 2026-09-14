(function () {
	'use strict';

	var root = document.querySelector('[data-aicite-guard-widget]');
	if (!root) {
		return;
	}

	var toggle = root.querySelector('.aicite-guard-a11y__toggle');
	var panel = root.querySelector('.aicite-guard-a11y__panel');
	var storageKey = 'aiciteGuardA11y';
	var state = { font: 100, contrast: false, links: false, spacing: false, motion: false };

	try {
		var saved = window.localStorage.getItem(storageKey);
		if (saved) {
			state = Object.assign(state, JSON.parse(saved));
		}
	} catch (e) {
		// Ignore blocked storage.
	}

	function persist() {
		try {
			window.localStorage.setItem(storageKey, JSON.stringify(state));
		} catch (e) {
			// Ignore blocked storage.
		}
	}

	function apply() {
		document.documentElement.style.fontSize = state.font + '%';
		document.documentElement.classList.toggle('aicite-guard-contrast', !!state.contrast);
		document.documentElement.classList.toggle('aicite-guard-links', !!state.links);
		document.documentElement.classList.toggle('aicite-guard-spacing', !!state.spacing);
		document.documentElement.classList.toggle('aicite-guard-motion', !!state.motion);

		root.querySelectorAll('[data-acg-action]').forEach(function (button) {
			var action = button.getAttribute('data-acg-action');
			button.classList.toggle('is-on', !!state[action]);
		});
	}

	apply();

	toggle.addEventListener('click', function () {
		var open = panel.hasAttribute('hidden');
		if (open) {
			panel.removeAttribute('hidden');
		} else {
			panel.setAttribute('hidden', 'hidden');
		}
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
	});

	root.addEventListener('click', function (event) {
		var button = event.target.closest('[data-acg-action]');
		if (!button) {
			return;
		}
		var action = button.getAttribute('data-acg-action');
		if (action === 'font-up') {
			state.font = Math.min(150, state.font + 10);
		} else if (action === 'font-down') {
			state.font = Math.max(90, state.font - 10);
		} else if (action === 'reset') {
			state = { font: 100, contrast: false, links: false, spacing: false, motion: false };
		} else if (Object.prototype.hasOwnProperty.call(state, action)) {
			state[action] = !state[action];
		}
		persist();
		apply();
	});
})();
