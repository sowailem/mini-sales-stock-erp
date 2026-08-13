<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Warehouse listing (content view, loaded by products/layout).
 *
 * Expects: $warehouses
 */
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Warehouses</h1>
		<p class="mt-1 text-sm text-slate-500">Manage the warehouses in your inventory.</p>
	</div>
	<a href="<?php echo site_url('inventory/warehouses/create'); ?>"
		class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
		Add Warehouse
	</a>
</div>

<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
	<?php if (empty($warehouses)): ?>

		<!-- Empty state -->
		<div class="px-6 py-16 text-center">
			<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
				<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
				</svg>
			</div>
			<h2 class="mt-4 text-base font-semibold text-slate-900">No warehouses found.</h2>
			<p class="mt-1 text-sm text-slate-500">Create your first warehouse to start managing inventory.</p>
			<div class="mt-6">
				<a href="<?php echo site_url('inventory/warehouses/create'); ?>"
					class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Add Warehouse
				</a>
			</div>
		</div>

	<?php else: ?>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($warehouses as $warehouse): ?>
						<tr class="transition hover:bg-slate-50">
							<td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900"><?php echo html_escape($warehouse->name); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
								<a href="<?php echo site_url('inventory?warehouse_id='.(int) $warehouse->id); ?>" class="text-indigo-600 transition hover:text-indigo-500">View Inventory</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>
</div>
