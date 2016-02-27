<?php

/* Class:		Administration
 * Author:		Aletheia Grace del Rosario
 * Date:		November 6, 2011
 * Description:	Settings of the whole database.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Administration extends CI_Controller {

	public function index($page = 0)
	{
		if ($this->session->userdata('user_logged') && $this->session->userdata("user_role") == "Administrator")
		{
			$data["query"] = $this->Account->getUsers($page * 10, 10);
			$data["total"] = $this->Account->countUsers("all");
			if ($data["total"] != 0) include("pagination.php");
			$this->load->view('administration', $data);
			$this->session->set_userdata("current_page", uri_string());
		}
		else redirect("home");
	}
	
	function change ($id)
	{
		if ($this->session->userdata('user_logged') && $this->session->userdata("user_role") == "Administrator")
		{
			$this->load->library("form_validation");
			$this->form_validation->set_rules("status", "status", "required");
			$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
			
			if ($this->form_validation->run() == FALSE)
			{
				$data["query"] = $this->Account->getUserData($id);
				$data["roles"] = $this->Account->getUserRoles();
				$this->load->view("administration_edit", $data);
			}
			else
			{
				$this->Account->change("users", array("user_id" => $id, "user_status" => $_POST["status"], "user_role_id" => $_POST["role"]));
				
				redirect($this->session->userdata("current_page"));
			}
		}
		else redirect("home");
	}
	
	function accounts ($key)
	{
		if ($this->session->userdata('user_logged') && $this->session->userdata("user_role") == "Administrator")
		{	
			if ($key == "add")
			{
				$this->load->library("form_validation");
				$this->form_validation->set_rules("username", "username", "required|alpha_numeric|max_length[25]|min_length[5]");
				$this->form_validation->set_rules("email", "e-mail address", "required|max_length[50]|valid_email");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
				
				if ($this->form_validation->run() == FALSE)
				{
					$data["query"] = $this->Employee->loadEmployees();
					$data["roles"] = $this->Account->getUserRoles();
					$this->load->view("administration_account_add", $data);
				}
				else
				{
					$this->load->library("encrypt");
					
					$d = array(
						"user_username" => $_POST["username"],
						"user_password" => $this->encrypt->encode($_POST["username"]),
						"user_email" => $_POST["email"],
						"user_status" => "active",
						"user_employee_id" => $_POST["employee"],
						"user_role_id" => $_POST["role"]
					);
					
					$this->Account->addUser($d);
					
					redirect($this->session->userdata("current_page"));
				}
			}
		}
		else redirect("home");
	}
	
	function reasons ($page = 0)
	{
		if ($this->session->userdata('user_logged') && $this->session->userdata("user_role") == "Administrator")
		{
			if ($page == "index" || is_numeric($page))
			{
				$data["query"] = $this->Records->getReportedErrors($page * 10, 10);
				$data["total"] = $this->Records->getQueryCount($this->Records->getReportedErrors());
				if ($data["total"] != 0) include("pagination.php");
				$data["page"] = $page;
				$this->load->view("administration_reasons", $data);
				$this->session->set_userdata("current_page", uri_string());
			}
			else if ($page == "add")
			{
				$this->load->library("form_validation");
				$this->form_validation->set_rules("reason", "reason", "required");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
				
				if ($this->form_validation->run() == FALSE) $this->load->view("administration_reasons_add");
				else
				{
					$this->Records->add("reason", array("reported_error_id" => $this->Records->getQueryCount($this->Records->getReportedErrors()), "reported_error_name" => $_POST["reason"]));
				
					redirect($this->session->userdata("current_page"));
				}
			}
		}
		else redirect("home");
	}
	
	function announcements ($page = 0, $id = NULL)
	{
		if ($this->session->userdata('user_logged') && $this->session->userdata("user_role") == "Administrator")
		{
			if (is_numeric($page) || $page == "index")
			{
				$data["query"] = $this->Account->getAnnouncements($page * 10, 10);
				$data["total"] = $this->Account->countAnnouncements();
				if ($data["total"] != 0) include("pagination.php");
				$this->load->view("administration_announcements", $data);
				$this->session->set_userdata("current_page", uri_string());
			}
			else if ($page == "add")
			{
				$this->load->library("form_validation");
				$this->form_validation->set_rules("announcement", "announcement", "required");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
				
				if ($this->form_validation->run() == FALSE) $this->load->view("administration_announcements_add");
				else
				{
					$data = array("announcement_proper" => $_POST["announcement"]);
					$this->Account->addAnnouncement($data);
					
					redirect($this->session->userdata("current_page"));
				}
			}
			else if ($page == "edit")
			{
				$this->load->library("form_validation");
				$this->form_validation->set_rules("announcement", "announcement", "required");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");
				
				if ($this->form_validation->run() == FALSE)
				{
					$data["query"] = $this->Account->getAnnouncement($id);
					$this->load->view("administration_announcements_edit", $data);
				}
				else
				{
					$data = array("announcement_proper" => $_POST["announcement"]);
					
					
					redirect($this->session->userdata("current_page"));
				}
			}
		}
		else redirect("home");
	}
}

/* End of file administration.php */
/* Location: ./application/controllers/administration.php */

?>