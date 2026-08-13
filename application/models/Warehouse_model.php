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

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * All warehouses, ordered by name.
	 *
	 * @return	array
	 */
	public function get_all()
	{
		return $this->db
			->select('id, name')
			->from($this->table)
			->order_by('name', 'ASC')
			->get()
			->result();
	}

	/**
	 * Get a single warehouse by ID.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
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
