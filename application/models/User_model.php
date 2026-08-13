<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_model
 *
 * All database access for the `users` table. Controllers must never
 * run raw SQL against this table; everything goes through this model.
 */
class User_model extends CI_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * Columns safe to expose outside of the authentication flow.
	 * The password hash is intentionally excluded.
	 *
	 * @var string
	 */
	protected $public_columns = 'id, username, is_active, created_at, updated_at';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Find a user by username.
	 *
	 * Returns the full row including the password hash. This method is only
	 * used internally during sign-in to verify credentials; the result must
	 * never be passed to a view.
	 *
	 * @param	string	$username
	 * @return	object|NULL
	 */
	public function find_by_username($username)
	{
		return $this->db
			->where('username', $username)
			->get($this->table)
			->row();
	}

	/**
	 * Get a user by ID, excluding the password hash.
	 *
	 * @param	int	$id
	 * @return	object|NULL
	 */
	public function get_by_id($id)
	{
		return $this->db
			->select($this->public_columns)
			->where('id', (int) $id)
			->get($this->table)
			->row();
	}

	/**
	 * Check whether a username is already taken.
	 *
	 * @param	string	$username
	 * @return	bool
	 */
	public function username_exists($username)
	{
		return $this->db
			->where('username', $username)
			->count_all_results($this->table) > 0;
	}

	/**
	 * Create a new user.
	 *
	 * Expects $data to already contain a hashed password.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE	New user ID on success, FALSE on failure
	 */
	public function create($data)
	{
		$this->db->insert($this->table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}
}
