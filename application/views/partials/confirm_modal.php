<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reusable confirmation modal (vanilla JS — no frameworks).
 *
 * Trigger buttons must carry:
 *   data-confirm            — marks the element as a trigger
 *   data-confirm-message    — confirmation text (inserted as text, never HTML)
 *   data-confirm-action     — POST endpoint used as the form action
 *
 * The wiring lives in assets/js/app.js. The form action is populated
 * at runtime so every trigger reuses this single CSRF-protected form.
 */
?>
<div id="confirm-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message">
	<div class="fixed inset-0 bg-slate-900/50" aria-hidden="true" data-confirm-backdrop></div>
	<div class="fixed inset-0 z-10 flex items-center justify-center p-4" data-confirm-panel-wrap>
		<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
			<h2 id="confirm-modal-title" class="text-lg font-semibold tracking-tight text-slate-900">Confirm action</h2>
			<p id="confirm-modal-message" class="mt-2 text-sm text-slate-500"></p>

			<?php echo form_open('products/disable/0', array(
				'id' => 'confirm-modal-form',
				'class' => 'mt-6 flex items-center justify-end gap-3',
				'novalidate' => 'novalidate',
			)); ?>
				<button type="button" data-confirm-cancel
					class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Cancel
				</button>
				<button type="submit"
					class="rounded-lg bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
					Disable
				</button>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
