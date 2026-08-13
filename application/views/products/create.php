<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add-product page (content view, loaded by products/layout).
 * Shares the product form partial with the edit page.
 */
?>
<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Product</h1>
		<p class="mt-1 text-sm text-slate-500">Add a new product to your catalog.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php $this->load->view('products/_form', array(
			'form_action' => 'products/store',
			'button_label' => 'Save Product',
			'product' => NULL,
			'categories' => $categories,
		)); ?>
	</div>
</div>
