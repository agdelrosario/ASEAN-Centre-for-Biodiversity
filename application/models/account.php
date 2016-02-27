<?php

/* Class:		Accounts
 * Author:		Aletheia Grace del Rosario
 * Date:		November 6, 2011
 * Description:	Controls the actions regarding the users' accounts.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }
	
	function login($username, $password)
	{
		$this->load->library("encrypt");
		
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_roles');
		$ticketingsystem->join('users', 'user_roles.user_role_id = users.user_role_id');
		$ticketingsystem->where('user_username', $username);
		$ticketingsystem->where("user_status", "active");
		$ticketingsystem->group_by('users.user_role_id');
		$ticketingsystem->order_by('users.user_role_id');
		$query = $ticketingsystem->get();
		
		
		if ($query->num_rows == 1)
		{
			$row = $query->row();
			
			$decoded = $this->encrypt->decode($row->user_password);
			
			if ($decoded == $password)
			{
				$this->session->set_userdata(
					array(
						'user_username'  => $username,
						'user_password' => $row->user_password,
						'user_employee_id' => $row->user_employee_id,
						'user_id' => $row->user_id,
						'user_role'	=> $row->user_role_name,
						'user_status' => $row->user_status,
						'user_email' => $row->user_email,
						'user_logged' => TRUE
					)
				);
			
				return true;
			}
			else return false;
		}
		else return false;
	}
	
	function logout()
	{
		$this->load->helper('date');
		$this->session->sess_destroy();
	}
	
	function getUsers($limit, $offset)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("users");
		$ticketingsystem->join("user_roles", "users.user_role_id = user_roles.user_role_id");
		$ticketingsystem->order_by("user_username", "asc");
		$ticketingsystem->limit($offset, $limit);
		return $ticketingsystem->get();
	}
	
	function countUsers()
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("users");
		$ticketingsystem->join("user_roles", "users.user_role_id = user_roles.user_role_id");
		$q = $ticketingsystem->get();
		return $q->num_rows;
	}
	
	function getUserRoles()
	{
		return $this->Records->getRowsFromTable("user_roles", "*");
	}
	
	function getUserData($user_id)
	{
		return $this->Records->getRowsFromTable("users", "*", "user_id", $user_id, "user_roles", "users.user_role_id = user_roles.user_role_id");
	}
	
	function getUsername($user_id)
	{
		$q = $this->Records->getRowsFromTable("users", "*", "user_id", $user_id, "user_roles", "users.user_role_id = user_roles.user_role_id");
		$row = $q->row();
		
		return $row->user_username;
	}
	
	function getUserRole($user_id)
	{
		$q = $this->Records->getRowsFromTable("users", "*", "user_id", $user_id, 'user_roles', 'user_roles.user_role_id = users.user_role_id');
		return $q->last_row()->user_role_name;
	}
	
	function getUserUnit($user_id)
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('employee_db.employee_acb_info.employee_id, library.acb_unit.acb_unit_acro');
		$employee_db->from('employee_db.employee_acb_info');
		$employee_db->join('library.acb_unit', 'library.acb_unit.acb_unit_id = employee_db.employee_acb_info.unit_id');
		$employee_db->join('ticketingsystem.users', 'ticketingsystem.users.user_employee_id = employee_db.employee_acb_info.employee_id');
		$employee_db->where('ticketingsystem.users.user_employee_id', $user_id);
		$query = $employee_db->get();
		
		if ($row != NULL)
		{
			$row = $query->row();			
			return $row->acb_unit_acro;
		}
		else return NULL;
	}
	
	function getUserUnitID($user_id)
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('employee_db.employee_acb_info.employee_id, employee_db.employee_acb_info.unit_id');
		$employee_db->from('employee_db.employee_acb_info');
		$employee_db->where('employee_db.employee_acb_info.unit_id', $user_id);
		$employee_db->or_where('employee_db.employee_acb_info.employee_id', 0);
		$query = $employee_db->get();
		
		if ($query->num_rows != 0)
		{
			$row = $query->row();
			if ($row->employee_id == 0) return NULL;
			else return $row->unit_id;
		}
		else return NULL;
	}
	
	function adminAnnouncements($limit = NULL, $offset = NULL)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('announcement_activation');
		$ticketingsystem->join('announcements', 'announcements.announcement_id = announcement_activation.announcement_activation_id', 'inner');
		$ticketingsystem->where('announcement_activation.announcement_activation_status_id', '1');
		if ($offset != NULL) $ticketingsystem->limit($offset, $limit);
		return $ticketingsystem->get();
	}
	
	function getAnnouncements($limit = NULL, $offset = NULL)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('announcements');
		if ($offset != NULL) $ticketingsystem->limit($offset, $limit);
		return $ticketingsystem->get();
	}
	
	function getAnnouncement($id)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('announcements');
		$ticketingsystem->where("announcement_id", $id);
		return $ticketingsystem->get();
	}
	
	function getAnnouncementStatus($id)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('announcements');
		$ticketingsystem->where("announcement_id", $id);
		$ticketingsystem->join("announcement_activation", "announcement_activation_id = announcements.announcement_id");
		$ticketingsystem->join("announcement_statuses", "announcement_activation_status_id = announcement_statuses.announcement_status_id");
		$q = $ticketingsystem->get();
		
		if ($q->num_rows == 0) return "Inactive";
		else
		{
			$row = $q->row();
			
			return ucwords($row->announcement_status_name);
		}
	}
	
	function countAnnouncements()
	{
		$q = $this->getAnnouncements();
		return $q->num_rows;
	}
	
	function change($key, $data)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		
		if ($key == "password")
		{
			$this->load->library('encrypt');
			$data["user_password"] = $this->encrypt->encode($data["user_password"]);
		}
		
		if ($this->Records->getQueryCount($this->Records->getRowsFromTable("users", "*", "user_" . $key, $data["user_" . $key])) == 0)
		{
			$ticketingsystem->where("user_id", $data["user_id"]);
			$ticketingsystem->update("users", $data);
			
			$this->session->set_userdata("user_" . $key, $data["user_" . $key]);
		}
	}
	
	function addUser($data)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$query = $ticketingsystem->get_where("users", array("user_username" => $data["user_username"]));
		$email = $ticketingsystem->get_where("users", array("user_email" => $data["user_email"]));
		
		if ($query->num_rows == 0 && $email->num_rows == 0)
		{
			$data["user_id"] = $this->countUsers() + 1;
			
			$ticketingsystem->insert("users", $data);
		}
	}
	
	function getEmployeeID($user_id)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$q = $ticketingsystem->get_where("users", array("user_id" => $user_id));
		
		if ($q->num_rows > 0)
		{
			$row = $q->row();
			return $row->user_employee_id;
		}
	}
	
	function getAdministrator()
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("users");
		$ticketingsystem->join("user_roles", "user_roles.user_role_id = users.user_role_id");
		$ticketingsystem->where("user_roles.user_role_name", "Administrator");
		$q = $ticketingsystem->get();
		
		// Returns the first administrator account seen in the system.
		$row = $q->row();
		
		return $row->user_id;
	}
}

/* End of file account.php */
/* Location: ./application/models/account.php */

?>