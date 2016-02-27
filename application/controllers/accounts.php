<?php

/* Class:		Account
 * Author:		Aletheia Grace del Rosario
 * Date:		November 6, 2011
 * Description:	Controls the actions a user can perform.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accounts extends CI_Controller {

	public function index()
	{
		if ($this->session->userdata('user_logged') == FALSE)
		{
			$this->load->library('form_validation');
			$this->load->view('login');
		}
		else redirect('home');
	}
	
	function login()
	{
		if (!$this->session->userdata('user_logged'))
		{
			$this->load->library('form_validation');
			$this->load->model('Account');
			
			$this->form_validation->set_rules('username', 'username', 'required|alpha_dash|min_length[5]|max_length[25]');
			$this->form_validation->set_rules('password', 'password', 'required|alpha_numeric');
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

			if ($this->form_validation->run() == FALSE)
			{
				$this->load->view('login');
			}
			else
			{
				if ($this->Account->login($_POST['username'], $_POST['password']))
					redirect('home');
				else $this->load->view('login');
			}
		}
		else redirect('home');
	}
	
	function logout()
	{
		// A user must be logged in, apparently, to be able to logout.
		if ($this->session->userdata('user_logged'))
		{
			$this->load->model('Account');
			
			$this->Account->logout();
		}
		
		redirect('');
	}
}

/* End of file usercontrol.php */
/* Location: ./application/controllers/usercontrol.php */

?>