<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product quantity page (content view, loaded by products/layout).
 *
 * Expects: $warehouse (object), $product (object), $quantity (int)
 */
?>

<div class="max-w-3xl">
	<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
		<div>
			<h1 class="text-2xl font-semibold tracking-tight text-slate-900"><?php echo html_escape($product->name); ?></h1>
			<p class="mt-1 text-sm text-slate-500">Available quantity in a single warehouse.</p>
		</div>
		<a href="<?php echo site_url('inventory'); ?>"
			class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			Back to Inventory
		</a>
	</div>

	<div class="mt-6 grid gap-6 sm:grid-cols-3">
		<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
			<h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</h2>
			<p class="mt-2 text-lg font-semibold text-slate-900"><?php echo html_escape($warehouse->name); ?></p>
		</div>

		<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
			<h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product</h2>
			<p class="mt-2 text-lg font-semibold text-slate-900"><?php echo html_escape($product->name); ?></p>
			<p class="mt-1 text-sm text-slate-500"><?php echo html_escape($product->code); ?></p>
		</div>

		<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
			<h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available Quantity</h2>
			<p class="mt-2 text-3xl font-bold tracking-tight text-slate-900"><?php echo number_format($quantity); ?></p>
		</div>
	</div>
</div>
