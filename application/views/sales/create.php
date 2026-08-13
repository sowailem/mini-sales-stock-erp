<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * New Sale page (content view, loaded by products/layout).
 *
 * The invoice items are built client-side by assets/js/sales.js; the
 * table rows carry the product_id[]/quantity[] fields that are posted
 * to sales/store. Expects: $customers, $warehouses.
 */

// form_error() wraps the message in the default <p> delimiters; strip
// them so only the plain message is rendered inside the styled <p> below.
$customer_error = trim(strip_tags(form_error('customer_id', '', '')));
$warehouse_error = trim(strip_tags(form_error('warehouse_id', '', '')));
$discount_error = trim(strip_tags(form_error('discount', '', '')));
?>

<div class="max-w-4xl">
	<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
		<div>
			<h1 class="text-2xl font-semibold tracking-tight text-slate-900">New Sale</h1>
			<p class="mt-1 text-sm text-slate-500">Create a sales invoice for a customer.</p>
		</div>
	</div>

	<!-- Client-side validation feedback (hidden until needed) -->
	<div id="invoice-error" role="alert" class="mt-6 hidden items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800 ring-1 ring-inset ring-red-200">
		<svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
			<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
		</svg>
		<p id="invoice-error-message"></p>
	</div>

	<?php echo form_open('sales/store', array(
		'id' => 'sale-form',
		'class' => 'space-y-6',
		'novalidate' => 'novalidate',
	)); ?>

		<!-- Customer + Warehouse -->
		<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
			<div class="grid gap-6 sm:grid-cols-2">
				<div>
					<label for="customer_id" class="block text-sm font-medium text-slate-700">Customer</label>
					<div class="mt-2">
						<select name="customer_id" id="customer_id" required
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm <?php echo $customer_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
							<?php echo $customer_error ? 'aria-describedby="customer_id-error"' : ''; ?>>
							<option value="">Select customer…</option>
							<?php foreach ($customers as $customer): ?>
								<option value="<?php echo (int) $customer->id; ?>"<?php echo set_select('customer_id', (string) $customer->id); ?>>
									<?php echo html_escape($customer->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php if ($customer_error): ?>
						<p id="customer_id-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($customer_error); ?></p>
					<?php endif; ?>
				</div>

				<div>
					<label for="warehouse_id" class="block text-sm font-medium text-slate-700">Warehouse</label>
					<div class="mt-2">
						<select name="warehouse_id" id="warehouse_id" required
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm <?php echo $warehouse_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
							<?php echo $warehouse_error ? 'aria-describedby="warehouse_id-error"' : ''; ?>>
							<option value="">Select warehouse…</option>
							<?php foreach ($warehouses as $warehouse): ?>
								<option value="<?php echo (int) $warehouse->id; ?>"<?php echo set_select('warehouse_id', (string) $warehouse->id); ?>>
									<?php echo html_escape($warehouse->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php if ($warehouse_error): ?>
						<p id="warehouse_id-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($warehouse_error); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Product search -->
			<div class="mt-6">
				<label for="product-search-input" class="block text-sm font-medium text-slate-700">Product</label>
				<div class="mt-2">
					<div class="relative" id="product-search" data-search-url="<?php echo site_url('sales/search-products'); ?>">
						<input type="search" id="product-search-input" autocomplete="off" placeholder="Search product…" maxlength="100"
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
						<ul id="product-results" class="absolute left-0 right-0 z-10 mt-1 hidden max-h-72 overflow-y-auto rounded-lg bg-white py-1 shadow-lg ring-1 ring-slate-900/5" role="listbox" aria-label="Product results"></ul>
					</div>
					<p class="mt-1 text-xs text-slate-400">Type to search — stock is shown for the selected warehouse.</p>
				</div>
			</div>
		</div>

		<!-- Invoice items -->
		<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
			<div class="border-b border-slate-200 px-6 py-4">
				<h2 class="text-base font-semibold tracking-tight text-slate-900">Invoice Items</h2>
			</div>

			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-slate-200">
					<thead class="bg-slate-50">
						<tr>
							<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
							<th scope="col" class="w-28 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
							<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
							<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
							<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
						</tr>
					</thead>
					<tbody id="invoice-items" class="divide-y divide-slate-200 bg-white"></tbody>
				</table>
			</div>

			<!-- Empty state -->
			<div id="invoice-empty" class="px-6 py-12 text-center">
				<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
					<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
					</svg>
				</div>
				<h3 class="mt-4 text-base font-semibold text-slate-900">No products added yet.</h3>
				<p class="mt-1 text-sm text-slate-500">Search for a product above to add it to the invoice.</p>
			</div>
		</div>

		<!-- Summary -->
		<div class="mt-6 flex flex-col items-end gap-6 sm:flex-row sm:items-start sm:justify-end">
			<div class="w-full sm:w-56">
				<label for="discount" class="block text-sm font-medium text-slate-700">Discount</label>
				<div class="mt-2">
					<input type="number" name="discount" id="discount" value="<?php echo set_value('discount', '0'); ?>"
						min="0" step="0.01" inputmode="decimal" placeholder="0.00"
						class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $discount_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
						<?php echo $discount_error ? 'aria-describedby="discount-error"' : ''; ?>>
				</div>
				<?php if ($discount_error): ?>
					<p id="discount-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($discount_error); ?></p>
				<?php endif; ?>
			</div>

			<dl class="w-full space-y-2 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-900/5 sm:w-64">
				<div class="flex items-center justify-between gap-4">
					<dt class="text-sm text-slate-500">Subtotal</dt>
					<dd id="summary-subtotal" class="text-sm font-medium text-slate-900">$0.00</dd>
				</div>
				<div class="flex items-center justify-between gap-4">
					<dt class="text-sm text-slate-500">Discount</dt>
					<dd id="summary-discount" class="text-sm font-medium text-slate-900">$0.00</dd>
				</div>
				<div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-2">
					<dt class="text-sm font-semibold text-slate-900">Total</dt>
					<dd id="summary-total" class="text-lg font-semibold text-indigo-600">$0.00</dd>
				</div>
			</dl>
		</div>

		<!-- Actions -->
		<div class="mt-6 flex justify-end">
			<button type="submit" id="save-invoice-btn"
				class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:cursor-not-allowed disabled:opacity-60">
				Save Invoice
			</button>
		</div>

	<?php echo form_close(); ?>
</div>

<script src="<?php echo base_url('assets/js/sales.js'); ?>" defer></script>
