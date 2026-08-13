<?php defined('BASEPATH') OR exit('No direct script access allowed');

$username = isset($user) && $user !== NULL ? $user->username : '';
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard · Mini ERP</title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body class="h-full bg-slate-50 antialiased">
	<header class="bg-white shadow-sm ring-1 ring-slate-900/5">
		<div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
			<div class="flex items-center gap-3">
				<div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600">
					<svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
					</svg>
				</div>
				<span class="text-base font-semibold tracking-tight text-slate-900">Mini ERP</span>
			</div>

			<div class="flex items-center gap-4">
				<span class="text-sm font-medium text-slate-600"><?php echo html_escape($username); ?></span>
				<?php echo form_open('auth/signout', array('class' => 'inline', 'novalidate' => 'novalidate')); ?>
					<button type="submit"
						class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
						Sign out
					</button>
				<?php echo form_close(); ?>
			</div>
		</div>
	</header>

	<main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
		<?php $this->load->view('partials/flash_messages'); ?>

		<div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
			<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Welcome, <?php echo html_escape($username); ?> 👋</h1>
			<p class="mt-2 text-sm text-slate-500">
				This is a protected page. Only signed-in users can see it — guests are redirected to the sign-in page.
			</p>
		</div>

		<div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
			<a href="<?php echo site_url('products'); ?>" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
				<h2 class="text-sm font-semibold text-slate-900">Products</h2>
				<p class="mt-1 text-sm text-slate-500">Manage your product catalog</p>
			</a>
			<a href="<?php echo site_url('customers'); ?>" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
				<h2 class="text-sm font-semibold text-slate-900">Customers</h2>
				<p class="mt-1 text-sm text-slate-500">Manage your customer list</p>
			</a>
			<a href="<?php echo site_url('sales/create'); ?>" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
				<h2 class="text-sm font-semibold text-slate-900">Sales</h2>
				<p class="mt-1 text-sm text-slate-500">Create a sales invoice</p>
			</a>
			<a href="<?php echo site_url('inventory'); ?>" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
				<h2 class="text-sm font-semibold text-slate-900">Inventory</h2>
				<p class="mt-1 text-sm text-slate-500">Manage warehouse inventory</p>
			</a>
			<?php if (isset($user) && $user !== NULL && $user->user_type === 'admin'): ?>
				<a href="<?php echo site_url('users'); ?>" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
					<h2 class="text-sm font-semibold text-slate-900">Users</h2>
					<p class="mt-1 text-sm text-slate-500">Manage user accounts and warehouse assignments</p>
				</a>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>
