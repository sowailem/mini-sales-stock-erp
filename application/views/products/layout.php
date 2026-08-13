<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * App shell shared by all products pages.
 *
 * Renders the application header, flash messages and the content view
 * named by `$content_view`. Only the explicit `$content_data` array is
 * forwarded — never the loader's internal variables, which would
 * otherwise leak into (and corrupt) the nested view load.
 */

$username = isset($user) && $user !== NULL ? $user->username : '';
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo isset($page_title) ? html_escape($page_title).' · Mini ERP' : 'Mini ERP'; ?></title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body class="h-full bg-slate-50 antialiased">
	<header class="bg-white shadow-sm ring-1 ring-slate-900/5">
		<div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
			<div class="flex items-center gap-3">
				<a href="<?php echo site_url('dashboard'); ?>" class="flex items-center gap-3">
					<div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600">
						<svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
						</svg>
					</div>
					<span class="text-base font-semibold tracking-tight text-slate-900">Mini ERP</span>
				</a>
				<nav class="ml-4 hidden items-center gap-4 sm:flex">
					<a href="<?php echo site_url('dashboard'); ?>" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Dashboard</a>
					<a href="<?php echo site_url('products'); ?>" class="text-sm font-semibold text-indigo-600">Products</a>
				</nav>
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

		<?php $this->load->view($content_view, isset($content_data) ? $content_data : array()); ?>
	</main>
</body>
</html>
