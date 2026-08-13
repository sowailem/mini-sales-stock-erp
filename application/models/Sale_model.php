<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sale_model
 *
 * All database access for the `sales` and `sale_items` tables. The
 * controller computes and validates every monetary value (line totals,
 * subtotal, discount, grand total) before it reaches this model — the
 * browser is never trusted. Saving an invoice is wrapped in a database
 * transaction by the controller; this model only inserts rows.
 */
class Sale_model extends CI_Model
{
	/**
	 * Table names.
	 *
	 * @var string
	 */
	protected $sales_table = 'sales';
	protected $items_table = 'sale_items';

	/**
	 * Columns selected for list/detail views. Customer and warehouse
	 * names are joined in, and `item_count` is a subquery so the list
	 * can show how many line items each invoice has.
	 *
	 * @var string
	 */
	protected $list_columns = 'sales.id, sales.customer_id, sales.warehouse_id, sales.subtotal, sales.discount, sales.total, sales.created_at, customers.name AS customer_name, warehouses.name AS warehouse_name, (SELECT COUNT(*) FROM sale_items WHERE sale_items.sale_id = sales.id) AS item_count';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get a page of recent invoices (newest first) with the customer
	 * and warehouse names joined in, optionally narrowed by a search
	 * term (invoice number or customer name).
	 *
	 * @param	int		$limit
	 * @param	int		$offset
	 * @param	string	$search
	 * @return	array
	 */
	public function get_recent($limit, $offset, $search = '')
	{
		$this->_apply_search($search);

		return $this->db
			->select($this->list_columns)
			->from($this->sales_table)
			->join('customers', 'customers.id = sales.customer_id', 'left')
			->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
			->order_by('sales.id', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()
			->result();
	}

	/**
	 * Total number of invoices matching the given search term. Used to
	 * build the pagination totals.
	 *
	 * @param	string	$search
	 * @return	int
	 */
	public function count_all($search = '')
	{
		$this->_apply_search($search);

		return (int) $this->db
			->from($this->sales_table)
			->join('customers', 'customers.id = sales.customer_id', 'left')
			->count_all_results();
	}

	/**
	 * Apply the shared listing search filter to the active query.
	 * Matches the invoice number or the customer name.
	 *
	 * @param	string	$search
	 * @return	void
	 */
	protected function _apply_search($search)
	{
		if ($search !== '')
		{
			$this->db
				->group_start()
				->like('sales.id', $search)
				->or_like('customers.name', $search)
				->group_end();
		}
	}

	/**
	 * Get a single invoice by ID with its customer and warehouse names.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
		return $this->db
			->select($this->list_columns)
			->from($this->sales_table)
			->join('customers', 'customers.id = sales.customer_id', 'left')
			->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
			->where('sales.id', (int) $id)
			->get()
			->row();
	}

	/**
	 * All line items of an invoice, with the product name and code
	 * joined in, in the order they were added.
	 *
	 * @param	int	$sale_id
	 * @return	array
	 */
	public function get_items($sale_id)
	{
		return $this->db
			->select('sale_items.product_id, sale_items.quantity, sale_items.price, sale_items.total, products.name AS product_name, products.code AS product_code')
			->from($this->items_table)
			->join('products', 'products.id = sale_items.product_id', 'left')
			->where('sale_items.sale_id', (int) $sale_id)
			->order_by('sale_items.id', 'ASC')
			->get()
			->result();
	}

	/**
	 * Insert a new sales invoice. Returns the new invoice ID, or FALSE
	 * when the insert failed.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE
	 */
	public function create($data)
	{
		$this->db->insert($this->sales_table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}

	/**
	 * Insert all line items for an invoice in one parameterized batch
	 * query. Returns TRUE only when every row was inserted.
	 *
	 * @param	int		$sale_id	Invoice ID the items belong to
	 * @param	array	$items		Items, each with product_id, quantity, price, total
	 * @return	bool
	 */
	public function create_items($sale_id, $items)
	{
		$rows = array();

		foreach ($items as $item)
		{
			$rows[] = array(
				'sale_id' => (int) $sale_id,
				'product_id' => (int) $item['product_id'],
				'quantity' => (int) $item['quantity'],
				'price' => sprintf('%.2f', (float) $item['price']),
				'total' => sprintf('%.2f', (float) $item['total']),
			);
		}

		if (empty($rows))
		{
			return FALSE;
		}

		$this->db->insert_batch($this->items_table, $rows);

		return $this->db->affected_rows() === count($rows);
	}
}
