<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reusable authentication layout.
 *
 * Shared by sign-in and sign-up. The `$content_view` variable names the
 * view that provides the card contents (title, description, form, links).
 */
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo isset($page_title) ? html_escape($page_title).' · Mini ERP' : 'Mini ERP'; ?></title>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body class="h-full bg-slate-50 antialiased">
	<div class="flex min-h-full flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
		<div class="mx-auto w-full max-w-md">

			<!-- Brand -->
			<div class="flex flex-col items-center">
				<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 shadow-sm">
					<svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
					</svg>
				</div>
				<p class="mt-4 text-lg font-semibold tracking-tight text-slate-900">Mini ERP</p>
			</div>

			<!-- Flash messages -->
			<div class="mt-8">
				<?php $this->load->view('partials/flash_messages'); ?>
			</div>

			<!-- Authentication card -->
			<div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
				<?php $this->load->view($content_view); ?>
			</div>
		</div>
	</div>
</body>
</html>
