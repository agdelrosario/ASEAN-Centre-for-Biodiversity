<?php

/* Class:		Employee
 * Author:		Aletheia Grace del Rosario
 * Date:		November 6, 2011
 * Description:	Controls the retrieval of data from the database with regards to employees.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends CI_Model {

	function __construct()
    {
        parent::__construct();
    }
	
	function loadEmployees($limit = NULL, $offset = NULL)
	{	
		$employee_db = $this->load->database('employee_db', TRUE);
		
		$employee_db->select('A.employee_id, A.first, A.middle, A.last, C.position, D.acb_unit, D.acb_unit_acro');
		$employee_db->from('employee_db.employee A');
		$employee_db->join('employee_db.employee_acb_info B', 'A.employee_id = B.employee_id', 'left');
		$employee_db->join('library.position C', 'C.position_id = B.position_id', 'left');
		$employee_db->join('library.acb_unit D', 'B.unit_id = D.acb_unit_id', 'left');
		$employee_db->where('B.start = (SELECT MAX(F.start) FROM employee_db.employee E
					LEFT JOIN employee_db.employee_acb_info F ON E.employee_id = F.employee_id
					LEFT JOIN library.position G ON F.position_id = G.position_id
					where E.employee_id=A.employee_id)');
		if ($offset != NULL) $employee_db->limit($offset, $limit);
		$employee_db->group_by('A.employee_id');
		$employee_db->order_by('A.last, B.start');
		
		return $employee_db->get();
	}
	
	function getUnits()
	{
		$library = $this->load->database('library');
		echo $library->get('*', 'acb_unit');
	}
	
	function getName($id, $forpdf = 0)
	{
		$query = $this->loadEmployees();
		
		foreach ($query->result() as $row)
		{
			if ($row->employee_id == $id)
			{
				$middle_initial = '';
				$string = strtok($row->middle, ' ');
				
				while ($string != false)
				{
					$middle_initial = $middle_initial . $string[0] . ".";
					$string = strtok(' ');
				}
				
				if ($forpdf == 0) return $row->last . ", " . $row->first . " " . $middle_initial;
				else return $row->first . " " . $middle_initial . " " . $row->last;
			}
		}
	}
	
	function getUnitID ($unit)
	{
		$library = $this->load->database('library', TRUE);
		$library->select('*');
		$library->from('acb_unit');
		$library->where('acb_unit', $unit);
		$unit_query = $library->get();
		$un = $unit_query->row();
		
		return $un->acb_unit_id;
	}
	
	function getFAHead ()
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('*');
		$employee_db->from('employee_acb_info');
		$employee_db->where('position_id', 2);
		return $employee_db->get();
	}
	
	function getUnitHead ($unit)
	{
		$unitid = $this->getUnitID($unit);
	
		$library = $this->load->database('library', TRUE);
		$library->select('*');
		$library->from('position');
		$library->where('supervisor', 1);
		$unitheads = $library->get();
		
		foreach ($unitheads->result() as $row)
		{
			$employee_db = $this->load->database('employee_db', TRUE);
			$employee_db->select('*');
			$employee_db->from('employee_acb_info');
			$employee_db->where('unit_id', $unitid);
			$employee_db->where('position_id', $row->position_id);
			$unithead = $employee_db->get();
			
			if ($unithead->num_rows == 0) continue;
			else return $unithead;
		}
	}
	
	function getExecutiveDirector ()
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('*');
		$employee_db->from('employee_db.employee_acb_info');
		$employee_db->join('library.position', "library.position.position_id = employee_db.employee_acb_info.position_id");
		$employee_db->where('library.position.supervisor', 0);
		$execdir = $employee_db->get();
		
		if ($execdir->num_rows == 1)
		{
			$q = $execdir->row();
			return $q->employee_id;
		}
		else return "";
	}
	
	function getEmployeePosition($id)
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('*');
		$employee_db->from('employee_db.employee_acb_info');
		$employee_db->join('library.position', 'library.position.position_id = employee_db.employee_acb_info.position_id', 'left');
		$employee_db->where('employee_db.employee_acb_info.employee_id', $id);
		$user = $employee_db->get();
		$employee = $user->row();
		
		return $employee->position;
	}
	
	function getUnit($id)
	{
		$employee_db = $this->load->database('employee_db', TRUE);
		$employee_db->select('*');
		$employee_db->from('employee_db.employee_acb_info');
		$employee_db->join('library.position', 'library.position.position_id = employee_db.employee_acb_info.position_id', 'left');
		$employee_db->join('library.acb_unit', 'employee_db.employee_acb_info.unit_id = library.acb_unit.acb_unit_id', 'left');
		$employee_db->where('employee_db.employee_acb_info.employee_id', $id);
		$user = $employee_db->get();
		$employee = $user->row();
		
		return $employee->acb_unit;
	}
	
	function getDrivers ()
	{
		$employee_db = $this->load->database("employee_db", TRUE);
		$employee_db->select("*");
		$employee_db->from("employee_db.employee");
		$employee_db->join("employee_db.employee_acb_info", "employee_db.employee_acb_info.employee_id = employee_db.employee.employee_id");
		$employee_db->join("library.position", "library.position.position_id = employee_db.employee_acb_info.position_id");
		$employee_db->where("library.position.position = 'Driver'");
		return $employee_db->get();
	}
	
	function getTravelOrders ($id)
	{
		return $this->Records->getRowsFromTable("travel_orders", "*", "travel_order_employee_id", $id, "destinations", "destination_id = travel_orders.travel_order_destination_id", "travel_orders.travel_order_id", "desc");
	}
	
	function getTripTickets ($id)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("passengers");
		$ticketingsystem->where("passenger_employee_id", $id);
		$ticketingsystem->join("trip_tickets", "trip_ticket_id = passengers.passenger_trip_ticket_id");
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->order_by("trip_ticket_id", "desc");
		return $ticketingsystem->get();
	}
	
	function isDirector($id)
	{
		$employee_db = $this->load->database("employee_db", TRUE);
		
		$employee_db->select("*");
		$employee_db->from("employee_db.employee_acb_info");
		$employee_db->join("library.position", "library.position.position_id = employee_db.employee_acb_info.position_id");
		$employee_db->where("employee_db.employee_acb_info.employee_id", $id);
		$employee_db->where("library.position.supervisor", 1);
		$employee_db->where("library.position.recruitment_type_id", 1);
		$q = $employee_db->get();
		
		if ($q->num_rows != 0) return TRUE;
		else return FALSE;
	}
	
	function isExecutiveDirector($id)
	{
		$employee_db = $this->load->database("employee_db", TRUE);
		
		$employee_db->select("*");
		$employee_db->from("employee_db.employee_acb_info");
		$employee_db->join("library.position", "library.position.position_id = employee_db.employee_acb_info.position_id");
		$employee_db->where("employee_db.employee_acb_info.employee_id", $id);
		$employee_db->where("library.position.supervisor", 0);
		$employee_db->where("library.position.recruitment_type_id", 1);
		$q = $employee_db->get();
		
		if ($q->num_rows != 0) return TRUE;
		else return FALSE;
	}
}

/* End of file account.php */
/* Location: ./application/models/account.php */

?>