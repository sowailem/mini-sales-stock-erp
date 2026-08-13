<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inventory controller
 *
 * Simple warehouse inventory: view the quantity of each product in each
 * warehouse, filter by warehouse, and manage warehouses. All database
 * access is delegated to Warehouse_model / Inventory_model; guests are
 * redirected to the sign-in page by Auth::require_login().
 *
 * The views reuse the shared products/layout app shell (header,
 * navigation, flash messages) so this feature looks and behaves like
 * the rest of the application.
 */
class Inventory extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('Warehouse_model');
		$this->load->model('Inventory_model');
		// Reused for the product lookup on the product-quantity page.
		$this->load->model('Product_model');
	}

	/**
	 * GET /inventory
	 *
	 * Inventory listing with an optional warehouse filter
	 * (?warehouse_id=N). Invalid or unknown warehouse IDs are ignored
	 * and fall back to "all warehouses".
	 */
	public function index()
	{
		$this->auth->require_login();

		$warehouse_id = $this->_validated_warehouse_filter($this->input->get('warehouse_id'));

		$this->load->view('products/layout', array(
			'page_title' => 'Inventory',
			'content_view' => 'inventory/index',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'inventory' => $this->Inventory_model->get_inventory($warehouse_id),
				'warehouses' => $this->Warehouse_model->get_all(),
				'warehouse_id' => $warehouse_id,
			),
		));
	}

	/**
	 * GET /inventory/product/{warehouse_id}/{product_id}
	 *
	 * Displays the quantity of a single product inside a single
	 * warehouse. A missing warehouse_stock record renders as quantity 0
	 * (no record is created by viewing).
	 */
	public function product($warehouse_id, $product_id)
	{
		$this->auth->require_login();

		$warehouse = $this->_find_warehouse($warehouse_id);

		if ($warehouse === NULL)
		{
			$this->session->set_flashdata('error', 'Warehouse not found.');
			redirect('inventory');
		}

		$product = $this->_find_product($product_id);

		if ($product === NULL)
		{
			$this->session->set_flashdata('error', 'Product not found.');
			redirect('inventory');
		}

		$this->load->view('products/layout', array(
			'page_title' => $product->name . ' · ' . $warehouse->name,
			'content_view' => 'inventory/product',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'warehouse' => $warehouse,
				'product' => $product,
				'quantity' => $this->Inventory_model->get_product_quantity($warehouse->id, $product->id),
			),
		));
	}

	/**
	 * GET /inventory/warehouses
	 *
	 * Lists all warehouses.
	 */
	public function warehouses()
	{
		$this->auth->require_login();

		$this->load->view('products/layout', array(
			'page_title' => 'Warehouses',
			'content_view' => 'inventory/warehouses',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'warehouses' => $this->Warehouse_model->get_all(),
			),
		));
	}

	/**
	 * GET /inventory/warehouses/create
	 *
	 * Displays the add-warehouse form.
	 */
	public function create_warehouse()
	{
		$this->auth->require_login();

		$this->_render_warehouse_form('Add Warehouse');
	}

	/**
	 * POST /inventory/warehouses/store
	 *
	 * Validates and saves a new warehouse. Redirects to the warehouse
	 * list on success, re-renders the form (values preserved) on
	 * failure.
	 */
	public function store_warehouse()
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('inventory/warehouses');
		}

		$this->_set_warehouse_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$warehouse_id = $this->Warehouse_model->create(array(
				'name' => $this->input->post('name'),
			));

			if ($warehouse_id !== FALSE)
			{
				$this->session->set_flashdata('success', 'Warehouse created successfully.');
				redirect('inventory/warehouses');
			}

			log_message('error', 'Inventory: failed to create warehouse (database error)');
			$this->session->set_flashdata('error', 'Unable to create the warehouse right now. Please try again later.');
			redirect('inventory/warehouses');
		}

		$this->_render_warehouse_form('Add Warehouse');
	}

	/**
	 * Form validation callback: reject warehouse names that are already
	 * in use.
	 *
	 * @param	string	$name
	 * @return	bool
	 */
	public function warehouse_name_unique($name)
	{
		// `required` reports the empty case; never report a duplicate here.
		if ($name === '' OR $name === NULL)
		{
			return TRUE;
		}

		if ($this->Warehouse_model->name_exists($name))
		{
			$this->form_validation->set_message('warehouse_name_unique', 'This warehouse name is already in use.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Define the validation rules for the warehouse name.
	 *
	 * @return	void
	 */
	protected function _set_warehouse_validation_rules()
	{
		$this->form_validation->set_rules('name', 'Warehouse name', 'trim|required|max_length[100]|callback_warehouse_name_unique', array(
			'required' => 'Please enter a warehouse name.',
			'max_length' => 'The warehouse name must be at most 100 characters.',
		));
	}

	/**
	 * Load the shared app layout with the add-warehouse form.
	 *
	 * @param	string	$page_title
	 * @return	void
	 */
	protected function _render_warehouse_form($page_title)
	{
		$this->load->view('products/layout', array(
			'page_title' => $page_title,
			'content_view' => 'inventory/warehouse_form',
			'user' => $this->auth->current_user(),
			'content_data' => array(),
		));
	}

	/**
	 * Validate an optional warehouse filter from the query string.
	 * Returns NULL unless the value is a positive integer that refers
	 * to an existing warehouse, in which case it is cast to int.
	 *
	 * @param	mixed	$filter
	 * @return	int|NULL
	 */
	protected function _validated_warehouse_filter($filter)
	{
		if ( ! is_numeric($filter) OR (int) $filter <= 0)
		{
			return NULL;
		}

		$warehouse_id = (int) $filter;

		return $this->Warehouse_model->get_by_id($warehouse_id) !== NULL ? $warehouse_id : NULL;
	}

	/**
	 * Validate a warehouse ID from the URL. Returns NULL for non-numeric
	 * IDs and missing warehouses.
	 *
	 * @param	mixed	$id
	 * @return	object|NULL
	 */
	protected function _find_warehouse($id)
	{
		if ( ! is_numeric($id) OR (int) $id <= 0)
		{
			return NULL;
		}

		return $this->Warehouse_model->get_by_id((int) $id);
	}

	/**
	 * Validate a product ID from the URL. Returns NULL for non-numeric
	 * IDs and missing products.
	 *
	 * @param	mixed	$id
	 * @return	object|NULL
	 */
	protected function _find_product($id)
	{
		if ( ! is_numeric($id) OR (int) $id <= 0)
		{
			return NULL;
		}

		return $this->Product_model->get_by_id((int) $id);
	}
}
