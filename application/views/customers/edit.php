<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Edit-customer page (content view, loaded by products/layout).
 * Shares the customer form partial with the create page.
 */
?>
<div class="max-w-2xl">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Customer</h1>
		<p class="mt-1 text-sm text-slate-500">Update the details for <span class="font-medium text-slate-700"><?php echo html_escape($customer->name); ?></span>.</p>
	</div>

	<div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
		<?php $this->load->view('customers/_form', array(
			'form_action' => 'customers/update/'.$customer->id,
			'button_label' => 'Update Customer',
			'customer' => $customer,
		)); ?>
	</div>
</div>
