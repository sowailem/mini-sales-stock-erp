<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inventory_model
 *
 * Inventory queries over the `warehouse_stock` table, joined with
 * `warehouses` and `products`. The inventory feature only reads
 * quantities; the sales feature deducts stock here when an invoice
 * is saved.
 */
class Inventory_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $stock_table = 'warehouse_stock';

	/**
	 * Warehouse scope for read queries. NULL = all warehouses; a
	 * positive integer = only that warehouse; 0 = nothing. Set once per
	 * request by the controller from Auth_lib::warehouse_scope() so the
	 * authorization condition lives in one place instead of in every
	 * method.
	 *
	 * @var int|NULL
	 */
	protected $warehouse_scope = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Restrict read queries to a single warehouse (or to nothing when
	 * the scope is 0). Pass NULL to lift the restriction.
	 *
	 * @param	int|NULL	$warehouse_id
	 * @return	$this
	 */
	public function scope_to_warehouse($warehouse_id)
	{
		$this->warehouse_scope = $warehouse_id === NULL ? NULL : (int) $warehouse_id;

		return $this;
	}

	/**
	 * Apply the configured warehouse scope to the active query.
	 *
	 * @return	void
	 */
	protected function _apply_warehouse_scope()
	{
		if ($this->warehouse_scope === NULL)
		{
			return;
		}

		if ($this->warehouse_scope > 0)
		{
			$this->db->where('warehouse_stock.warehouse_id', $this->warehouse_scope);
		}
		else
		{
			$this->db->where('1 = 0');
		}
	}

	/**
	 * Inventory rows (warehouse, product, quantity), optionally limited
	 * to a single warehouse and always limited by the configured scope.
	 * Only valid warehouse/product relationships are returned — the
	 * inner joins drop orphaned rows.
	 *
	 * @param	int|NULL	$warehouse_id	Optional warehouse filter
	 * @return	array
	 */
	public function get_inventory($warehouse_id = NULL)
	{
		$this->db
			->select('warehouses.id AS warehouse_id, warehouses.name AS warehouse_name, products.id AS product_id, products.name AS product_name, warehouse_stock.quantity')
			->from($this->stock_table)
			->join('warehouses', 'warehouses.id = warehouse_stock.warehouse_id', 'inner')
			->join('products', 'products.id = warehouse_stock.product_id', 'inner');

		if ($warehouse_id !== NULL)
		{
			$this->db->where('warehouse_stock.warehouse_id', (int) $warehouse_id);
		}

		$this->_apply_warehouse_scope();

		return $this->db
			->order_by('warehouses.name', 'ASC')
			->order_by('products.name', 'ASC')
			->get()
			->result();
	}

	/**
	 * Current quantity of a product in a warehouse. Returns 0 when no
	 * stock record exists for the pair — the record itself is never
	 * created by reading.
	 *
	 * @param	int	$warehouse_id
	 * @param	int	$product_id
	 * @return	int
	 */
	public function get_product_quantity($warehouse_id, $product_id)
	{
		$row = $this->db
			->select('quantity')
			->from($this->stock_table)
			->where('warehouse_id', (int) $warehouse_id)
			->where('product_id', (int) $product_id)
			->get()
			->row();

		return $row !== NULL ? (int) $row->quantity : 0;
	}

	/**
	 * Current stock quantities for a set of products in one warehouse.
	 * Returns an array keyed by product ID; products without a stock
	 * record are simply absent from the map (the caller defaults them
	 * to 0).
	 *
	 * @param	int		$warehouse_id
	 * @param	array	$product_ids	List of product IDs (int-cast)
	 * @return	array
	 */
	public function get_stock_for_products($warehouse_id, $product_ids)
	{
		$stock = array();

		if (empty($product_ids))
		{
			return $stock;
		}

		$rows = $this->db
			->select('product_id, quantity')
			->from($this->stock_table)
			->where('warehouse_id', (int) $warehouse_id)
			->where_in('product_id', $product_ids)
			->get()
			->result();

		foreach ($rows as $row)
		{
			$stock[(int) $row->product_id] = (int) $row->quantity;
		}

		return $stock;
	}

	/**
	 * Deduct the sold quantities of an invoice from a warehouse's
	 * stock. Each line is decremented only while enough stock is left;
	 * a missing stock record or insufficient quantity fails that line
	 * and its product name is returned so the caller can reject and
	 * roll back the whole invoice. Stock can therefore never go
	 * negative.
	 *
	 * Note: the caller must run this inside a database transaction so
	 * a failed deduction rolls back the invoice too.
	 *
	 * @param	int		$warehouse_id
	 * @param	array	$items	Invoice items, each with product_id, quantity and name
	 * @return	string|NULL	Product name that could not be deducted, or NULL on success
	 */
	public function deduct_stock($warehouse_id, $items)
	{
		foreach ($items as $item)
		{
			$this->db
				->where('warehouse_id', (int) $warehouse_id)
				->where('product_id', (int) $item['product_id'])
				->where('quantity >=', (int) $item['quantity'])
				->set('quantity', 'quantity - ' . (int) $item['quantity'], FALSE)
				->update($this->stock_table);

			// 0 affected rows means the pair has no record or not enough
			// stock — either way the line cannot be fulfilled.
			if ($this->db->affected_rows() !== 1)
			{
				return isset($item['name']) ? (string) $item['name'] : (string) $item['product_id'];
			}
		}

		return NULL;
	}
}
