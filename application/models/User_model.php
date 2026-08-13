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
	protected $public_columns = 'id, username, user_type, warehouse_id, is_active, created_at, updated_at';

	/**
	 * The only user types the application knows.
	 *
	 * @var array
	 */
	protected $valid_user_types = array('admin', 'user_warehouse');

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
	 * Validate a user-type / warehouse assignment pair. Only the two
	 * known user types are allowed: admins must have no warehouse and
	 * warehouse users must carry a positive warehouse ID. Warehouse
	 * existence is additionally enforced by the users.warehouse_id
	 * foreign key in the database.
	 *
	 * @param	string	$user_type
	 * @param	mixed	$warehouse_id
	 * @return	bool
	 */
	public function validate_assignment($user_type, $warehouse_id)
	{
		if ( ! in_array($user_type, $this->valid_user_types, TRUE))
		{
			return FALSE;
		}

		if ($user_type === 'admin')
		{
			return $warehouse_id === NULL OR $warehouse_id === '';
		}

		return is_numeric($warehouse_id) && (int) $warehouse_id > 0;
	}

	/**
	 * Create a new user.
	 *
	 * Expects $data to already contain a hashed password. The permission
	 * fields are normalized here so a caller can never create an invalid
	 * user type or an admin with a warehouse: an absent/unknown type
	 * falls back to 'admin' (the open-signup default), admins always get
	 * warehouse_id = NULL, and a warehouse user keeps a warehouse ID only
	 * when it is a positive number.
	 *
	 * @param	array	$data	Associative array of column => value
	 * @return	int|FALSE	New user ID on success, FALSE on failure
	 */
	public function create($data)
	{
		$user_type = isset($data['user_type']) ? (string) $data['user_type'] : 'admin';

		if ( ! in_array($user_type, $this->valid_user_types, TRUE))
		{
			$user_type = 'admin';
		}

		$data['user_type'] = $user_type;

		if ($user_type === 'admin')
		{
			$data['warehouse_id'] = NULL;
		}
		else
		{
			$warehouse_id = isset($data['warehouse_id']) ? $data['warehouse_id'] : NULL;
			$data['warehouse_id'] = (is_numeric($warehouse_id) && (int) $warehouse_id > 0) ? (int) $warehouse_id : NULL;
		}

		$this->db->insert($this->table, $data);

		return $this->db->affected_rows() === 1
			? (int) $this->db->insert_id()
			: FALSE;
	}
}
