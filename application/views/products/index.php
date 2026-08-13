<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product listing (content view, loaded by products/layout).
 *
 * Expects: $products, $categories, $pagination_links, $search,
 * $category_id, $total, $page, $start, $end
 */

$has_filters = ($search !== '' || $category_id !== NULL);
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Products</h1>
		<p class="mt-1 text-sm text-slate-500">Manage the products in your catalog.</p>
	</div>
	<a href="<?php echo site_url('products/create'); ?>"
		class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
		Add Product
	</a>
</div>

<!-- Filters -->
<div class="mt-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:p-6">
	<form method="get" action="<?php echo site_url('products'); ?>" class="flex flex-col gap-4 lg:flex-row lg:items-end">
		<div class="flex-1">
			<label for="q" class="block text-sm font-medium text-slate-700">Search</label>
			<div class="mt-2">
				<input type="search" name="q" id="q" value="<?php echo html_escape($search); ?>"
					placeholder="Search by name or code…" maxlength="100"
					class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
			</div>
		</div>

		<div class="w-full lg:w-56">
			<label for="category" class="block text-sm font-medium text-slate-700">Category</label>
			<div class="mt-2">
				<select name="category" id="category"
					class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
					<option value="">All categories</option>
					<?php foreach ($categories as $category): ?>
						<option value="<?php echo (int) $category->id; ?>"<?php echo $category_id !== NULL && $category_id === (int) $category->id ? ' selected' : ''; ?>>
							<?php echo html_escape($category->name); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="flex gap-2">
			<button type="submit"
				class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Search
			</button>
			<a href="<?php echo site_url('products'); ?>"
				class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Clear
			</a>
		</div>
	</form>
</div>

<!-- Results summary -->
<?php if ( ! empty($products)): ?>
	<p class="mt-6 text-sm text-slate-500">
		Showing <span class="font-medium text-slate-700"><?php echo $start; ?>–<?php echo $end; ?></span>
		of <span class="font-medium text-slate-700"><?php echo $total; ?></span> products
	</p>
<?php endif; ?>

<!-- Table -->
<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
	<?php if (empty($products)): ?>

		<!-- Empty state -->
		<div class="px-6 py-16 text-center">
			<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
				<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
				</svg>
			</div>
			<h2 class="mt-4 text-base font-semibold text-slate-900">
				<?php echo $has_filters ? 'No products match your search' : 'No products yet'; ?>
			</h2>
			<p class="mt-1 text-sm text-slate-500">
				<?php echo $has_filters
					? 'Try adjusting your search or category filter.'
					: 'Add your first product to get started.'; ?>
			</p>
			<div class="mt-6">
				<?php if ($has_filters): ?>
					<a href="<?php echo site_url('products'); ?>"
						class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						Clear filters
					</a>
				<?php else: ?>
					<a href="<?php echo site_url('products/create'); ?>"
						class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						Add Product
					</a>
				<?php endif; ?>
			</div>
		</div>

	<?php else: ?>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Code</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($products as $product): ?>
						<tr class="transition hover:bg-slate-50">
							<td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900"><?php echo html_escape($product->code); ?></td>
							<td class="px-6 py-4 text-sm text-slate-700"><?php echo html_escape($product->name); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
								<?php if ($product->category_name !== NULL): ?>
									<?php echo html_escape($product->category_name); ?>
								<?php else: ?>
									<span class="text-slate-400">—</span>
								<?php endif; ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
								<?php echo '$' . number_format((float) $product->price, 2); ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4">
								<?php if ((int) $product->is_active === 1): ?>
									<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-800 ring-1 ring-inset ring-emerald-200">Active</span>
								<?php else: ?>
									<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">Inactive</span>
								<?php endif; ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
								<a href="<?php echo site_url('products/edit/'.$product->id); ?>" class="text-indigo-600 transition hover:text-indigo-500">Edit</a>
								<?php if ((int) $product->is_active === 1): ?>
									<button type="button"
										class="ml-3 text-red-600 transition hover:text-red-500"
										data-confirm
										data-confirm-message="<?php echo html_escape('Disable "'.$product->name.'"? It will no longer appear as active.'); ?>"
										data-confirm-action="<?php echo site_url('products/disable/'.$product->id); ?>">
										Disable
									</button>
								<?php endif; ?>
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

<?php $this->load->view('partials/confirm_modal'); ?>
<script src="<?php echo base_url('assets/js/app.js'); ?>" defer></script>
