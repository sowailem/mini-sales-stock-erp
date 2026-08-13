<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth controller
 *
 * Handles sign-up, sign-in and sign-out. All database access is delegated
 * to User_model and all session handling to the Auth library.
 */
class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('User_model');
	}

	/**
	 * GET/POST /auth/signup
	 *
	 * Displays the sign-up form and creates a new user on valid submission.
	 * Successful registrations are signed in automatically.
	 */
	public function signup()
	{
		// Authenticated users have no business on the sign-up page.
		$this->auth->require_guest();

		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9_.-]+$/]|callback_username_unique', array(
			'required' => 'Please choose a username.',
			'min_length' => 'Username must be at least 3 characters.',
			'max_length' => 'Username must be at most 50 characters.',
			'regex_match' => 'Username may only contain letters, numbers, dots, underscores, and dashes.',
		));
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]', array(
			'required' => 'Please choose a password.',
			'min_length' => 'Password must be at least 8 characters.',
		));
		$this->form_validation->set_rules('password_confirm', 'Password confirmation', 'required|matches[password]', array(
			'required' => 'Please confirm your password.',
			'matches' => 'The password confirmation does not match your password.',
		));

		if ($this->form_validation->run() === TRUE)
		{
			$username = $this->input->post('username');

			$user_id = $this->User_model->create(array(
				'username' => $username,
				// Never store plain-text passwords.
				'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
				'is_active' => 1,
			));

			if ($user_id === FALSE)
			{
				log_message('error', 'Auth: failed to create user "' . $username . '" (database error)');
				$this->session->set_flashdata('error', 'Unable to create your account right now. Please try again later.');
				redirect('auth/signup');
			}

			$this->auth->login((object) array(
				'id' => $user_id,
				'username' => $username,
			));

			$this->session->set_flashdata('success', 'Account created successfully. Welcome!');
			redirect('dashboard');
		}

		$this->load->view('auth/layout', array(
			'page_title' => 'Create your account',
			'content_view' => 'auth/signup',
		));
	}

	/**
	 * GET/POST /auth/signin
	 *
	 * Displays the sign-in form and authenticates the user on valid submission.
	 */
	public function signin()
	{
		// Authenticated users have no business on the sign-in page.
		$this->auth->require_guest();

		$this->form_validation->set_rules('username', 'Username', 'trim|required', array(
			'required' => 'Please enter your username.',
		));
		$this->form_validation->set_rules('password', 'Password', 'required', array(
			'required' => 'Please enter your password.',
		));

		if ($this->form_validation->run() === TRUE)
		{
			$user = $this->User_model->find_by_username($this->input->post('username'));

			// Verify the password first; only then consider account status,
			// so the response never reveals whether a username exists.
			if ($user !== NULL && password_verify($this->input->post('password'), $user->password))
			{
				if ((int) $user->is_active === 1)
				{
					$this->auth->login($user);

					// Send the user back where they were headed, if anywhere.
					$uri = $this->auth->intended_uri();
					redirect($uri ? $uri : 'dashboard');
				}

				$this->session->set_flashdata('error', 'Your account is inactive. Please contact support.');
				redirect('auth/signin');
			}

			// Generic message: do not disclose whether the username exists.
			$this->session->set_flashdata('error', 'Invalid username or password.');
			redirect('auth/signin');
		}

		$this->load->view('auth/layout', array(
			'page_title' => 'Sign in',
			'content_view' => 'auth/signin',
		));
	}

	/**
	 * POST /auth/signout
	 *
	 * Destroys the authentication session and redirects to the sign-in page.
	 * Only accepts POST (with CSRF protection) to prevent forced logouts.
	 */
	public function signout()
	{
		// Only authenticated users can sign out.
		if ( ! $this->auth->is_logged_in())
		{
			redirect('auth/signin');
		}

		// Reject GET requests; a sign-out must come from the POST form.
		if ($this->input->method() !== 'post')
		{
			redirect('dashboard');
		}

		$this->auth->logout();

		$this->session->set_flashdata('success', 'You have been signed out successfully.');
		redirect('auth/signin');
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
}
