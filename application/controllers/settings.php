<?php

/* Class:		Settings
 * Author:		Aletheia Grace del Rosario
 * Date:		November 6, 2011
 * Description:	Settings of the current user's account.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {

	public function index()
	{
		if ($this->session->userdata('user_logged'))
		{
			$this->load->library("encrypt");
			$this->load->view('settings');
			$this->session->set_userdata("current_page", uri_string());
		}
		else redirect('home');
	}
	
	function edit($key = NULL)
	{
		$justnew = TRUE;
		
		if (isset($_POST["change_username"])) $key = "username";
		else if (isset($_POST["change_password"])) $key = "password";
		else if (isset($_POST["change_email"])) $key = "email";
		else $justnew = FALSE;
		
		if ($key == "username" || $key == "password" || $key == "email")
		{
			$this->load->library("form_validation");
			$this->load->library("encrypt");
			if ($key == "username") $this->form_validation->set_rules("new_username", "new username", "required|alpha_numeric");
			if ($key == "password")
			{
				$this->form_validation->set_rules("new_password", "new password", "required");
				$this->form_validation->set_rules("confirm_new_password", "confirm new password", "required|matches[new_password]");
			}
			if ($key == "email") $this->form_validation->set_rules("new_email", "new email", "required|valid_email");
			$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
			
			if ($this->form_validation->run() == FALSE)
			{
				$data["key"] = "$key";
				$data["justnew"] = $justnew;
				$this->load->view('settings_edit', $data);
			}
			else
			{
				$this->Account->change($key, array("user_id" => $this->session->userdata("user_id"), "user_" . $key => $_POST["new_" . $key]));
				
				redirect($this->session->userdata('current_page'));
			}
		}
	}
}

/* End of file settings.php */
/* Location: ./application/controllers/settings.php */

?>