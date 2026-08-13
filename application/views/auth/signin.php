<?php defined('BASEPATH') OR exit('No direct script access allowed');

$username_error = form_error('username', '', '');
$password_error = form_error('password', '', '');
?>
<div>
	<h2 class="text-2xl font-semibold tracking-tight text-slate-900">Welcome back</h2>
	<p class="mt-2 text-sm text-slate-500">Sign in to your account</p>

	<?php echo form_open('auth/signin', array('class' => 'mt-8 space-y-6', 'novalidate' => 'novalidate')); ?>

		<div>
			<label for="username" class="block text-sm font-medium text-slate-700">Username</label>
			<div class="mt-2">
				<input type="text" name="username" id="username" value="<?php echo set_value('username'); ?>"
					autocomplete="username" required
					class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $username_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
					<?php echo $username_error ? 'aria-describedby="username-error"' : ''; ?>>
			</div>
			<?php if ($username_error): ?>
				<p id="username-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($username_error); ?></p>
			<?php endif; ?>
		</div>

		<div>
			<label for="password" class="block text-sm font-medium text-slate-700">Password</label>
			<div class="mt-2">
				<input type="password" name="password" id="password"
					autocomplete="current-password" required
					class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $password_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
					<?php echo $password_error ? 'aria-describedby="password-error"' : ''; ?>>
			</div>
			<?php if ($password_error): ?>
				<p id="password-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($password_error); ?></p>
			<?php endif; ?>
		</div>

		<div>
			<button type="submit"
				class="flex w-full justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
				Sign In
			</button>
		</div>
	<?php echo form_close(); ?>

	<p class="mt-6 text-center text-sm text-slate-500">
		Don't have an account?
		<a href="<?php echo site_url('auth/signup'); ?>" class="font-semibold text-indigo-600 hover:text-indigo-500">Create one</a>
	</p>
</div>
