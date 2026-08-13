<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customers controller
 *
 * Handles listing, creating and editing customers. All database access
 * is delegated to Customer_model; guests are redirected to the sign-in
 * page by Auth::require_login().
 */
class Customers extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('Customer_model');
	}

	/**
	 * GET /customers
	 *
	 * Lists all customers.
	 */
	public function index()
	{
		$this->auth->require_login();

		$this->load->view('products/layout', array(
			'page_title' => 'Customers',
			'content_view' => 'customers/index',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'customers' => $this->Customer_model->get_all(),
			),
		));
	}

	/**
	 * GET /customers/create
	 *
	 * Displays the add-customer form.
	 */
	public function create()
	{
		$this->auth->require_login();

		$this->_render_form('Add Customer', 'customers/create', NULL);
	}

	/**
	 * POST /customers/store
	 *
	 * Validates and saves a new customer. Redirects to the list on
	 * success, re-renders the form (values preserved) on failure.
	 */
	public function store()
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('customers');
		}

		$this->_set_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$customer_id = $this->Customer_model->create($this->_customer_data());

			if ($customer_id !== FALSE)
			{
				$this->session->set_flashdata('success', 'Customer created successfully.');
				redirect('customers');
			}

			log_message('error', 'Customers: failed to create customer (database error)');
			$this->session->set_flashdata('error', 'Unable to create the customer right now. Please try again later.');
			redirect('customers');
		}

		$this->_render_form('Add Customer', 'customers/create', NULL);
	}

	/**
	 * GET /customers/edit/{id}
	 *
	 * Displays the edit form populated with the existing customer.
	 */
	public function edit($id)
	{
		$this->auth->require_login();

		$customer = $this->_find_customer($id);

		if ($customer === NULL)
		{
			$this->session->set_flashdata('error', 'Customer not found.');
			redirect('customers');
		}

		$this->_render_form('Edit Customer', 'customers/edit', $customer);
	}

	/**
	 * POST /customers/update/{id}
	 *
	 * Validates and updates an existing customer. Redirects to the list
	 * on success, re-renders the form (submitted values preserved) on
	 * failure.
	 */
	public function update($id)
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('customers');
		}

		$customer = $this->_find_customer($id);

		if ($customer === NULL)
		{
			$this->session->set_flashdata('error', 'Customer not found.');
			redirect('customers');
		}

		$this->_set_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$updated = $this->Customer_model->update($customer->id, $this->_customer_data());

			if ($updated)
			{
				$this->session->set_flashdata('success', 'Customer updated successfully.');
				redirect('customers');
			}

			log_message('error', 'Customers: failed to update customer #' . $customer->id . ' (database error)');
			$this->session->set_flashdata('error', 'Unable to update the customer right now. Please try again later.');
			redirect('customers');
		}

		$this->_render_form('Edit Customer', 'customers/edit', $customer);
	}

	/**
	 * Define the validation rules shared by store() and update().
	 *
	 * @return	void
	 */
	protected function _set_validation_rules()
	{
		$this->form_validation->set_rules('name', 'Customer name', 'trim|required|max_length[150]', array(
			'required' => 'Please enter a customer name.',
			'max_length' => 'The customer name must be at most 150 characters.',
		));
		$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]', array(
			'max_length' => 'The phone number must be at most 30 characters.',
		));
	}

	/**
	 * Build the column => value array used by create() and update().
	 * A blank phone is stored as NULL since the column is nullable.
	 *
	 * @return	array
	 */
	protected function _customer_data()
	{
		$phone = trim((string) $this->input->post('phone'));

		return array(
			'name' => $this->input->post('name'),
			'phone' => $phone !== '' ? $phone : NULL,
		);
	}

	/**
	 * Load the shared app layout with the given create/edit content view.
	 *
	 * @param	string			$page_title
	 * @param	string			$content_view
	 * @param	object|NULL		$customer	Customer being edited, or NULL when creating
	 * @return	void
	 */
	protected function _render_form($page_title, $content_view, $customer)
	{
		$this->load->view('products/layout', array(
			'page_title' => $page_title,
			'content_view' => $content_view,
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'customer' => $customer,
			),
		));
	}

	/**
	 * Validate a customer ID from the URL and fetch the customer.
	 * Returns NULL for non-numeric IDs and missing customers.
	 *
	 * @param	mixed	$id
	 * @return	object|NULL
	 */
	protected function _find_customer($id)
	{
		if ( ! is_numeric($id) OR (int) $id <= 0)
		{
			return NULL;
		}

		return $this->Customer_model->get_by_id((int) $id);
	}
}
