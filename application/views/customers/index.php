<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer listing (content view, loaded by products/layout).
 *
 * Expects: $customers
 */
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Customers</h1>
		<p class="mt-1 text-sm text-slate-500">Manage the customers in your business.</p>
	</div>
	<a href="<?php echo site_url('customers/create'); ?>"
		class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
		Add Customer
	</a>
</div>

<div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
	<?php if (empty($customers)): ?>

		<!-- Empty state -->
		<div class="px-6 py-16 text-center">
			<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
				<svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
				</svg>
			</div>
			<h2 class="mt-4 text-base font-semibold text-slate-900">No customers yet</h2>
			<p class="mt-1 text-sm text-slate-500">Add your first customer to get started.</p>
			<div class="mt-6">
				<a href="<?php echo site_url('customers/create'); ?>"
					class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Add Customer
				</a>
			</div>
		</div>

	<?php else: ?>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">ID</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
						<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</th>
						<th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($customers as $customer): ?>
						<tr class="transition hover:bg-slate-50">
							<td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500"><?php echo (int) $customer->id; ?></td>
							<td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo html_escape($customer->name); ?></td>
							<td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
								<?php if ($customer->phone !== NULL && $customer->phone !== ''): ?>
									<?php echo html_escape($customer->phone); ?>
								<?php else: ?>
									<span class="text-slate-400">—</span>
								<?php endif; ?>
							</td>
							<td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
								<a href="<?php echo site_url('customers/edit/'.$customer->id); ?>" class="text-indigo-600 transition hover:text-indigo-500">Edit</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>
</div>
