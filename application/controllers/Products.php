<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Products controller
 *
 * Handles listing, searching, filtering, creating, editing and disabling
 * products. All database access is delegated to Product_model; guests are
 * redirected to the sign-in page by Auth::require_login().
 */
class Products extends CI_Controller
{
	/**
	 * Number of products shown per page.
	 *
	 * @var int
	 */
	protected $per_page = 10;

	/**
	 * Product ID currently being edited, used by the code-uniqueness
	 * validation callback so a product's own code never conflicts with
	 * itself. NULL when creating.
	 *
	 * @var int|NULL
	 */
	protected $editing_product_id = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('pagination');
		$this->load->model('Product_model');
	}

	/**
	 * GET /products
	 *
	 * Paginated product listing with search (name/code) and category
	 * filters. The filters are read from the query string and preserved
	 * by the pagination links.
	 */
	public function index()
	{
		$this->auth->require_login();

		$search = trim((string) $this->input->get('q'));
		$search = function_exists('mb_substr') ? mb_substr($search, 0, 100) : substr($search, 0, 100);
		$category = $this->input->get('category');
		$category_id = (is_numeric($category) && (int) $category > 0) ? (int) $category : NULL;

		$total = $this->Product_model->count_products($search, $category_id);

		// Clamp the requested page so out-of-range values fall back to
		// the last page (or page 1 when there are no results).
		$total_pages = max(1, (int) ceil($total / $this->per_page));
		$page = min(max(1, (int) $this->input->get('page')), $total_pages);
		$offset = ($page - 1) * $this->per_page;

		$this->pagination->initialize(array(
			'base_url' => site_url('products'),
			'total_rows' => $total,
			'per_page' => $this->per_page,
			'page_query_string' => TRUE,
			'reuse_query_string' => TRUE,
			'query_string_segment' => 'page',
		));

		$products = $this->Product_model->get_products($this->per_page, $offset, $search, $category_id);

		$this->load->view('products/layout', array(
			'page_title' => 'Products',
			'content_view' => 'products/index',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'products' => $products,
				'categories' => $this->Product_model->get_active_categories(),
				'pagination_links' => $this->pagination->create_links(),
				'search' => $search,
				'category_id' => $category_id,
				'total' => $total,
				'page' => $page,
				'start' => $total > 0 ? $offset + 1 : 0,
				'end' => min($total, $offset + $this->per_page),
			),
		));
	}

	/**
	 * GET /products/create
	 *
	 * Displays the add-product form.
	 */
	public function create()
	{
		$this->auth->require_login();

		$this->_render_form('Add Product', 'products/create', NULL);
	}

	/**
	 * POST /products/store
	 *
	 * Validates and saves a new product. Redirects to the list on
	 * success, re-renders the form (values preserved) on failure.
	 */
	public function store()
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('products');
		}

		$this->_set_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$product_id = $this->Product_model->create(array(
				'name' => $this->input->post('name'),
				'code' => $this->input->post('code'),
				'category_id' => (int) $this->input->post('category_id'),
				'price' => $this->input->post('price'),
				'is_active' => 1,
			));

			if ($product_id !== FALSE)
			{
				$this->session->set_flashdata('success', 'Product created successfully.');
				redirect('products');
			}

			log_message('error', 'Products: failed to create product (database error)');
			$this->session->set_flashdata('error', 'Unable to create the product right now. Please try again later.');
			redirect('products');
		}

		$this->_render_form('Add Product', 'products/create', NULL);
	}

	/**
	 * GET /products/edit/{id}
	 *
	 * Displays the edit form populated with the existing product.
	 */
	public function edit($id)
	{
		$this->auth->require_login();

		$product = $this->_find_product($id);

		if ($product === NULL)
		{
			$this->session->set_flashdata('error', 'Product not found.');
			redirect('products');
		}

		$this->_render_form('Edit Product', 'products/edit', $product);
	}

	/**
	 * POST /products/update/{id}
	 *
	 * Validates and updates an existing product. Redirects to the list
	 * on success, re-renders the form (submitted values preserved) on
	 * failure.
	 */
	public function update($id)
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('products');
		}

		$product = $this->_find_product($id);

		if ($product === NULL)
		{
			$this->session->set_flashdata('error', 'Product not found.');
			redirect('products');
		}

		$this->editing_product_id = (int) $product->id;
		$this->_set_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$updated = $this->Product_model->update($product->id, array(
				'name' => $this->input->post('name'),
				'code' => $this->input->post('code'),
				'category_id' => (int) $this->input->post('category_id'),
				'price' => $this->input->post('price'),
			));

			if ($updated)
			{
				$this->session->set_flashdata('success', 'Product updated successfully.');
				redirect('products');
			}

			log_message('error', 'Products: failed to update product #' . $product->id . ' (database error)');
			$this->session->set_flashdata('error', 'Unable to update the product right now. Please try again later.');
			redirect('products');
		}

		$this->_render_form('Edit Product', 'products/edit', $product);
	}

	/**
	 * POST /products/disable/{id}
	 *
	 * Deactivates a product (soft delete — the row is never removed).
	 * Only accepts POST with CSRF protection; GET requests are rejected.
	 */
	public function disable($id)
	{
		$this->auth->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('products');
		}

		$product = $this->_find_product($id);

		if ($product === NULL)
		{
			$this->session->set_flashdata('error', 'Product not found.');
			redirect('products');
		}

		if ((int) $product->is_active === 0)
		{
			$this->session->set_flashdata('error', 'This product is already disabled.');
			redirect('products');
		}

		if ($this->Product_model->disable($product->id))
		{
			$this->session->set_flashdata('success', 'Product disabled successfully.');
		}
		else
		{
			log_message('error', 'Products: failed to disable product #' . $product->id . ' (database error)');
			$this->session->set_flashdata('error', 'Unable to disable the product right now. Please try again later.');
		}

		redirect('products');
	}

	/**
	 * Form validation callback: reject product codes that are already in
	 * use, ignoring the product currently being edited (if any).
	 *
	 * @param	string	$code
	 * @return	bool
	 */
	public function code_unique($code)
	{
		// `required` reports the empty case; never report a duplicate here.
		if ($code === '' OR $code === NULL)
		{
			return TRUE;
		}

		if ($this->Product_model->code_exists($code, $this->editing_product_id))
		{
			$this->form_validation->set_message('code_unique', 'This product code is already in use.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Form validation callback: the chosen category must exist and be
	 * active.
	 *
	 * @param	string	$category_id
	 * @return	bool
	 */
	public function category_valid($category_id)
	{
		// `required` reports the empty case; never report an invalid one here.
		if ($category_id === '' OR $category_id === NULL)
		{
			return TRUE;
		}

		if ( ! $this->Product_model->category_exists($category_id))
		{
			$this->form_validation->set_message('category_valid', 'Please choose a valid category.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Define the validation rules shared by store() and update().
	 *
	 * @return	void
	 */
	protected function _set_validation_rules()
	{
		$this->form_validation->set_rules('name', 'Product name', 'trim|required|max_length[150]', array(
			'required' => 'Please enter a product name.',
			'max_length' => 'The product name must be at most 150 characters.',
		));
		$this->form_validation->set_rules('code', 'Product code', 'trim|required|max_length[50]|callback_code_unique', array(
			'required' => 'Please enter a product code.',
			'max_length' => 'The product code must be at most 50 characters.',
		));
		$this->form_validation->set_rules('category_id', 'Category', 'required|callback_category_valid', array(
			'required' => 'Please choose a category.',
		));
		$this->form_validation->set_rules('price', 'Price', 'trim|required|decimal|greater_than_equal_to[0]|less_than[10000000000]', array(
			'required' => 'Please enter a price.',
			'decimal' => 'The price must be a valid number.',
			'greater_than_equal_to' => 'The price must be 0 or greater.',
			'less_than' => 'The price must be less than 10,000,000,000.',
		));
	}

	/**
	 * Load the products layout with the given create/edit content view.
	 *
	 * @param	string		$page_title
	 * @param	string		$content_view
	 * @param	object|NULL	$product	Product being edited, or NULL when creating
	 * @return	void
	 */
	protected function _render_form($page_title, $content_view, $product)
	{
		$this->load->view('products/layout', array(
			'page_title' => $page_title,
			'content_view' => $content_view,
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'product' => $product,
				'categories' => $this->Product_model->get_active_categories(),
			),
		));
	}

	/**
	 * Validate a product ID from the URL and fetch the product. Returns
	 * NULL for non-numeric IDs and missing products.
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
