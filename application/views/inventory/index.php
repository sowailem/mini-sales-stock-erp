<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inventory listing (content view, loaded by products/layout).
 *
 * Admins: full warehouse filter + warehouse-management buttons.
 * Warehouse users: no selector, their assigned warehouse shown as
 * read-only information, inventory limited to that warehouse.
 *
 * Expects: $inventory, $warehouses, $warehouse_id (int|NULL),
 *          $is_admin (bool), $assigned_warehouse (object|NULL)
 */

$has_warehouses = ! empty($warehouses);
$has_filter = $warehouse_id !== NULL;
$is_warehouse_user = ! $is_admin;
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Inventory</h1>
		<p class="mt-1 text-sm text-slate-500"><?php echo $is_warehouse_user ? 'Product quantities for your assigned warehouse.' : 'Manage and view product quantities across warehouses.'; ?></p>
	</div>
	<?php if ($is_admin): ?>
		<div class="flex flex-col gap-2 sm:flex-row">
			<a href="<?php echo site_url('inventory/warehouses'); ?>"
				class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Warehouses
			</a>
			<a href="<?php echo site_url('inventory/warehouses/create'); ?>"
				class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Add Warehouse
			</a>
		</div>
	<?php endif; ?>
</div>

<?php if ($is_admin && ! $has_warehouses): ?>

	<!-- Empty state: no warehouses yet (admin only) -->
	<div class="mt-6 rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-900/5">
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

	<!-- Warehouse filter (admin) or assigned warehouse (warehouse user) -->
	<div class="mt-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:p-6">
		<?php if ($is_warehouse_user): ?>

			<div class="flex items-center justify-between gap-4">
				<div>
					<p class="text-sm font-medium text-slate-700">Warehouse</p>
					<p class="mt-1 text-sm font-semibold text-slate-900"><?php echo $assigned_warehouse !== NULL ? html_escape($assigned_warehouse->name) : '—'; ?></p>
				</div>
				<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">Assigned</span>
			</div>
			<p class="mt-2 text-xs text-slate-400">You can only view inventory for your assigned warehouse.</p>

		<?php else: ?>

			<form method="get" action="<?php echo site_url('inventory'); ?>" class="flex flex-col gap-4 sm:flex-row sm:items-end">
				<div class="w-full sm:w-72">
					<label for="warehouse_id" class="block text-sm font-medium text-slate-700">Warehouse</label>
					<div class="mt-2">
						<select name="warehouse_id" id="warehouse_id"
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
							<option value="">All Warehouses</option>
							<?php foreach ($warehouses as $warehouse): ?>
								<option value="<?php echo (int) $warehouse->id; ?>"<?php echo $has_filter && $warehouse_id === (int) $warehouse->id ? ' selected' : ''; ?>>
									<?php echo html_escape($warehouse->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="flex gap-2">
					<button type="submit"
						class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						Filter
					</button>
					<?php if ($has_filter): ?>
						<a href="<?php echo site_url('inventory'); ?>"
							class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
							Clear
						</a>
					<?php endif; ?>
				</div>
			</form>

		<?php endif; ?>
	</div>

	<!-- Inventory table -->
	<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
		<?php if (empty($inventory)): ?>

			<!-- Empty state: no stock records -->
			<div class="px-6 py-16 text-center">
				<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
					<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
					</svg>
				</div>
				<h2 class="mt-4 text-base font-semibold text-slate-900">
					<?php echo $has_filter ? 'No products found in this warehouse.' : 'No inventory found.'; ?>
				</h2>
				<p class="mt-1 text-sm text-slate-500">
					<?php echo $has_filter
						? 'This warehouse has no stock records yet.'
						: 'Add stock to your warehouses to see it here.'; ?>
				</p>
			</div>

		<?php else: ?>

			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-slate-200">
					<thead class="bg-slate-50">
						<tr>
							<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
							<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
							<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Quantity</th>
							<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-200 bg-white">
						<?php foreach ($inventory as $row): ?>
							<tr class="transition hover:bg-slate-50">
								<td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900"><?php echo html_escape($row->warehouse_name); ?></td>
								<td class="px-6 py-4 text-sm text-slate-700"><?php echo html_escape($row->product_name); ?></td>
								<td class="whitespace-nowrap px-6 py-4">
									<?php if ((int) $row->quantity > 0): ?>
										<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-sm font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">
											<?php echo number_format((int) $row->quantity); ?>
										</span>
									<?php else: ?>
										<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
											0
										</span>
									<?php endif; ?>
								</td>
								<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
									<a href="<?php echo site_url('inventory/product/'.$row->warehouse_id.'/'.$row->product_id); ?>" class="text-indigo-600 transition hover:text-indigo-500">View</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		<?php endif; ?>
	</div>

<?php endif; ?>
