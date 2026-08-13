<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard controller
 *
 * Example of a protected page. Guests are redirected to the sign-in page
 * by Auth::require_login(), which also remembers where they were headed.
 */
class Dashboard extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
	}

	public function index()
	{
		$this->auth->require_login();

		$this->load->view('dashboard/index', array(
			'user' => $this->auth->current_user(),
		));
	}
}
