<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sales invoice listing (content view, loaded by products/layout).
 *
 * Expects: $sales, $search, $pagination_links, $total, $page, $start, $end
 */

$has_filters = ($search !== '');

function _sales_date($created_at)
{
	$timestamp = strtotime($created_at);
	return $timestamp !== FALSE ? date('M j, Y g:i A', $timestamp) : html_escape($created_at);
}
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Sales</h1>
		<p class="mt-1 text-sm text-slate-500">Recent sales invoices.</p>
	</div>
	<a href="<?php echo site_url('sales/create'); ?>"
		class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
		New Sale
	</a>
</div>

<!-- Search filter -->
<div class="mt-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:p-6">
	<form method="get" action="<?php echo site_url('sales'); ?>" class="flex flex-col gap-4 sm:flex-row sm:items-end">
		<div class="flex-1">
			<label for="q" class="block text-sm font-medium text-slate-700">Search</label>
			<div class="mt-2">
				<input type="search" name="q" id="q" value="<?php echo html_escape($search); ?>"
					placeholder="Search by invoice number or customer…" maxlength="100"
					class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
			</div>
		</div>

		<div class="flex gap-2">
			<button type="submit"
				class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Search
			</button>
			<a href="<?php echo site_url('sales'); ?>"
				class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Clear
			</a>
		</div>
	</form>
</div>

<!-- Results summary -->
<?php if ( ! empty($sales)): ?>
	<p class="mt-6 text-sm text-slate-500">
		Showing <span class="font-medium text-slate-700"><?php echo $start; ?>–<?php echo $end; ?></span>
		of <span class="font-medium text-slate-700"><?php echo $total; ?></span> invoices
	</p>
<?php endif; ?>

<!-- Table -->
<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
	<?php if (empty($sales)): ?>

		<!-- Empty state -->
		<div class="px-6 py-16 text-center">
			<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
				<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
				</svg>
			</div>
			<h2 class="mt-4 text-base font-semibold text-slate-900">
				<?php echo $has_filters ? 'No invoices match your search' : 'No invoices yet.'; ?>
			</h2>
			<p class="mt-1 text-sm text-slate-500">
				<?php echo $has_filters
					? 'Try a different invoice number or customer name.'
					: 'Create your first sale to start recording invoices.'; ?>
			</p>
			<div class="mt-6">
				<?php if ($has_filters): ?>
					<a href="<?php echo site_url('sales'); ?>"
						class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						Clear search
					</a>
				<?php else: ?>
					<a href="<?php echo site_url('sales/create'); ?>"
						class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						New Sale
					</a>
				<?php endif; ?>
			</div>
		</div>

	<?php else: ?>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
						<th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Items</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($sales as $sale): ?>
						<tr class="transition hover:bg-slate-50">
							<td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
								<a href="<?php echo site_url('sales/view/'.(int) $sale->id); ?>" class="text-indigo-600 transition hover:text-indigo-500">#<?php echo (int) $sale->id; ?></a>
							</td>
							<td class="px-6 py-4 text-sm text-slate-700"><?php echo html_escape($sale->customer_name); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700"><?php echo html_escape($sale->warehouse_name); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500"><?php echo _sales_date($sale->created_at); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-center text-sm text-slate-700">
								<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
									<?php echo (int) $sale->item_count; ?>
								</span>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-900">
								<?php echo '$' . number_format((float) $sale->total, 2); ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
								<a href="<?php echo site_url('sales/view/'.(int) $sale->id); ?>" class="text-indigo-600 transition hover:text-indigo-500">View</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>
</div>

<?php if ( ! empty($pagination_links)): ?>
	<?php echo $pagination_links; ?>
<?php endif; ?>
