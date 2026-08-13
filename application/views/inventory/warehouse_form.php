<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add-warehouse page (content view, loaded by products/layout).
 *
 * Values come from set_value() so submitted input is preserved when
 * validation fails.
 */

$name_error = form_error('name', '', '');
?>

<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Warehouse</h1>
		<p class="mt-1 text-sm text-slate-500">Add a new warehouse to your inventory.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php echo form_open('inventory/warehouses/store', array('class' => 'space-y-6', 'novalidate' => 'novalidate')); ?>

			<div>
				<label for="name" class="block text-sm font-medium text-slate-700">Warehouse name</label>
				<div class="mt-2">
					<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>"
						required maxlength="100"
						class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $name_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
						<?php echo $name_error ? 'aria-describedby="name-error"' : ''; ?>>
				</div>
				<?php if ($name_error): ?>
					<p id="name-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($name_error); ?></p>
				<?php endif; ?>
			</div>

			<div class="flex items-center gap-3 pt-2">
				<button type="submit"
					class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Create Warehouse
				</button>
				<a href="<?php echo site_url('inventory/warehouses'); ?>"
					class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Cancel
				</a>
			</div>

		<?php echo form_close(); ?>
	</div>
</div>
