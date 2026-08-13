/* Confirmation modal wiring (vanilla JS, no frameworks).
 *
 * Any element with [data-confirm] opens the #confirm-modal on click.
 * The modal copies the trigger's data-confirm-message into the dialog
 * and points the shared POST form at data-confirm-action.
 *
 * Closes on: Cancel, backdrop/outside click, Escape, form submit.
 * Focus returns to the trigger after closing.
 */
(function () {
	'use strict';

	var modal = document.getElementById('confirm-modal');

	// No modal on this page (e.g. create/edit forms) — nothing to do.
	if (modal === null) {
		return;
	}

	var messageEl = document.getElementById('confirm-modal-message');
	var form = document.getElementById('confirm-modal-form');
	var cancelButton = modal.querySelector('[data-confirm-cancel]');
	var panelWrap = modal.querySelector('[data-confirm-panel-wrap]');
	var lastTrigger = null;

	function closeModal()
	{
		modal.classList.add('hidden');

		if (lastTrigger !== null)
		{
			lastTrigger.focus();
			lastTrigger = null;
		}
	}

	function openModal(trigger)
	{
		lastTrigger = trigger;
		messageEl.textContent = trigger.getAttribute('data-confirm-message') || '';
		form.setAttribute('action', trigger.getAttribute('data-confirm-action') || '');
		modal.classList.remove('hidden');
		cancelButton.focus();
	}

	// Open on trigger click.
	document.querySelectorAll('[data-confirm]').forEach(function (trigger)
	{
		trigger.addEventListener('click', function ()
		{
			openModal(trigger);
		});
	});

	// Close on Cancel.
	cancelButton.addEventListener('click', closeModal);

	// Close on outside click (click on the wrapper itself, not the panel).
	panelWrap.addEventListener('click', function (event)
	{
		if (event.target === panelWrap)
		{
			closeModal();
		}
	});

	// Close on Escape.
	document.addEventListener('keydown', function (event)
	{
		if (event.key === 'Escape' && ! modal.classList.contains('hidden'))
		{
			closeModal();
		}
	});
})();
