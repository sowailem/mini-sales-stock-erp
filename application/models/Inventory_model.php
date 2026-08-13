<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inventory_model
 *
 * Read-only inventory queries over the `warehouse_stock` table, joined
 * with `warehouses` and `products`. The inventory feature never writes
 * stock — quantities are only ever read.
 */
class Inventory_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $stock_table = 'warehouse_stock';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Inventory rows (warehouse, product, quantity), optionally limited
	 * to a single warehouse. Only valid warehouse/product relationships
	 * are returned — the inner joins drop orphaned rows.
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
}
