<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sales controller
 *
 * Simple sales invoice creation. Guests are redirected to the sign-in
 * page by Auth::require_login(). All database access is delegated to
 * Sale_model / Customer_model / Warehouse_model / Product_model.
 *
 * Monetary values are never trusted from the browser: the server
 * recalculates every line total, the subtotal and the grand total from
 * the products' current selling prices before saving. The invoice and
 * all of its items are written inside a single database transaction so
 * a failure can never leave a partial invoice behind.
 */
class Sales extends CI_Controller
{
	/**
	 * Number of invoices shown per page on the listing.
	 *
	 * @var int
	 */
	protected $per_page = 10;

	/**
	 * Validated invoice items collected during store(). Populated by
	 * _collect_validated_items(); NULL with an error message in
	 * $this->_items_error when validation fails.
	 *
	 * @var array|NULL
	 */
	protected $invoice_items = NULL;

	/**
	 * Human-readable reason the invoice items failed validation.
	 *
	 * @var string
	 */
	protected $items_error = '';

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('pagination');
		$this->load->model('Sale_model');
		$this->load->model('Customer_model');
		$this->load->model('Warehouse_model');
		$this->load->model('Product_model');
		// Used to deduct sold quantities from warehouse_stock on save.
		$this->load->model('Inventory_model');
	}

	/**
	 * GET /sales?q=term
	 *
	 * Paginated listing of recent invoices, newest first. The search
	 * term matches the invoice number or the customer name; it is read
	 * from the query string and preserved by the pagination links.
	 */
	public function index()
	{
		$this->auth->require_login();

		// Warehouse users only ever see their own warehouse's invoices;
		// admins see all of them. One central scope for the whole request.
		$this->Sale_model->scope_to_warehouse($this->auth->warehouse_scope());

		$search = trim((string) $this->input->get('q'));
		$search = function_exists('mb_substr') ? mb_substr($search, 0, 100) : substr($search, 0, 100);

		$total = $this->Sale_model->count_all($search);

		// Clamp the requested page so out-of-range values fall back to
		// the last page (or page 1 when there are no results).
		$total_pages = max(1, (int) ceil($total / $this->per_page));
		$page = min(max(1, (int) $this->input->get('page')), $total_pages);
		$offset = ($page - 1) * $this->per_page;

		$this->pagination->initialize(array(
			'base_url' => site_url('sales'),
			'total_rows' => $total,
			'per_page' => $this->per_page,
			'page_query_string' => TRUE,
			'reuse_query_string' => TRUE,
			'query_string_segment' => 'page',
		));

		$sales = $this->Sale_model->get_recent($this->per_page, $offset, $search);

		$this->load->view('products/layout', array(
			'page_title' => 'Sales',
			'content_view' => 'sales/index',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'sales' => $sales,
				'search' => $search,
				'pagination_links' => $this->pagination->create_links(),
				'total' => $total,
				'page' => $page,
				'start' => $total > 0 ? $offset + 1 : 0,
				'end' => min($total, $offset + $this->per_page),
			),
		));
	}

	/**
	 * GET /sales/view/{id}
	 *
	 * Displays a single invoice with all of its line items.
	 */
	public function view($id)
	{
		$this->auth->require_login();

		// Warehouse users can only read invoices from their own
		// warehouse; an invoice from any other warehouse simply looks
		// like it does not exist.
		$this->Sale_model->scope_to_warehouse($this->auth->warehouse_scope());

		$sale = $this->_find_sale($id);

		if ($sale === NULL)
		{
			$this->session->set_flashdata('error', 'Invoice not found.');
			redirect('sales');
		}

		$this->load->view('products/layout', array(
			'page_title' => 'Invoice #' . $sale->id,
			'content_view' => 'sales/view',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'sale' => $sale,
				'items' => $this->Sale_model->get_items($sale->id),
			),
		));
	}

	/**
	 * GET /sales/create
	 *
	 * Displays the New Sale page (the invoice builder). Admins pick the
	 * warehouse; warehouse users are locked to their assigned warehouse
	 * and never see a warehouse selector.
	 */
	public function create()
	{
		$this->auth->require_login();

		$is_admin = $this->auth->is_admin();

		if ( ! $is_admin && $this->auth->assigned_warehouse_id() === NULL)
		{
			$this->session->set_flashdata('error', 'You do not have permission to access this page.');
			redirect('dashboard');
		}

		$this->_render_form($is_admin);
	}

	/**
	 * GET /sales/search-products?q=term&warehouse_id=N
	 *
	 * AJAX product search for the invoice builder. Returns a small JSON
	 * list of active products matching the term (name or code), plus the
	 * current stock of each product in the given warehouse so the UI can
	 * show how much is available. This is a read-only lookup, so it uses
	 * GET like the other read filters in the application.
	 */
	public function search_products()
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'get')
		{
			redirect('sales/create');
		}

		$term = trim((string) $this->input->get('q'));
		$term = function_exists('mb_substr') ? mb_substr($term, 0, 100) : substr($term, 0, 100);

		// The warehouse used for the stock lookup. Admins may ask about
		// any warehouse; a warehouse user is always locked to their
		// assigned warehouse and a client-supplied ID is never trusted.
		if ($this->auth->is_admin())
		{
			// Optional warehouse filter for stock. Invalid/absent values
			// just mean no stock is reported.
			$warehouse_filter = $this->input->get('warehouse_id');
			$warehouse_id = (is_numeric($warehouse_filter) && (int) $warehouse_filter > 0) ? (int) $warehouse_filter : NULL;
		}
		else
		{
			$warehouse_id = $this->auth->assigned_warehouse_id();
		}

		$products = array();

		if ($term !== '')
		{
			$products = $this->Product_model->search_active_products($term, 10);

			// One batched stock lookup for all returned products instead of
			// a query per row.
			$stock = array();
			if ($warehouse_id !== NULL)
			{
				$stock = $this->Inventory_model->get_stock_for_products($warehouse_id, array_map(function ($product) {
					return (int) $product->id;
				}, $products));
			}

			$results = array();

			foreach ($products as $product)
			{
				$product_id = (int) $product->id;

				$results[] = array(
					'id' => $product_id,
					'name' => $product->name,
					'code' => $product->code,
					'price' => (float) $product->price,
					// NULL when no warehouse was selected; otherwise the
					// current quantity (0 when there is no stock record).
					'stock' => $warehouse_id !== NULL ? (isset($stock[$product_id]) ? $stock[$product_id] : 0) : NULL,
				);
			}
			$products = $results;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('products' => $products)));
	}

	/**
	 * POST /sales/store
	 *
	 * Validates the request, recalculates all monetary values
	 * server-side and saves the invoice inside a database transaction.
	 * Redirects to the new sale form on success; on failure it either
	 * re-renders the form (field validation errors, values preserved)
	 * or redirects with an error message (item / database failures).
	 */
	public function store()
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('sales/create');
		}

		$is_admin = $this->auth->is_admin();

		if ( ! $is_admin && $this->auth->assigned_warehouse_id() === NULL)
		{
			$this->session->set_flashdata('error', 'You do not have permission to access this page.');
			redirect('dashboard');
		}

		// The warehouse the invoice is recorded against. Admins choose
		// it on the form; a warehouse user's assignment is authoritative
		// and a submitted warehouse_id can never override it.
		$warehouse_id = $is_admin ? (int) $this->input->post('warehouse_id') : $this->auth->assigned_warehouse_id();

		$this->_set_validation_rules($is_admin);

		if ($this->form_validation->run() === TRUE)
		{
			$this->invoice_items = $this->_collect_validated_items();

			if ($this->invoice_items === NULL)
			{
				$this->session->set_flashdata('error', $this->items_error);
				redirect('sales/create');
			}

			$subtotal = 0.0;
			foreach ($this->invoice_items as $item)
			{
				$subtotal += (float) $item['total'];
			}
			$subtotal = round($subtotal, 2);

			$discount = $this->_discount_value();

			if ($discount > $subtotal)
			{
				$this->session->set_flashdata('error', 'The discount cannot be greater than the subtotal.');
				redirect('sales/create');
			}

			$total = round($subtotal - $discount, 2);

			// The invoice, its line items and the stock deduction are one
			// atomic operation: any failure rolls everything back so no
			// partial invoice and no partial stock movement can exist.
			$this->db->trans_begin();

			$sale_id = $this->Sale_model->create(array(
				'customer_id' => (int) $this->input->post('customer_id'),
				'warehouse_id' => $warehouse_id,
				'subtotal' => sprintf('%.2f', $subtotal),
				'discount' => sprintf('%.2f', $discount),
				'total' => sprintf('%.2f', $total),
			));

			$stock_failure = NULL;

			if ($sale_id !== FALSE)
			{
				$this->Sale_model->create_items($sale_id, $this->invoice_items);
				$stock_failure = $this->Inventory_model->deduct_stock($warehouse_id, $this->invoice_items);
			}

			if ($sale_id !== FALSE && $stock_failure === NULL && $this->db->trans_status() === TRUE)
			{
				$this->db->trans_commit();
				$this->session->set_flashdata('success', 'Invoice saved successfully.');
				redirect('sales');
			}

			$this->db->trans_rollback();

			if ($stock_failure !== NULL)
			{
				$warehouse = $this->Warehouse_model->get_by_id($warehouse_id);
				$warehouse_name = $warehouse !== NULL ? $warehouse->name : 'the selected warehouse';
				$this->session->set_flashdata('error', 'Unable to save the invoice: not enough stock for "' . $stock_failure . '" in ' . $warehouse_name . '.');
			}
			else
			{
				log_message('error', 'Sales: failed to save invoice (database error)');
				$this->session->set_flashdata('error', 'Unable to save the invoice right now. Please try again later.');
			}

			redirect('sales');
		}

		$this->_render_form($is_admin);
	}

	/**
	 * Form validation callback: the chosen customer must exist.
	 *
	 * @param	string	$customer_id
	 * @return	bool
	 */
	public function customer_valid($customer_id)
	{
		// `required` reports the empty case; never report an invalid one here.
		if ($customer_id === '' OR $customer_id === NULL)
		{
			return TRUE;
		}

		if ($this->Customer_model->get_by_id($customer_id) === NULL)
		{
			$this->form_validation->set_message('customer_valid', 'Please choose a valid customer.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Form validation callback: the chosen warehouse must exist.
	 *
	 * @param	string	$warehouse_id
	 * @return	bool
	 */
	public function warehouse_valid($warehouse_id)
	{
		// `required` reports the empty case; never report an invalid one here.
		if ($warehouse_id === '' OR $warehouse_id === NULL)
		{
			return TRUE;
		}

		if ($this->Warehouse_model->get_by_id($warehouse_id) === NULL)
		{
			$this->form_validation->set_message('warehouse_valid', 'Please choose a valid warehouse.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Define the validation rules for the invoice header fields. The
	 * line items are validated separately in _collect_validated_items()
	 * because they cannot be restored into the form once the page has
	 * been rendered.
	 *
	 * @return	void
	 */
	protected function _set_validation_rules($is_admin = TRUE)
	{
		$this->form_validation->set_rules('customer_id', 'Customer', 'required|callback_customer_valid', array(
			'required' => 'Please choose a customer.',
		));

		// The warehouse field only exists on the admin form. For a
		// warehouse user the warehouse is resolved from their account
		// and a submitted value is deliberately ignored.
		if ($is_admin)
		{
			$this->form_validation->set_rules('warehouse_id', 'Warehouse', 'required|callback_warehouse_valid', array(
				'required' => 'Please choose a warehouse.',
			));
		}

		$this->form_validation->set_rules('discount', 'Discount', 'trim|numeric|greater_than_equal_to[0]|less_than[10000000000]', array(
			'numeric' => 'The discount must be a valid number.',
			'greater_than_equal_to' => 'The discount must be 0 or greater.',
			'less_than' => 'The discount is too large.',
		));
	}

	/**
	 * Parse the submitted discount as a non-negative amount. A blank
	 * discount is treated as 0. Validation has already rejected
	 * negative values, so this only guards against non-numeric input.
	 *
	 * @return	float
	 */
	protected function _discount_value()
	{
		$discount = $this->input->post('discount');

		if ($discount === '' OR $discount === NULL)
		{
			return 0.0;
		}

		$value = (float) $discount;

		return $value < 0 ? 0.0 : round($value, 2);
	}

	/**
	 * Validate and normalize the submitted invoice items. Each product
	 * must exist and be active, and every quantity must be a whole
	 * number of at least 1. Prices and line totals are always taken
	 * from the database — submitted values are ignored. Duplicate
	 * product rows are merged into a single item with the combined
	 * quantity. Returns an array of items, or NULL with $this->items_error
	 * set when validation fails.
	 *
	 * @return	array|NULL
	 */
	protected function _collect_validated_items()
	{
		$product_ids = $this->input->post('product_id');
		$quantities = $this->input->post('quantity');

		if ( ! is_array($product_ids) OR ! is_array($quantities))
		{
			$this->items_error = 'Please add at least one product to the invoice.';
			return NULL;
		}

		$items = array();

		foreach ($product_ids as $index => $product_id)
		{
			// Ignore entirely blank rows (defensive; the UI never sends them).
			if ($product_id === '' OR $product_id === NULL)
			{
				continue;
			}

			$product = $this->Product_model->get_active_by_id($product_id);

			if ($product === NULL)
			{
				$this->items_error = 'One of the selected products is no longer available.';
				return NULL;
			}

			$quantity = isset($quantities[$index]) ? $quantities[$index] : '';
			$quantity = filter_var($quantity, FILTER_VALIDATE_INT);

			if ($quantity === FALSE OR $quantity < 1 OR $quantity > 99999)
			{
				$this->items_error = 'Product quantities must be whole numbers of at least 1.';
				return NULL;
			}

			$price = round((float) $product->price, 2);

			$items[] = array(
				'product_id' => (int) $product->id,
				'name' => $product->name,
				'quantity' => $quantity,
				'price' => $price,
				'total' => round($price * $quantity, 2),
			);
		}

		if (empty($items))
		{
			$this->items_error = 'Please add at least one product to the invoice.';
			return NULL;
		}

		// Merge duplicate products (the UI already merges them; this is a
		// server-side safety net so the totals can never be misreported).
		$merged = array();

		foreach ($items as $item)
		{
			$key = $item['product_id'];

			if (isset($merged[$key]))
			{
				$merged[$key]['quantity'] += $item['quantity'];
				$merged[$key]['total'] = round($merged[$key]['price'] * $merged[$key]['quantity'], 2);
			}
			else
			{
				$merged[$key] = $item;
			}
		}

		return array_values($merged);
	}

	/**
	 * Validate an invoice ID from the URL and fetch the invoice.
	 * Returns NULL for non-numeric IDs and missing invoices.
	 *
	 * @param	mixed	$id
	 * @return	object|NULL
	 */
	protected function _find_sale($id)
	{
		if ( ! is_numeric($id) OR (int) $id <= 0)
		{
			return NULL;
		}

		return $this->Sale_model->get_by_id((int) $id);
	}

	/**
	 * Load the shared app layout with the New Sale form.
	 *
	 * Admins get the full warehouse selector; warehouse users get their
	 * assigned warehouse as read-only information and no selector.
	 *
	 * @param	bool	$is_admin
	 * @return	void
	 */
	protected function _render_form($is_admin = TRUE)
	{
		$this->load->view('products/layout', array(
			'page_title' => 'New Sale',
			'content_view' => 'sales/create',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'customers' => $this->Customer_model->get_all(),
				'warehouses' => $is_admin ? $this->Warehouse_model->get_all() : array(),
				'assigned_warehouse' => $is_admin ? NULL : $this->auth->assigned_warehouse(),
				'is_admin' => $is_admin,
			),
		));
	}
}
