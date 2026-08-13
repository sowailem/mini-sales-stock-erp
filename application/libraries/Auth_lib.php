<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_lib
 *
 * Central, reusable authentication layer built on top of the CodeIgniter
 * session library. Controllers use this (typically aliased to `$this->auth`)
 * instead of duplicating session logic. The session only ever stores:
 * user_id, username, is_authenticated.
 *
 * Note: the class is intentionally NOT named `Auth` — that name is taken by
 * the Auth controller, and CI's loader would treat an already-declared class
 * as loaded and construct the controller recursively.
 */
class Auth_lib
{
	/**
	 * CI super-object reference.
	 *
	 * @var object
	 */
	protected $CI;

	/**
	 * Session keys used to represent an authenticated user.
	 *
	 * @var array
	 */
	protected $session_keys = array('user_id', 'username', 'is_authenticated');

	/**
	 * Session key under which the intended (redirect-back) URI is stored.
	 *
	 * @var string
	 */
	protected $redirect_key = 'redirect_uri';

	public function __construct()
	{
		$this->CI =& get_instance();

		// Ensure dependencies are always available, even if this library
		// is loaded directly instead of through the autoloader.
		$this->CI->load->library('session');
		$this->CI->load->helper('url');
		$this->CI->load->model('User_model');
	}

	/**
	 * Whether the current visitor is authenticated.
	 *
	 * @return	bool
	 */
	public function is_logged_in()
	{
		return $this->CI->session->userdata('is_authenticated') === TRUE
			&& (int) $this->CI->session->userdata('user_id') > 0;
	}

	/**
	 * ID of the authenticated user, or NULL for guests.
	 *
	 * @return	int|NULL
	 */
	public function user_id()
	{
		return $this->is_logged_in() ? (int) $this->CI->session->userdata('user_id') : NULL;
	}

	/**
	 * Username of the authenticated user, or NULL for guests.
	 *
	 * @return	string|NULL
	 */
	public function username()
	{
		return $this->is_logged_in() ? $this->CI->session->userdata('username') : NULL;
	}

	/**
	 * Fresh user record for the authenticated user (no password hash),
	 * or NULL for guests / unknown users.
	 *
	 * @return	object|NULL
	 */
	public function current_user()
	{
		if ( ! $this->is_logged_in())
		{
			return NULL;
		}

		$user = $this->CI->User_model->get_by_id($this->user_id());

		// The account may have been deleted after the session was created.
		if ($user === NULL)
		{
			$this->logout();
		}

		return $user;
	}

	/**
	 * Sign a user in: regenerate the session ID (prevents session fixation)
	 * and store only the minimum required identity data.
	 *
	 * @param	object	$user	User row containing at least `id` and `username`
	 * @return	void
	 */
	public function login($user)
	{
		$this->CI->session->sess_regenerate(TRUE);

		$this->CI->session->set_userdata(array(
			'user_id' => (int) $user->id,
			'username' => (string) $user->username,
			'is_authenticated' => TRUE,
		));
	}

	/**
	 * Sign the current user out: remove all auth data and invalidate the
	 * old session ID. Non-auth session data (e.g. flashdata) is preserved.
	 *
	 * @return	void
	 */
	public function logout()
	{
		$this->CI->session->unset_userdata($this->session_keys);
		$this->CI->session->sess_regenerate(TRUE);
	}

	/**
	 * Guard for protected pages. Redirects guests to the sign-in page and
	 * remembers where they were trying to go so they can be sent back after
	 * a successful sign-in. Returns normally for authenticated users.
	 *
	 * @return	void
	 */
	public function require_login()
	{
		if ($this->is_logged_in())
		{
			return;
		}

		// Only remember GET navigation (never form submissions) and never
		// remember the sign-in page itself.
		if ($this->CI->input->method() === 'get' && $this->CI->uri->uri_string() !== 'auth/signin')
		{
			$this->CI->session->set_userdata($this->redirect_key, $this->CI->uri->uri_string());
		}

		redirect('auth/signin');
	}

	/**
	 * Guard for guest-only pages (sign-in / sign-up). Redirects
	 * authenticated users to the dashboard. Returns normally for guests.
	 *
	 * @return	void
	 */
	public function require_guest()
	{
		if ($this->is_logged_in())
		{
			redirect('dashboard');
		}
	}

	/**
	 * Consume and return the stored intended URI (if any) so the user can be
	 * redirected back to the page they originally requested. Only relative
	 * URI strings are ever stored, which prevents open-redirect abuse.
	 *
	 * @return	string|NULL
	 */
	public function intended_uri()
	{
		$uri = $this->CI->session->userdata($this->redirect_key);
		$this->CI->session->unset_userdata($this->redirect_key);

		return $uri;
	}

	/* ------------------------------------------------------------------ *
	 * Permissions
	 * ------------------------------------------------------------------ *
	 * The application knows exactly two user types, stored on the user
	 * record itself (no roles/permissions tables):
	 *
	 *   - 'admin'          global access, warehouse_id = NULL
	 *   - 'user_warehouse' access limited to warehouse_id
	 *
	 * These methods are the single source of truth for authorization;
	 * controllers must never re-derive the current user's type or scope
	 * on their own.
	 * ------------------------------------------------------------------ */

	/**
	 * User type of the authenticated user ('admin' or 'user_warehouse'),
	 * or NULL for guests / unknown users.
	 *
	 * @return	string|NULL
	 */
	public function user_type()
	{
		$user = $this->current_user();

		return $user !== NULL ? $user->user_type : NULL;
	}

	/**
	 * Whether the authenticated user is an admin (global access).
	 *
	 * @return	bool
	 */
	public function is_admin()
	{
		return $this->user_type() === 'admin';
	}

	/**
	 * Whether the authenticated user is a warehouse user (access limited
	 * to one assigned warehouse).
	 *
	 * @return	bool
	 */
	public function is_warehouse_user()
	{
		return $this->user_type() === 'user_warehouse';
	}

	/**
	 * Warehouse the authenticated user is assigned to, or NULL for
	 * admins and guests. A warehouse user whose warehouse was deleted
	 * (FK ON DELETE SET NULL) also gets NULL here.
	 *
	 * @return	int|NULL
	 */
	public function assigned_warehouse_id()
	{
		$user = $this->current_user();

		if ($user === NULL)
		{
			return NULL;
		}

		$warehouse_id = $user->warehouse_id;

		return ($warehouse_id !== NULL && (int) $warehouse_id > 0) ? (int) $warehouse_id : NULL;
	}

	/**
	 * Warehouse record the authenticated user is assigned to, or NULL.
	 * Only meaningful for warehouse users.
	 *
	 * @return	object|NULL
	 */
	public function assigned_warehouse()
	{
		$warehouse_id = $this->assigned_warehouse_id();

		if ($warehouse_id === NULL)
		{
			return NULL;
		}

		$this->CI->load->model('Warehouse_model');

		return $this->CI->Warehouse_model->get_by_id($warehouse_id);
	}

	/**
	 * Warehouse scope for warehouse-aware queries. NULL means "all
	 * warehouses" (admins), a positive integer restricts to that
	 * warehouse, and 0 means "nothing visible" (a warehouse user whose
	 * assignment is missing, or an unknown user type). Controllers feed
	 * this into a model's scope_to_warehouse() in exactly one place per
	 * request.
	 *
	 * @return	int|NULL
	 */
	public function warehouse_scope()
	{
		if ($this->is_admin())
		{
			return NULL;
		}

		if ($this->is_warehouse_user())
		{
			$warehouse_id = $this->assigned_warehouse_id();

			return $warehouse_id !== NULL ? $warehouse_id : 0;
		}

		return 0;
	}

	/**
	 * Whether the authenticated user may access the given warehouse.
	 * Admins may access any warehouse; warehouse users only their own.
	 * Invalid IDs are always rejected.
	 *
	 * @param	mixed	$warehouse_id
	 * @return	bool
	 */
	public function can_access_warehouse($warehouse_id)
	{
		if ( ! is_numeric($warehouse_id) OR (int) $warehouse_id <= 0)
		{
			return FALSE;
		}

		if ($this->is_admin())
		{
			return TRUE;
		}

		if ( ! $this->is_warehouse_user())
		{
			return FALSE;
		}

		return (int) $warehouse_id === (int) $this->assigned_warehouse_id();
	}

	/**
	 * Guard for admin-only pages. Redirects guests to the sign-in page
	 * and non-admins to the dashboard with a generic error. Returns
	 * normally for admins.
	 *
	 * @return	void
	 */
	public function require_admin()
	{
		$this->require_login();

		if ($this->is_admin())
		{
			return;
		}

		// Generic message: never reveal what the restricted page was.
		$this->CI->session->set_flashdata('error', 'You do not have permission to access this page.');
		redirect('dashboard');
	}

	/**
	 * Guard for access to a specific warehouse. Redirects guests to the
	 * sign-in page and users without access to that warehouse to the
	 * inventory page with a generic error. Returns normally for users
	 * who may access the warehouse.
	 *
	 * @param	mixed	$warehouse_id
	 * @return	void
	 */
	public function require_warehouse_access($warehouse_id)
	{
		$this->require_login();

		if ($this->can_access_warehouse($warehouse_id))
		{
			return;
		}

		$this->CI->session->set_flashdata('error', 'You do not have permission to access that warehouse.');
		redirect('inventory');
	}
}
