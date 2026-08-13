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
}
