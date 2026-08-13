<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared product form (create and edit).
 *
 * Expects:
 *   $form_action  — URI for form_open() (e.g. 'products/store')
 *   $button_label — submit button text
 *   $product      — product being edited (object) or NULL when creating
 *   $categories   — active categories for the dropdown
 *
 * Values come from set_value() so submitted input is preserved when
 * validation fails; the product row is only the fallback default.
 */

$product = isset($product) ? $product : NULL;

$name_error = form_error('name', '', '');
$code_error = form_error('code', '', '');
$category_error = form_error('category_id', '', '');
$price_error = form_error('price', '', '');
?>

<?php echo form_open($form_action, array('class' => 'space-y-6', 'novalidate' => 'novalidate')); ?>

	<div>
		<label for="name" class="block text-sm font-medium text-slate-700">Product name</label>
		<div class="mt-2">
			<input type="text" name="name" id="name" value="<?php echo set_value('name', $product ? $product->name : ''); ?>"
				required maxlength="150"
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $name_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $name_error ? 'aria-describedby="name-error"' : ''; ?>>
		</div>
		<?php if ($name_error): ?>
			<p id="name-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($name_error); ?></p>
		<?php endif; ?>
	</div>

	<div>
		<label for="code" class="block text-sm font-medium text-slate-700">Product code</label>
		<div class="mt-2">
			<input type="text" name="code" id="code" value="<?php echo set_value('code', $product ? $product->code : ''); ?>"
				required maxlength="50" placeholder="e.g. PRD-0001"
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $code_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $code_error ? 'aria-describedby="code-error"' : ''; ?>>
		</div>
		<?php if ($code_error): ?>
			<p id="code-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($code_error); ?></p>
		<?php endif; ?>
	</div>

	<div>
		<label for="category_id" class="block text-sm font-medium text-slate-700">Category</label>
		<div class="mt-2">
			<select name="category_id" id="category_id" required
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm <?php echo $category_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $category_error ? 'aria-describedby="category_id-error"' : ''; ?>>
				<option value="">Select a category…</option>
				<?php foreach ($categories as $category): ?>
					<option value="<?php echo (int) $category->id; ?>"<?php echo set_select('category_id', (string) $category->id, $product !== NULL && (int) $product->category_id === (int) $category->id); ?>>
						<?php echo html_escape($category->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php if ($category_error): ?>
			<p id="category_id-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($category_error); ?></p>
		<?php endif; ?>
	</div>

	<div>
		<label for="price" class="block text-sm font-medium text-slate-700">Price</label>
		<div class="mt-2">
			<input type="number" name="price" id="price" value="<?php echo set_value('price', $product ? $product->price : ''); ?>"
				required step="0.01" min="0" max="9999999999.99" inputmode="decimal" placeholder="0.00"
				class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm <?php echo $price_error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-300 focus:ring-indigo-600'; ?>"
				<?php echo $price_error ? 'aria-describedby="price-error"' : ''; ?>>
		</div>
		<?php if ($price_error): ?>
			<p id="price-error" class="mt-2 text-sm text-red-600"><?php echo html_escape($price_error); ?></p>
		<?php endif; ?>
	</div>

	<div class="flex items-center gap-3 pt-2">
		<button type="submit"
			class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			<?php echo html_escape($button_label); ?>
		</button>
		<a href="<?php echo site_url('products'); ?>"
			class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			Cancel
		</a>
	</div>

<?php echo form_close(); ?>
