<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared customer form (create and edit).
 *
 * Expects:
 *   $form_action  — URI for form_open() (e.g. 'customers/store')
 *   $button_label — submit button text
 *   $customer     — customer being edited (object) or NULL when creating
 *
 * Values come from set_value() so submitted input is preserved when
 * validation fails; the customer row is only the fallback default.
 */

$customer = isset($customer) ? $customer : NULL;

$name_error = form_error('name', '', '');
$phone_error = form_error('phone', '', '');
?>

<?php echo form_open($form_action, array('class' => 'space-y-6', 'novalidate' => 'novalidate')); ?>

	<div>
		<label for="name" class="block text-sm font-medium text-slate-700">Name</label>
		<div class="mt-2">
			<input type="text" name="name" id="name" value="<?php echo set_value('name', $customer ? $customer->name : ''); ?>"
				required maxlength="150"
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $name_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $name_error ? 'aria-describedby="name-error"' : ''; ?>>
		</div>
		<?php if ($name_error): ?>
			<p id="name-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($name_error); ?></p>
		<?php endif; ?>
	</div>

	<div>
		<label for="phone" class="block text-sm font-medium text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label>
		<div class="mt-2">
			<input type="tel" name="phone" id="phone" value="<?php echo set_value('phone', $customer ? $customer->phone : ''); ?>"
				maxlength="30" inputmode="tel" placeholder="e.g. +1 555 0100"
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $phone_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $phone_error ? 'aria-describedby="phone-error"' : ''; ?>>
		</div>
		<?php if ($phone_error): ?>
			<p id="phone-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($phone_error); ?></p>
		<?php endif; ?>
	</div>

	<div class="flex items-center gap-3 pt-2">
		<button type="submit"
			class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			<?php echo html_escape($button_label); ?>
		</button>
		<a href="<?php echo site_url('customers'); ?>"
			class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			Cancel
		</a>
	</div>

<?php echo form_close(); ?>
