(function () {
	'use strict';

	var config = window.spgByPprosAdmin || {};
	var notice = document.querySelector('[data-spg-by-ppros-notice]');

	function showNotice(message, isError) {
		if (!notice) {
			return;
		}
		notice.hidden = false;
		notice.textContent = message;
		notice.style.borderLeftColor = isError ? '#b32d2e' : '#0f766e';
	}

	function post(action, extra) {
		var body = new window.URLSearchParams();
		body.set('action', action);
		body.set('nonce', config.nonce || '');
		if (extra) {
			Object.keys(extra).forEach(function (key) {
				body.set(key, extra[key]);
			});
		}

		return window.fetch(config.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (response) {
			return response.json();
		});
	}

	function withBusy(button, work) {
		if (!button) {
			return work();
		}
		var original = button.textContent;
		button.disabled = true;
		button.textContent = (config.i18n && config.i18n.working) || 'Working…';
		return work().finally(function () {
			button.disabled = false;
			button.textContent = original;
		});
	}

	function reloadSoon() {
		window.setTimeout(function () {
			window.location.reload();
		}, 700);
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-spg-by-ppros-action]');
		if (!button) {
			return;
		}

		var action = button.getAttribute('data-spg-by-ppros-action');

		if (action === 'regenerate-llms') {
			event.preventDefault();
			withBusy(button, function () {
				return post('spg_by_ppros_regenerate_llms').then(function (json) {
					if (!json.success) {
						throw new Error((json.data && json.data.message) || (config.i18n && config.i18n.error));
					}
					var preview = document.getElementById('spg-by-ppros-llms-preview');
					if (preview && json.data.basic) {
						preview.textContent = json.data.basic;
					}
					showNotice((config.i18n && config.i18n.done) || 'Done.');
					reloadSoon();
				}).catch(function (error) {
					showNotice(error.message, true);
				});
			});
		}

		if (action === 'scan-a11y' || action === 'refresh-health' || action === 'statement') {
			event.preventDefault();
			var map = {
				'scan-a11y': 'spg_by_ppros_scan_a11y',
				'refresh-health': 'spg_by_ppros_refresh_health',
				statement: 'spg_by_ppros_statement'
			};
			withBusy(button, function () {
				return post(map[action]).then(function (json) {
					if (!json.success) {
						throw new Error((json.data && json.data.message) || (config.i18n && config.i18n.error));
					}
					showNotice((config.i18n && config.i18n.done) || 'Done.');
					reloadSoon();
				}).catch(function (error) {
					showNotice(error.message, true);
				});
			});
		}

		if (action === 'generate-alt' || action === 'apply-alt') {
			event.preventDefault();
			var row = button.closest('tr');
			if (!row) {
				return;
			}
			var id = row.getAttribute('data-attachment');
			var field = row.querySelector('.spg-by-ppros-alt-field');
			var extra = { attachment_id: id };

			if (action === 'apply-alt') {
				if (!window.confirm((config.i18n && config.i18n.confirm) || 'Apply?')) {
					return;
				}
				extra.alt = field ? field.value : '';
			}

			withBusy(button, function () {
				return post(action === 'generate-alt' ? 'spg_by_ppros_generate_alt' : 'spg_by_ppros_apply_alt', extra).then(function (json) {
					if (!json.success) {
						throw new Error((json.data && json.data.message) || (config.i18n && config.i18n.error));
					}
					if (action === 'generate-alt' && field && json.data.alt) {
						field.value = json.data.alt;
						showNotice((config.i18n && config.i18n.done) || 'Done.');
						return;
					}
					row.parentNode.removeChild(row);
					showNotice((config.i18n && config.i18n.applied) || 'Applied.');
				}).catch(function (error) {
					showNotice(error.message, true);
				});
			});
		}
	});

	var wizard = document.querySelector('[data-spg-by-ppros-wizard]');
	if (!wizard) {
		return;
	}

	var steps = wizard.querySelectorAll('.spg-by-ppros-step');
	var pills = wizard.querySelectorAll('.spg-by-ppros-steps li');
	var current = 1;

	function showStep(index) {
		current = index;
		steps.forEach(function (step) {
			var active = Number(step.getAttribute('data-step')) === index;
			step.hidden = !active;
			step.classList.toggle('is-active', active);
		});
		pills.forEach(function (pill, i) {
			pill.classList.toggle('is-active', i === index - 1);
		});
	}

	wizard.addEventListener('click', function (event) {
		if (event.target.closest('[data-spg-by-ppros-next]')) {
			showStep(Math.min(4, current + 1));
		}
		if (event.target.closest('[data-spg-by-ppros-prev]')) {
			showStep(Math.max(1, current - 1));
		}
		if (!event.target.closest('[data-spg-by-ppros-finish]')) {
			return;
		}

		var types = [];
		wizard.querySelectorAll('input[name="wiz_types[]"]:checked').forEach(function (box) {
			types.push(box.value);
		});

		var payload = {
			ai_visibility: {
				enabled: wizard.querySelector('[name="wiz_ai_enabled"]').checked,
				post_types: types,
				include_excerpts: true,
				auto_update: true,
				max_items: 50
			},
			crawler: {
				robots_integration: true,
				allow_answer_engines: wizard.querySelector('[name="wiz_allow_engines"]').checked,
				block_training: wizard.querySelector('[name="wiz_block_training"]').checked
			},
			accessibility: {
				enabled: wizard.querySelector('[name="wiz_a11y"]').checked,
				widget_enabled: wizard.querySelector('[name="wiz_widget"]').checked
			},
			health: {
				enabled: wizard.querySelector('[name="wiz_health"]').checked
			},
			wizard_complete: true
		};

		var finish = event.target.closest('[data-spg-by-ppros-finish]');
		withBusy(finish, function () {
			return post('spg_by_ppros_save_wizard', { settings: JSON.stringify(payload) }).then(function (json) {
				if (!json.success) {
					throw new Error((json.data && json.data.message) || (config.i18n && config.i18n.error));
				}
				window.location.href = json.data.redirect;
			}).catch(function (error) {
				showNotice(error.message, true);
			});
		});
	});
})();
