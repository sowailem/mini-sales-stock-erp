<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Warehouse_model
 *
 * All database access for the `warehouses` table. Controllers must
 * never run raw SQL against this table; everything goes through this
 * model. The feature only ever creates and reads warehouses — there are
 * intentionally no edit/delete actions.
 */
class Warehouse_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'warehouses';

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
			$this->db->where('id', $this->warehouse_scope);
		}
		else
		{
			$this->db->where('1 = 0');
		}
	}

	/**
	 * All accessible warehouses, ordered by name.
	 *
	 * @return	array
	 */
	public function get_all()
	{
		$this->_apply_warehouse_scope();

		return $this->db
			->select('id, name')
			->from($this->table)
			->order_by('name', 'ASC')
			->get()
			->result();
	}

	/**
	 * Get a single warehouse by ID, unless the current scope excludes it.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
		$this->_apply_warehouse_scope();

		return $this->db
			->select('id, name')
			->from($this->table)
			->where('id', (int) $id)
			->get()
			->row();
	}

	/**
	 * Check whether a warehouse name is already in use.
	 *
	 * @param	string	$name
	 * @return	bool
	 */
	public function name_exists($name)
	{
		return $this->db
			->where('name', $name)
			->count_all_results($this->table) > 0;
	}

	/**
	 * Create a new warehouse.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE	New warehouse ID on success, FALSE on failure
	 */
	public function create($data)
	{
		$this->db->insert($this->table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}
}
