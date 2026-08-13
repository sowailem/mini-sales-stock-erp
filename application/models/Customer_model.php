<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer_model
 *
 * All database access for the `customers` table. Controllers must never
 * run raw SQL against this table; everything goes through this model.
 * The table is intentionally minimal — only a name and an optional
 * phone number. There are no delete actions.
 */
class Customer_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'customers';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * All customers, ordered by name.
	 *
	 * @return	array
	 */
	public function get_all()
	{
		return $this->db
			->select('id, name, phone')
			->from($this->table)
			->order_by('name', 'ASC')
			->get()
			->result();
	}

	/**
	 * Get a single customer by ID.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
		return $this->db
			->select('id, name, phone')
			->from($this->table)
			->where('id', (int) $id)
			->get()
			->row();
	}

	/**
	 * Create a new customer.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE	New customer ID on success, FALSE on failure
	 */
	public function create($data)
	{
		$this->db->insert($this->table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}

	/**
	 * Update an existing customer.
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
}
