<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Edit-product page (content view, loaded by products/layout).
 * Shares the product form partial with the create page.
 */
?>
<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Product</h1>
		<p class="mt-1 text-sm text-slate-500">Update the details for <span class="font-medium text-slate-700"><?php echo html_escape($product->name); ?></span>.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php $this->load->view('products/_form', array(
			'form_action' => 'products/update/'.$product->id,
			'button_label' => 'Update Product',
			'product' => $product,
			'categories' => $categories,
		)); ?>
	</div>
</div>
