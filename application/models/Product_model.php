<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product_model
 *
 * All database access for the `products` and `categories` tables.
 * Controllers must never run raw SQL against these tables; everything
 * goes through this model. Products are deactivated (is_active = 0),
 * never physically deleted.
 */
class Product_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'products';

	/**
	 * Columns selected for list views. `categories.name` is joined in
	 * by the listing query so rows carry a `category_name` property.
	 *
	 * @var string
	 */
	protected $list_columns = 'products.id, products.name, products.code, products.category_id, products.price, products.is_active, categories.name AS category_name';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get a page of products, optionally narrowed by a search term
	 * (name or code) and/or a category. Results are ordered newest first.
	 *
	 * @param	int		$limit			Number of rows to return
	 * @param	int		$offset			Row offset for pagination
	 * @param	string	$search			Search term (name or code)
	 * @param	int|NULL	$category_id	Optional category filter
	 * @return	array
	 */
	public function get_products($limit, $offset, $search = '', $category_id = NULL)
	{
		$this->db
			->select($this->list_columns)
			->from($this->table)
			->join('categories', 'categories.id = products.category_id', 'left');

		$this->_apply_filters($search, $category_id);

		return $this->db
			->order_by('products.id', 'DESC')
			->limit($limit, $offset)
			->get()
			->result();
	}

	/**
	 * Count products matching the given search term and/or category.
	 * Used to build the pagination totals.
	 *
	 * @param	string		$search			Search term (name or code)
	 * @param	int|NULL	$category_id	Optional category filter
	 * @return	int
	 */
	public function count_products($search = '', $category_id = NULL)
	{
		$this->_apply_filters($search, $category_id);

		return (int) $this->db->count_all_results($this->table);
	}

	/**
	 * Get a single product by ID, including its category name.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
		return $this->db
			->select($this->list_columns)
			->from($this->table)
			->join('categories', 'categories.id = products.category_id', 'left')
			->where('products.id', (int) $id)
			->get()
			->row();
	}

	/**
	 * Get a single active product by ID. Only used by the sales feature
	 * so an invoice can never reference a missing or disabled product.
	 *
	 * @param	int|string	$id
	 * @return	object|NULL
	 */
	public function get_active_by_id($id)
	{
		return $this->db
			->select('id, name, code, price')
			->from($this->table)
			->where('id', (int) $id)
			->where('is_active', 1)
			->get()
			->row();
	}

	/**
	 * Search active products by name or code for the sales AJAX product
	 * lookup. LIKE is case-insensitive under the table's
	 * utf8mb4_unicode_ci collation. Only a small page of results is
	 * returned so the full catalog is never sent to the browser.
	 *
	 * @param	string	$term
	 * @param	int		$limit	Maximum number of results
	 * @return	array
	 */
	public function search_active_products($term, $limit = 10)
	{
		return $this->db
			->select('id, name, code, price')
			->from($this->table)
			->group_start()
			->like('products.name', $term)
			->or_like('products.code', $term)
			->group_end()
			->where('products.is_active', 1)
			->order_by('products.name', 'ASC')
			->limit((int) $limit)
			->get()
			->result();
	}

	/**
	 * Check whether a product code is already in use, optionally
	 * excluding a given product (used when editing).
	 *
	 * @param	string	$code
	 * @param	int|NULL	$exclude_id	Product ID to ignore
	 * @return	bool
	 */
	public function code_exists($code, $exclude_id = NULL)
	{
		$this->db->where('code', $code);

		if ($exclude_id !== NULL)
		{
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results($this->table) > 0;
	}

	/**
	 * Create a new product.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE	New product ID on success, FALSE on failure
	 */
	public function create($data)
	{
		$this->db->insert($this->table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}

	/**
	 * Update an existing product.
	 *
	 * @param	int		$id
	 * @param	array	$data	Associative array of column => value
	 * @return	bool
	 */
	public function update($id, $data)
	{
		$this->db->where('id', (int) $id);

		return $this->db->update($this->table, $data);
	}

	/**
	 * Deactivate a product (soft delete). The row is never removed.
	 *
	 * @param	int	$id
	 * @return	bool
	 */
	public function disable($id)
	{
		$this->db->where('id', (int) $id);

		return $this->db->update($this->table, array('is_active' => 0));
	}

	/**
	 * Check whether a category exists and is active. Used by validation
	 * so products can never reference an invalid category.
	 *
	 * @param	int|string	$id
	 * @return	bool
	 */
	public function category_exists($id)
	{
		return $this->db
			->where('id', (int) $id)
			->where('is_active', 1)
			->count_all_results('categories') > 0;
	}

	/**
	 * All active categories, ordered by name. Used for the category
	 * filter dropdown and the product form.
	 *
	 * @return	array
	 */
	public function get_active_categories()
	{
		return $this->db
			->select('id, name')
			->where('is_active', 1)
			->order_by('name', 'ASC')
			->get('categories')
			->result();
	}

	/**
	 * Apply the shared search/category filters to the active query.
	 * Search matches the product name OR the product code.
	 *
	 * @param	string		$search
	 * @param	int|NULL	$category_id
	 * @return	void
	 */
	protected function _apply_filters($search, $category_id)
	{
		if ($search !== '')
		{
			$this->db
				->group_start()
				->like('products.name', $search)
				->or_like('products.code', $search)
				->group_end();
		}

		if ($category_id !== NULL)
		{
			$this->db->where('products.category_id', (int) $category_id);
		}
	}
}
