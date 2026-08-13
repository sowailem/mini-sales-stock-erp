<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add-user page (content view, loaded by products/layout).
 *
 * Creates either an admin (no warehouse) or a warehouse user (exactly
 * one valid warehouse). The warehouse field is shown only when the
 * warehouse-user type is selected; the backend ignores it for admins
 * anyway. Expects: $warehouses.
 */

$username_error = trim(strip_tags(form_error('username', '', '')));
$password_error = trim(strip_tags(form_error('password', '', '')));
$password_confirm_error = trim(strip_tags(form_error('password_confirm', '', '')));
$user_type_error = trim(strip_tags(form_error('user_type', '', '')));
$warehouse_error = trim(strip_tags(form_error('warehouse_id', '', '')));
?>
<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add User</h1>
		<p class="mt-1 text-sm text-slate-500">Create an admin account or a warehouse user tied to a single warehouse.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php echo form_open('users/store', array('class' => 'space-y-6', 'novalidate' => 'novalidate')); ?>

			<div>
				<label for="username" class="block text-sm font-medium text-slate-700">Username</label>
				<div class="mt-2">
					<input type="text" name="username" id="username" value="<?php echo set_value('username'); ?>"
						required maxlength="50"
						class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $username_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
						<?php echo $username_error ? 'aria-describedby="username-error"' : ''; ?>>
				</div>
				<?php if ($username_error): ?>
					<p id="username-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($username_error); ?></p>
				<?php endif; ?>
			</div>

			<div class="grid gap-6 sm:grid-cols-2">
				<div>
					<label for="password" class="block text-sm font-medium text-slate-700">Password</label>
					<div class="mt-2">
						<input type="password" name="password" id="password"
							required minlength="8" autocomplete="new-password"
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $password_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
							<?php echo $password_error ? 'aria-describedby="password-error"' : ''; ?>>
					</div>
					<?php if ($password_error): ?>
						<p id="password-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($password_error); ?></p>
					<?php endif; ?>
				</div>

				<div>
					<label for="password_confirm" class="block text-sm font-medium text-slate-700">Confirm password</label>
					<div class="mt-2">
						<input type="password" name="password_confirm" id="password_confirm"
							required minlength="8" autocomplete="new-password"
							class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $password_confirm_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
							<?php echo $password_confirm_error ? 'aria-describedby="password_confirm-error"' : ''; ?>>
					</div>
					<?php if ($password_confirm_error): ?>
						<p id="password_confirm-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($password_confirm_error); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div>
				<label for="user_type" class="block text-sm font-medium text-slate-700">User type</label>
				<div class="mt-2">
					<select name="user_type" id="user_type" required
						class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm <?php echo $user_type_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
						<?php echo $user_type_error ? 'aria-describedby="user_type-error"' : ''; ?>>
						<option value="user_warehouse"<?php echo set_select('user_type', 'user_warehouse', TRUE); ?>>Warehouse user</option>
						<option value="admin"<?php echo set_select('user_type', 'admin'); ?>>Admin</option>
					</select>
				</div>
				<?php if ($user_type_error): ?>
					<p id="user_type-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($user_type_error); ?></p>
				<?php endif; ?>
			</div>

			<div id="warehouse-field">
				<label for="warehouse_id" class="block text-sm font-medium text-slate-700">Warehouse</label>
				<div class="mt-2">
					<select name="warehouse_id" id="warehouse_id"
						class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm <?php echo $warehouse_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
						<?php echo $warehouse_error ? 'aria-describedby="warehouse_id-error"' : ''; ?>>
						<option value="">Select warehouse…</option>
						<?php foreach ($warehouses as $warehouse): ?>
							<option value="<?php echo (int) $warehouse->id; ?>"<?php echo set_select('warehouse_id', (string) $warehouse->id); ?>>
								<?php echo html_escape($warehouse->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ($warehouse_error): ?>
					<p id="warehouse_id-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($warehouse_error); ?></p>
				<?php else: ?>
					<p class="mt-1 text-xs text-slate-400">Warehouse users can only access their assigned warehouse.</p>
				<?php endif; ?>
			</div>

			<div class="flex items-center gap-3 pt-2">
				<button type="submit"
					class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Save User
				</button>
				<a href="<?php echo site_url('users'); ?>"
					class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
					Cancel
				</a>
			</div>

		<?php echo form_close(); ?>
	</div>
</div>

<script>
	// Show the warehouse field only when a warehouse user is selected.
	(function () {
		'use strict';

		var typeSelect = document.getElementById('user_type');
		var warehouseField = document.getElementById('warehouse-field');

		function sync() {
			if (typeSelect.value === 'user_warehouse') {
				warehouseField.classList.remove('hidden');
			} else {
				warehouseField.classList.add('hidden');
			}
		}

		typeSelect.addEventListener('change', sync);
		sync();
	})();
</script>
