<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Single invoice page (content view, loaded by products/layout).
 *
 * Expects: $sale (object), $items (array of line items)
 */

$created_at = strtotime($sale->created_at);
$sale_date = $created_at !== FALSE ? date('M j, Y g:i A', $created_at) : html_escape($sale->created_at);
?>

<div class="max-w-4xl">
	<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
		<div>
			<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Invoice #<?php echo (int) $sale->id; ?></h1>
			<p class="mt-1 text-sm text-slate-500"><?php echo html_escape($sale_date); ?></p>
		</div>
		<div class="flex flex-col gap-2 sm:flex-row">
			<a href="<?php echo site_url('sales/create'); ?>"
				class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				New Sale
			</a>
			<a href="<?php echo site_url('sales'); ?>"
				class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Back to Sales
			</a>
		</div>
	</div>

	<!-- Invoice header -->
	<div class="mt-6 grid gap-6 sm:grid-cols-2">
		<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
			<h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</h2>
			<p class="mt-2 text-lg font-semibold text-slate-900"><?php echo html_escape($sale->customer_name); ?></p>
		</div>
		<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
			<h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</h2>
			<p class="mt-2 text-lg font-semibold text-slate-900"><?php echo html_escape($sale->warehouse_name); ?></p>
		</div>
	</div>

	<!-- Line items -->
	<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
		<div class="border-b border-slate-200 px-6 py-4">
			<h2 class="text-base font-semibold tracking-tight text-slate-900">Items</h2>
		</div>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($items as $item): ?>
						<tr class="transition hover:bg-slate-50">
							<td class="px-6 py-4">
								<span class="block text-sm font-medium text-slate-900"><?php echo html_escape($item->product_name); ?></span>
								<?php if ($item->product_code): ?>
									<span class="block text-xs text-slate-400"><?php echo html_escape($item->product_code); ?></span>
								<?php endif; ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-700"><?php echo number_format((int) $item->quantity); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-700"><?php echo '$' . number_format((float) $item->price, 2); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-900"><?php echo '$' . number_format((float) $item->total, 2); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Summary -->
	<div class="mt-6 flex justify-end">
		<dl class="w-full space-y-2 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:w-64">
			<div class="flex items-center justify-between gap-4">
				<dt class="text-sm text-slate-500">Subtotal</dt>
				<dd class="text-sm font-medium text-slate-900"><?php echo '$' . number_format((float) $sale->subtotal, 2); ?></dd>
			</div>
			<div class="flex items-center justify-between gap-4">
				<dt class="text-sm text-slate-500">Discount</dt>
				<dd class="text-sm font-medium text-slate-900"><?php echo '$' . number_format((float) $sale->discount, 2); ?></dd>
			</div>
			<div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-2">
				<dt class="text-sm font-semibold text-slate-900">Total</dt>
				<dd class="text-lg font-semibold text-indigo-600"><?php echo '$' . number_format((float) $sale->total, 2); ?></dd>
			</div>
		</dl>
	</div>
</div>
