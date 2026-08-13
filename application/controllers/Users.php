<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users controller
 *
 * Admin-only user management. Lists existing accounts and creates new
 * ones — the two supported types are 'admin' (no warehouse) and
 * 'user_warehouse' (exactly one existing warehouse). All routes are
 * guarded by Auth::require_admin(); warehouse users are always
 * redirected away with a generic error. All database access is
 * delegated to User_model / Warehouse_model.
 */
class Users extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('User_model');
		$this->load->model('Warehouse_model');
	}

	/**
	 * GET /users
	 *
	 * Lists all user accounts with their type, assigned warehouse and
	 * status.
	 */
	public function index()
	{
		$this->auth->require_admin();

		$this->load->view('products/layout', array(
			'page_title' => 'Users',
			'content_view' => 'users/index',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'users' => $this->User_model->get_all(),
			),
		));
	}

	/**
	 * GET /users/create
	 *
	 * Displays the add-user form.
	 */
	public function create()
	{
		$this->auth->require_admin();

		$this->_render_form();
	}

	/**
	 * POST /users/store
	 *
	 * Validates and creates a new user. Redirects to the user list on
	 * success, re-renders the form (values preserved) on failure.
	 */
	public function store()
	{
		$this->auth->require_admin();

		if ($this->input->method() !== 'post')
		{
			redirect('users');
		}

		$this->_set_validation_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$username = $this->input->post('username');
			$user_type = $this->input->post('user_type');

			// The warehouse field is only meaningful for warehouse
			// users; User_model::create() enforces NULL for admins.
			$warehouse_id = $user_type === 'user_warehouse' ? (int) $this->input->post('warehouse_id') : NULL;

			$user_id = $this->User_model->create(array(
				'username' => $username,
				// Never store plain-text passwords.
				'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
				'user_type' => $user_type,
				'warehouse_id' => $warehouse_id,
				'is_active' => 1,
			));

			if ($user_id !== FALSE)
			{
				$this->session->set_flashdata('success', 'User created successfully.');
				redirect('users');
			}

			log_message('error', 'Users: failed to create user "' . $username . '" (database error)');
			$this->session->set_flashdata('error', 'Unable to create the user right now. Please try again later.');
			redirect('users');
		}

		$this->_render_form();
	}

	/**
	 * Form validation callback: reject usernames that are already taken.
	 *
	 * @param	string	$username
	 * @return	bool
	 */
	public function username_unique($username)
	{
		if ($this->User_model->username_exists($username))
		{
			$this->form_validation->set_message('username_unique', 'This username is already taken.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Form validation callback: only the two known user types are
	 * allowed.
	 *
	 * @param	string	$user_type
	 * @return	bool
	 */
	public function valid_user_type($user_type)
	{
		if ( ! in_array($user_type, array('admin', 'user_warehouse'), TRUE))
		{
			$this->form_validation->set_message('valid_user_type', 'Please choose a valid user type.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Form validation callback: a warehouse user must carry an existing
	 * warehouse; admins must not. The rule applies only to the posted
	 * user type, so the field can never be forced onto an admin.
	 *
	 * @param	string	$warehouse_id
	 * @return	bool
	 */
	public function warehouse_for_type($warehouse_id)
	{
		if ($this->input->post('user_type') !== 'user_warehouse')
		{
			return TRUE;
		}

		if ($warehouse_id === '' OR $warehouse_id === NULL)
		{
			$this->form_validation->set_message('warehouse_for_type', 'Please choose a warehouse for the warehouse user.');
			return FALSE;
		}

		if ($this->Warehouse_model->get_by_id($warehouse_id) === NULL)
		{
			$this->form_validation->set_message('warehouse_for_type', 'Please choose a valid warehouse.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Define the validation rules for the add-user form.
	 *
	 * @return	void
	 */
	protected function _set_validation_rules()
	{
		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9_.-]+$/]|callback_username_unique', array(
			'required' => 'Please enter a username.',
			'min_length' => 'Username must be at least 3 characters.',
			'max_length' => 'Username must be at most 50 characters.',
			'regex_match' => 'Username may only contain letters, numbers, dots, underscores, and dashes.',
		));
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]', array(
			'required' => 'Please enter a password.',
			'min_length' => 'Password must be at least 8 characters.',
		));
		$this->form_validation->set_rules('password_confirm', 'Password confirmation', 'required|matches[password]', array(
			'required' => 'Please confirm the password.',
			'matches' => 'The password confirmation does not match the password.',
		));
		$this->form_validation->set_rules('user_type', 'User type', 'required|callback_valid_user_type', array(
			'required' => 'Please choose a user type.',
		));
		// The warehouse is only required/validated for warehouse users
		// (see the callback).
		$this->form_validation->set_rules('warehouse_id', 'Warehouse', 'callback_warehouse_for_type');
	}

	/**
	 * Load the shared app layout with the add-user form.
	 *
	 * @return	void
	 */
	protected function _render_form()
	{
		$this->load->view('products/layout', array(
			'page_title' => 'Add User',
			'content_view' => 'users/create',
			'user' => $this->auth->current_user(),
			'content_data' => array(
				'warehouses' => $this->Warehouse_model->get_all(),
			),
		));
	}
}
