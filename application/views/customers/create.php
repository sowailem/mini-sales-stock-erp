<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add-customer page (content view, loaded by products/layout).
 * Shares the customer form partial with the edit page.
 */
?>
<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Customer</h1>
		<p class="mt-1 text-sm text-slate-500">Add a new customer to your business.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php $this->load->view('customers/_form', array(
			'form_action' => 'customers/store',
			'button_label' => 'Save Customer',
			'customer' => NULL,
		)); ?>
	</div>
</div>
