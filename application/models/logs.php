<?php

/* Class:		Logs
 * Author:		Aletheia Grace del Rosario
 * Date:		February 2, 2011
 * Description:	Controls the actions regarding the access of records in the database.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logs extends CI_Model {

	function __construct()
    {
        parent::__construct();
		$this->load->database();
    }
	
	function addAction($action_name)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_actions');
		$ticketingsystem->where('user_log_action_name', $action_name);
		$query = $ticketingsystem->get();
		
		if ($query->num_rows == 0)
		{
			$d = array(
				'user_log_action_id' => $this->countAllActions(),
				'user_log_action_name' => $action_name
			);
	
			$ticketingsystem->insert('user_log_actions', $d);
		}
	}
	
	function addLog($user_id, $action_id, $receiver_id, $document_id, $object_id)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		
		$ticketingsystem->insert(
			'user_logs',
			array(
				'user_log_id' => $this->countAllLogs() + 1,
				'user_log_user_id' => $user_id,
				'user_log_action_id' => $action_id,
				'user_log_receiver_id' => $receiver_id,
				'user_log_document_id' => $document_id,
				'user_log_object_id' => $object_id
			)
		);
	}
	
	function addObject($object_name)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		// Checks if the object to be added is not existing.
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_objects');
		$ticketingsystem->where('user_log_object_name', $object_name);
		$query = $ticketingsystem->get();
		
		if ($query->num_rows == 0)
		{
			$d = array(
				'user_log_object_id' => $this->countAllobjects(),
				'user_log_object_name' => $object_name
			);
	
			$ticketingsystem->insert('user_log_objects', $d);
		}
	}
	
	function countAllActions()
	{
		$query = $this->getActions();
		return $query->num_rows;
	}
	
	// Counts all records.
	function countAllLogs()
	{
		$query = $this->getLogs();
		return $query->num_rows;
	}
	
	function countAllobjects()
	{
		$query = $this->getobjects();
		return $query->num_rows;
	}
	
	function getActionID($action_name)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_actions');
		$ticketingsystem->where('user_log_action_name', $action_name);
		$query = $ticketingsystem->get();
		
		if ($query->num_rows == 0)
		{
			$this->addAction($action_name);
			return $this->getActionID($action_name);
		}
		else
		{
			$row = $query->row();
			return $row->user_log_action_id;
		}
	}
	
	function getActions()
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_actions');
		return $ticketingsystem->get();
	}
	
	// Retrieves records.
	function getLogs($limit = NULL, $offset = NULL)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_logs');
		$ticketingsystem->join('user_log_actions', 'user_log_actions.user_log_action_id = user_logs.user_log_action_id');
		$ticketingsystem->join('user_log_objects', 'user_log_objects.user_log_object_id = user_logs.user_log_object_id');
		$ticketingsystem->order_by('user_log_id', 'desc');
		if ($limit != NULL) $ticketingsystem->limit($limit, $offset);
		return $ticketingsystem->get();
	}
	
	function getObjectID($object_name)
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_objects');
		$ticketingsystem->where('user_log_object_name', $object_name);
		$query = $ticketingsystem->get();
		
		if ($query->num_rows == 0)
		{
			// Add object if the object does not exist.
			$this->addObject($object_name);
			return $this->getObjectID($object_name);
		}
		else
		{
			$row = $query->row();
			return $row->user_log_object_id;
		}
	}
	
	function getObjects()
	{
		$ticketingsystem = $this->load->database('ticketingsystem', TRUE);
		$ticketingsystem->select('*');
		$ticketingsystem->from('user_log_objects');
		return $ticketingsystem->get();
	}
}

/* End of file logs.php */
/* Location: ./application/models/logs.php */

?>