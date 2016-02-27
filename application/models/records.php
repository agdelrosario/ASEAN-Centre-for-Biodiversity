<?php

/* Class:		Records
 * Author:		Aletheia Grace del Rosario
 * Date:		February 2, 2012
 * Description:	Controls the actions regarding the access of records in the database.
 */

if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class Records extends CI_Model {

	var $exists = array(
		"approved" => array("to" => "EXISTS (SELECT NULL FROM ticketingsystem.approved_travel_orders a WHERE a.approved_travel_order_id = travel_orders.travel_order_id)",
							"tt" => "EXISTS (SELECT NULL FROM ticketingsystem.approved_trip_tickets a WHERE a.approved_trip_ticket_id = trip_tickets.trip_ticket_id)"),
		"cancelled" => array("to" => "EXISTS (SELECT * FROM ticketingsystem.cancelled_travel_orders c WHERE c.cancelled_travel_order_id = travel_orders.travel_order_id)",
							"tt" => "EXISTS (SELECT * FROM ticketingsystem.cancelled_trip_tickets c WHERE c.cancelled_trip_ticket_id = trip_tickets.trip_ticket_id)"),
		"disapproved" => array("to" => "EXISTS (SELECT NULL FROM ticketingsystem.disapproved_travel_orders d WHERE d.disapproved_travel_order_id = travel_orders.travel_order_id)",
							"tt" => "EXISTS (SELECT NULL FROM ticketingsystem.disapproved_trip_tickets d WHERE d.disapproved_trip_ticket_id = trip_tickets.trip_ticket_id)"),
		"recommended" => array("to" => "EXISTS (SELECT * FROM ticketingsystem.recommended_travel_orders r WHERE r.recommended_travel_order_id = travel_orders.travel_order_id)"),
		"reported" => array("to" => "EXISTS (SELECT * FROM ticketingsystem.reported_travel_orders r WHERE r.reported_travel_order_id = travel_orders.travel_order_id)",
							"tt" => "EXISTS (SELECT * FROM ticketingsystem.reported_trip_tickets r WHERE r.reported_trip_ticket_id = trip_tickets.trip_ticket_id)"),
		"expired" => array("to" => "EXISTS (SELECT * FROM ticketingsystem.expired_travel_orders e WHERE e.expired_travel_order_id = travel_orders.travel_order_id)",
							"tt" => "EXISTS (SELECT * FROM ticketingsystem.expired_trip_tickets e WHERE e.expired_trip_ticket_id = trip_tickets.trip_ticket_id)")
	);
	
	var $not_exists = array(
		"approved" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.approved_travel_orders a WHERE a.approved_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT a.approved_travel_order_id FROM ticketingsystem.approved_travel_orders a)",
							"tt" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.approved_trip_tickets a WHERE a.approved_trip_ticket_id IS NULL) AND trip_tickets.trip_ticket_id NOT IN (SELECT a.approved_trip_ticket_id FROM ticketingsystem.approved_trip_tickets a)"),
		"cancelled" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.cancelled_travel_orders c WHERE c.cancelled_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT c.cancelled_travel_order_id FROM ticketingsystem.cancelled_travel_orders c)",
							"tt" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.cancelled_trip_tickets c WHERE c.cancelled_trip_ticket_id IS NULL) AND trip_tickets.trip_ticket_id NOT IN (SELECT c.cancelled_trip_ticket_id FROM ticketingsystem.cancelled_trip_tickets c)"),
		"disapproved" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.disapproved_travel_orders d WHERE d.disapproved_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT d.disapproved_travel_order_id FROM ticketingsystem.disapproved_travel_orders d)",
							"tt" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.disapproved_trip_tickets d WHERE d.disapproved_trip_ticket_id IS NULL) AND trip_tickets.trip_ticket_id NOT IN (SELECT d.disapproved_trip_ticket_id FROM ticketingsystem.disapproved_trip_tickets d)"),
		"recommended" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.recommended_travel_orders r WHERE r.recommended_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT r.recommended_travel_order_id FROM ticketingsystem.recommended_travel_orders r)"),
		"reported" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.reported_travel_orders rp WHERE rp.reported_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT rp.reported_travel_order_id FROM ticketingsystem.reported_travel_orders rp)",
							"tt" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.reported_trip_tickets rp WHERE rp.reported_trip_ticket_id IS NULL) AND trip_tickets.trip_ticket_id NOT IN (SELECT rp.reported_trip_ticket_id FROM ticketingsystem.reported_trip_tickets rp)"),
		"expired" => array("to" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.expired_travel_orders e WHERE e.expired_travel_order_id IS NULL) AND travel_orders.travel_order_id NOT IN (SELECT e.expired_travel_order_id FROM ticketingsystem.expired_travel_orders e)",
							"tt" => "NOT EXISTS (SELECT NULL FROM ticketingsystem.expired_trip_tickets e WHERE e.expired_trip_ticket_id IS NULL) AND trip_tickets.trip_ticket_id NOT IN (SELECT e.expired_trip_ticket_id FROM ticketingsystem.expired_trip_tickets e)")
	);

	function __construct()
    {
        parent::__construct();
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
    }
	
	function add ($key, $data)
	{
		$keys = array(
			"assistant" => "assistant",
			"plate number" => "vehicle",
			"travel order" => "travel_order",
			"trip ticket" => "trip_ticket",
			"requesting official" => "requesting_official",
			"passenger" => "passenger",
			"destination" => "destination",
			"reason" => "reported_error"
		);
		
		$user_id = $this->session->userdata("user_id");
		
		if (($key == "assistant" || $key == "requesting official" || $key == "passenger") && $this->getQueryCount($this->getRowsFromTable($keys[$key] . "s", "*", $keys[$key] . "_travel_order_id", $data[$keys[$key] . "_travel_order_id"])) == 0)
			$this->insertToTable($keys[$key] . "s", $data);
		else if (($key == "destination" && $this->getQueryCount($this->getRowsFromTable("destinations", "*", "destination_name", $data["destination_name"])) == 0))
			$this->insertToTable($key . "s", $data, $user_id, "add", NULL, $data[$key . "_name"], $key);
		else if ($key == "reason" && $this->getQueryCount($this->getRowsFromTable("reported_errors", "*", "reported_error_name", $data["reported_error_name"])) == 0)
			$this->insertToTable($keys[$key] . "s", $data, $user_id, "add", NULL, $data[$keys[$key] . "_name"], $key);
		else if ($key == "plate number")
			$this->insertToTable($keys[$key] . "s", $data, $user_id, "add", NULL, $data[$keys[$key] . "_platenum"], $keys[$key]);
		else if ($key == "travel order" || $key == "trip ticket")
		{
			$recepient = ($key == "travel order") ? $data["travel_order_employee_id"] : NULL;
			$this->insertToTable($keys[$key] . "s", $data, $user_id, "add", $recepient, $data[$keys[$key] . "_id"], $key);
			if ($key == "travel order" && $this->Employee->getUnit($this->getTravelOrderEmployeeID($data["travel_order_id"])) == "Finance and Administration")
			{
				$dt = array("recommended_travel_order_id" => $data["travel_order_id"], "recommended_travel_order_user" => $user_id);
				$this->insertToTable("recommended_travel_orders", $dt, $user_id, "add", $recepient, $data["travel_order_id"], $key);
			}
		}
	}
	
	function addTravelOrderByStatus($key, $id, $user, $reason = NULL, $specifics = NULL)
	{
		$status = $this->getTravelOrderStatus($id);
		$employee = $this->getTravelOrderEmployeeID($id);
		$role = $this->session->userdata("user_role");
		
		$table = array("approve" => "approved", "cancel" => "cancelled", "disapprove" => "disapproved", "recommend" => "recommended", "report" => "reported");
		$data = array($table[$key] . "_travel_order_id" => $id, $table[$key] . "_travel_order_user" => $user);
		
		if ($key == "report")
		{
			$data[$table[$key] . "_travel_order_reason"] = $reason;
			$data[$table[$key] . "_travel_order_specifics"] = $specifics;
		}
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->where("travel_order_date_arrival <= ", date("Y-m-d"));
		$ticketingsystem->where("travel_order_id", $id);
		$query = $ticketingsystem->get();
		
		if ((($status == "Approved" || $status == "Disapproved") && $key == "report" && $query->num_rows == 0) ||
			($status == "For approval" && ($key == "report" || (($key == "approve" || $key == "disapprove") && ($role == "Administrator" || ($role == "Approving Official" && (($this->session->userdata("user_employee_id") != $this->getTravelOrderEmployeeID($id) || ($this->Employee->isExecutiveDirector($user) && $this->Employee->isDirector($this->getTravelOrderEmployeeID($id)))))))))) ||
			($status == "For cancellation" && $key == "cancel") ||
			($status == "For recommendation" && (($key == "recommend" && ($role == "Unit Head" || $role == "Administrator" || ($role == "Moderator" && $this->Employee->isDirector($this->getTravelOrderEmployeeID($id))))) || $key == "report")) ||
			($status == "Expired" && $key == "cancel")
		)
			$this->insertToTable($table[$key] . "_travel_orders", $data, $user, $key, $employee, $id, "travel order");
	}
	
	function addTripTicketByStatus($key, $id, $user, $reason = NULL, $specifics = NULL)
	{
		$status = $this->getTripTicketStatus($id);
		
		$table = array("approve" => "approved", "cancel" => "cancelled", "disapprove" => "disapproved", "report" => "reported");
		$data = array($table[$key] . "_trip_ticket_id" => $id, $table[$key] . "_trip_ticket_user" => $user);
		
		if ($key == "report")
		{
			$data[$table[$key] . "_trip_ticket_reason"] = $reason;
			$data[$table[$key] . "_trip_ticket_specifics"] = $specifics;
		}
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->where("trip_ticket_date_travel <= ", date("Y-m-d"));
		$ticketingsystem->where("trip_ticket_id", $id);
		$query = $ticketingsystem->get();
		
		if ((($status == "Approved" || $status == "Disapproved") && $key == "report" && $query->num_rows == 0) ||
			($status == "For approval" && ($key == "report" || $key == "approve" || $key == "disapprove")) ||
			($status == "For cancellation" && $key == "cancel") ||
			($status == "Expired" && $key == "cancel"))
			$this->insertToTable($table[$key] . "_trip_tickets", $data, $user, $key, $employee, $id, "trip ticket");
	}
	
	function autoCancelTravelOrders()
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->where("travel_order_date_arrival <= ", date("Y-m-d", strtotime("-1 week")));
		$ticketingsystem->where($this->exists["expired"]["to"] . " AND " . $this->not_exists["approved"]["to"] . " AND " . $this->not_exists["disapproved"]["to"] . " AND " . $this->not_exists["cancelled"]["to"], NULL, FALSE);
		$q = $ticketingsystem->get();
		
		foreach ($q->result() as $row)
		{
			$this->insertToTable("cancelled_travel_orders", array("cancelled_travel_order_id" => $row->travel_order_id, "cancelled_travel_order_user" => $this->Account->getAdministrator()));
			
			$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
			$ticketingsystem->where("expired_travel_order_id", $row->travel_order_id);
			$ticketingsystem->update("expired_travel_orders", array("expired_travel_order_auto" => "true"));
		}
		
		return $q;
	}
	
	function autoCancelTripTickets()
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->where("trip_ticket_date_travel <= ", date("Y-m-d", strtotime("-1 week")));
		$ticketingsystem->where($this->not_exists["approved"]["tt"] . " AND " . $this->not_exists["cancelled"]["tt"], NULL, FALSE);
		$q = $ticketingsystem->get();
		
		foreach ($q->result() as $row)
		{
			$this->insertToTable("cancelled_trip_tickets", array("cancelled_trip_ticket_id" => $row->trip_ticket_id, "cancelled_trip_ticket_user" => $this->Account->getAdministrator()));
			
			$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
			$ticketingsystem->where("expired_trip_ticket_id", $row->trip_ticket_id);
			$ticketingsystem->update("expired_trip_tickets", array("expired_trip_ticket_auto" => "true"));
		}
		
		return $q;
	}
	
	function checkAddDuplicatesTravelOrder($employee_id, $destination_id, $departure_date)
	{
		$n = " AND ";
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->where("travel_order_employee_id", $employee_id);
		$ticketingsystem->where("travel_order_destination_id", $destination_id);
		$ticketingsystem->where("travel_order_date_departure", $departure_date);
		$ticketingsystem->where($this->exists["recommended"]["to"] . $n . $this->exists["approved"]["to"] . $n . $this->not_exists["disapproved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"], NULL, FALSE);
		$query = $ticketingsystem->get();
		
		if ($query->num_rows == 0) return "false";
		else return "true";
	}
	
	function checkDateValidity($departure_date, $arrival_date)
	{
		$departure_year = strtok($departure_date, "-");
		$departure_month = strtok("-");
		$departure_day = strtok(strtok("-"), " ");
		$arrival_year = strtok($arrival_date, "-");
		$arrival_month = strtok("-");
		$arrival_day = strtok(strtok("-"), " ");
		
		if ($departure_year > date("Y"))
		{
			if ($arrival_year > $departure_year) return "valid";
			else if ($arrival_year == $departure_year)
			{
				if ($arrival_month > $departure_month) return "valid";
				else if ($arrival_month == $departure_month)
				{
					if ($arrival_day >= $departure_day) return "valid";
					else return "invalid";
				}
				else return "invalid";
			}
			else return "invalid";
		}
		else if ($departure_year == date("Y"))
		{
			if ($departure_month > date("m")) return "valid";
			else if ($departure_month == date("m"))
			{
				if ($departure_day > date("d")) return "valid";
				else return "invalid";
			}
			else return "invalid";
		}
		else return "invalid";
	}
	
	function checkTravelOrderInsertPermissions($id, $cancelled = NULL, $reported = NULL, $approved = NULL, $disapproved = NULL, $recommended = NULL, $expired = NULL)
	{
		$data = $this->retrieveExistence($id, "travel orders");
		
		$status = array(
			"cancelled" => $cancelled,
			"reported" => $reported,
			"expired" => $expired,
			"approved" => $approved,
			"disapproved" => $disapproved,
			"recommended" => $recommended
		);
		
		$key = array_keys($status);
		$value = FALSE;
		
		foreach ($key as $row)
			if ($status[$row] != NULL)
			{
				if ($data[$row] == $status[$row]) $value = TRUE;
				else return FALSE;
			}
		
		return $value;
	}
	
	function checkTripTicketInsertPermissions($id, $cancelled = NULL, $reported = NULL, $approved = NULL, $disapproved = NULL)
	{
		$data = $this->retrieveExistence($id, "trip tickets");
		
		$status = array(
			"cancelled" => $cancelled,
			"reported" => $reported,
			"approved" => $approved,
			"disapproved" => $disapproved
		);
		
		$key = array_keys($status);
		$value = FALSE;
		
		foreach ($key as $row)
			if ($status[$row] != NULL)
			{
				if ($data[$row] == $status[$row]) $value = TRUE;
				else return FALSE;
			}
		
		return $value;
	}
	
	function countDriverTrips($id, $month, $year)
	{
		$q = $this->getDriverTrips($id, $month, $year);
		return $q->num_rows;
	}
	
	function countTravelOrderSearchResults($query)
	{
		return $this->getQueryCount($this->searchTravelOrders($query));
	}
	
	function countTripTicketSearchResults($query)
	{
		return $this->getQueryCount($this->searchTripTickets($query));
	}
	
	function countTravelOrders($key, $user_id = NULL)
	{
		return $this->getQueryCount($this->getTravelOrdersByStatus($key, NULL, NULL, $user_id));
	}
	
	function countTripsForYear($year)
	{
		$n = " AND ";
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);		
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("vehicles", "vehicle_id = trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->order_by("trip_ticket_id", "desc");
		$ticketingsystem->like("trip_ticket_date_travel", $year, "after");
		$ticketingsystem->where($this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"], NULL, FALSE);
		$q = $ticketingsystem->get();
		
		return $q->num_rows;
	}
	
	function countTripTickets($key, $user_id = NULL)
	{
		$q = $this->getTripTicketsByStatus($key, NULL, NULL, $user_id);
		
		if ($q != NULL) return $this->getQueryCount($q);
		else return 0;
	}
	
	function countUpcomingTrips()
	{
		$q = $this->getUpcomingTrips();
		return $q->num_rows;
	}
	
	function deleteFromTable($table, $where, $element)
	{
		$q = $this->getRowsFromTable($table, "*", $where, $element);
		
		if ($q->num_rows != 0)
		{
			$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
			$ticketingsystem->where($where, $element);
			$ticketingsystem->delete($table);
		}
	}
	
	function getAssistants ($id)
	{
		return $this->getRowsFromTable("assistants", "*", "assistant_travel_order_id", $id);
	}
	
	function getDestinations()
	{
		return $this->getRowsFromTable("destinations", "*");
	}
	
	function getDriverTrips($id, $month, $year)
	{
		$n = " AND ";
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);		
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("vehicles", "vehicle_id = trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->order_by("trip_ticket_id", "desc");
		$ticketingsystem->where("trip_ticket_driver_id", $id);
		$ticketingsystem->like("trip_ticket_date_travel", $year . "-" . $month, "after");
		$ticketingsystem->where($this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"], NULL, FALSE);
		return $ticketingsystem->get();
	}
	
	function getExpiredTravelOrders()
	{	
		$n = " AND ";
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->where("travel_order_date_arrival < ", date("Y-m-d", strtotime("+1 day")));
		$ticketingsystem->where("travel_order_date_arrival < ", date("Y-m-d", strtotime("-1 week")));
		$ticketingsystem->where($this->not_exists["cancelled"]["to"] . $n . $this->not_exists["approved"]["to"], NULL, FALSE);
		
		$query = $ticketingsystem->get();
		
		foreach ($query->result() as $row)
		{
			if ($this->getQueryCount($this->getRowsFromTable("expired_travel_orders", "*", "expired_travel_order_id", $row->travel_order_id)) == 0)
				$this->insertToTable("expired_travel_orders", array("expired_travel_order_id" => $row->travel_order_id, "expired_travel_order_user" => $this->Account->getAdministrator()));
		}
		
		return $query;
	}
	
	function getExpiredTripTickets()
	{
		$n = " AND ";
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->where("trip_ticket_date_travel < ", date("Y-m-d", strtotime("+1 day")));
		$ticketingsystem->where("trip_ticket_date_travel < ", date("Y-m-d", strtotime("-1 week")));
		$ticketingsystem->where($this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["approved"]["tt"], NULL, FALSE);
		
		$query = $ticketingsystem->get();
		
		foreach ($query->result() as $row)
		{
			if ($this->getQueryCount($this->getRowsFromTable("expired_trip_tickets", "*", "expired_trip_ticket_id", $row->trip_ticket_id)) == 0)
				$this->insertToTable("expired_trip_tickets", array("expired_trip_ticket_id" => $row->trip_ticket_id, "expired_trip_ticket_user" => $this->Account->getAdministrator()));
		}
		
		return $query;
	}
	
	function getNewTravelOrderID ($year)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->like("travel_order_id", $year, "after");
		$query = $ticketingsystem->get();
		return $year . "-" . str_pad($query->num_rows + 1, 3, "0", STR_PAD_LEFT);
	}
	
	function getNewTripTicketID ($year)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->like("trip_ticket_id", $year, "after");
		$query = $ticketingsystem->get();
		return $year . "-" . str_pad($query->num_rows + 1, 3, "0", STR_PAD_LEFT);
	}
	
	function getPlateNumbers ()
	{
		return $this->getRowsFromTable("vehicles", "*");
	}
	
	function getQueryCount($query)
	{
		return $query->num_rows;
	}
	
	function getReason($key, $id)
	{
		$docs = array(
			"travel order" => "travel_order",
			"trip ticket" => "trip_ticket"
		);
		
		$query = $this->getRowsFromTable("reported_" . $docs[$key] . "s", "*", "reported_" . $docs[$key] . "_id", $id, "reported_errors", "reported_" . $docs[$key] . "_reason = reported_error_id");
		$row = $query->row();
		
		$reason = $row->reported_error_name;
		if ($reason == NULL) return "None";
		else return $reason;
	}
	
	function getSpecifics($key, $id)
	{
		$docs = array(
			"travel order" => "travel_order",
			"trip ticket" => "trip_ticket"
		);
		
		$query = $this->getRowsFromTable("reported_" . $docs[$key] . "s", "*", "reported_" . $docs[$key] . "_id", $id);
		$row = $query->row();
		
		if ($key == "travel order") $reason = $row->reported_travel_order_specifics;
		else if ($key == "trip ticket") $reason = $row->reported_trip_ticket_specifics;
		
		if ($reason == NULL) return "None";
		else return $reason;
	}
	
	function getReportedErrors($limit = NULL, $offset = NULL)
	{
		if ($offset == NULL) return $this->getRowsFromTable("reported_errors", "*");
		else return $this->getRowsFromTable("reported_errors", "*", NULL, NULL, NULL, NULL, "reported_error_name", "asc", $limit, $offset);
	}
	
	function getRowsFromTable($table, $selection, $element = NULL, $value = NULL, $join_to_table = NULL, $join_on = NULL, $order_by = NULL, $order_by_arr = NULL, $limit = NULL, $offset = NULL, $like_column = NULL, $like_text = NULL, $like_placement = NULL)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select($selection);
		$ticketingsystem->from($table);
		if ($element != NULL)
		{
			if ($value != NULL) $ticketingsystem->where($element, $value);
			else $ticketingsystem->where($element, NULL, FALSE);
		}
		if ($join_to_table != NULL) $ticketingsystem->join($join_to_table, $join_on);
		if ($order_by != NULL) $ticketingsystem->order_by($order_by, $order_by_arr);
		$ticketingsystem->limit($offset, $limit);
		if ($like_column != NULL) $ticketingsystem->like($like_column, $like_text, $like_placement);
		return $ticketingsystem->get();
	}
	
	function getTravelOrder($id)
	{
		return $this->getRowsFromTable("travel_orders", "*", "travel_order_id", $id, "destinations", "travel_orders.travel_order_destination_id = destination_id");
	}
	
	function getTravelOrderByStatus($key, $id)
	{
		return $this->getRowsFromTable($key . "_travel_orders", "*", $key . "_travel_order_id", $id);
	}
	
	function getTravelOrdersByStatus($key = NULL, $limit = NULL, $offset = NULL, $user_id = NULL)
	{
		$n = " AND ";
		$or = " OR ";
	
		$status = array(
			"approved" => $this->exists["recommended"]["to"] . $n . $this->exists["approved"]["to"] . $n . $this->not_exists["disapproved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"] . $n . $this->not_exists["expired"]["to"],
			"cancelled" => "(" . $this->exists["reported"]["to"] . $or . $this->exists["expired"]["to"] . ")" . $n . $this->exists["cancelled"]["to"],
			"disapproved" => $this->exists["recommended"]["to"] . $n . $this->exists["disapproved"]["to"] . $n . $this->not_exists["approved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"] . $n . $this->not_exists["expired"]["to"],
			"forapproval" => $this->exists["recommended"]["to"] . $n . $this->not_exists["disapproved"]["to"] . $n . $this->not_exists["approved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"] . $n . $this->not_exists["expired"]["to"],
			"forcancellation" => $this->exists["reported"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["expired"]["to"],
			"forrecommendation" => $this->not_exists["recommended"]["to"] . $n . $this->not_exists["approved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"] . $n . $this->not_exists["disapproved"]["to"] . $n . $this->not_exists["expired"]["to"],
			"expired" => $this->exists["expired"]["to"] . $n . $this->not_exists["cancelled"]["to"]
		);
		
		$status["pending"] = $status["forapproval"] . $or . $status["forcancellation"] . $or . $status["forrecommendation"];
		
		$where = ($key == NULL) ? NULL : $status[$key];
		$role = $this->session->userdata("user_role");
		if ($user_id != NULL && $role != "Administrator")
		{
			$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
			$ticketingsystem->select("*");
			$ticketingsystem->from("ticketingsystem.travel_orders");
			$ticketingsystem->where($where, NULL, FALSE);
			$ticketingsystem->join("ticketingsystem.destinations", "ticketingsystem.travel_orders.travel_order_destination_id = ticketingsystem.destinations.destination_id");
			$ticketingsystem->join("employee_db.employee_acb_info", "employee_db.employee_acb_info.employee_id = ticketingsystem.travel_orders.travel_order_employee_id");
			$ticketingsystem->join("library.position", "library.position.position_id = employee_db.employee_acb_info.position_id");
			if ($role == "Moderator" || ($role == "Approving Official" && $this->Employee->isExecutiveDirector($this->Account->getEmployeeID($user_id)) && $key == "forapproval"))
			{
				$ticketingsystem->where("library.position.supervisor", 1);
				$ticketingsystem->where("library.position.recruitment_type_id", 1);
			}
			else
			{
				$ticketingsystem->where("library.position.supervisor !=", 1);
				$ticketingsystem->where("employee_db.employee_acb_info.unit_id", $this->Account->getUserUnitID($user_id));
			}
			
			$ticketingsystem->order_by("travel_orders.travel_order_id", "desc");
			return $ticketingsystem->get();
		}
		else
			return $this->getRowsFromTable("travel_orders", "*", $where, NULL, "destinations", "travel_orders.travel_order_destination_id = destination_id", "travel_orders.travel_order_id", "desc", $limit, $offset);
	}
	
	function getTripTicketsByStatus($key = NULL, $limit = NULL, $offset = NULL, $user_id = NULL)
	{
		$n = " AND ";
		$or = " OR ";
	
		$status = array(
			"approved" => $this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"] . $n . $this->not_exists["expired"]["tt"],
			"cancelled" => "(" . $this->exists["reported"]["tt"] . $or . $this->exists["expired"]["tt"] . ")" . $n . $this->exists["cancelled"]["tt"],
			"disapproved" => $this->exists["disapproved"]["tt"] . $n . $this->not_exists["approved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"] . $n . $this->not_exists["expired"]["tt"],
			"forapproval" => $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["approved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"] . $n . $this->not_exists["expired"]["tt"],
			"forcancellation" => $this->exists["reported"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["expired"]["tt"],
			"expired" => $this->exists["expired"]["tt"] . $n . $this->not_exists["cancelled"]["tt"]
		);
		
		$status["pending"] = $status["forapproval"] . $or . $status["forcancellation"];
		
		$role = $this->session->userdata("user_role");
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		if ($key != NULL) $ticketingsystem->where($status[$key], NULL, FALSE);
		$ticketingsystem->join("destinations", "trip_tickets.trip_ticket_destination_id = destinations.destination_id");
		$ticketingsystem->join("vehicles", "trip_tickets.trip_ticket_vehicle_id = vehicles.vehicle_id");
		$ticketingsystem->order_by("trip_tickets.trip_ticket_id", "desc");
		if ($limit != NULL) $ticketingsystem->limit($offset, $limit);
		$query = $ticketingsystem->get();
		
		if ($user_id != NULL)
		{
			if (($role == "Approving Official" && !$this->Employee->isExecutiveDirector($this->Account->getEmployeeID($user_id))) || $role == "Administrator") return $query;
			else return NULL;
		}
		else return $query;
	}
	
	function getTravelOrderDuration($id)
	{
		$query = $this->getRowsFromTable("travel_orders", "*", "travel_order_id", $id);
		$row = $query->row();
	
		$dep = $this->Records->humanizeDate($row->travel_order_date_departure);
		$arv = $this->Records->humanizeDate($row->travel_order_date_arrival);
		
		if ($dep == $arv) $date = $dep;
		else {
			$departure = $arrival = array();
			
			$departure["month"] = strtok($dep, " ");
			$departure["day"] = strtok(" ");
			$departure["year"] = strtok(" ");
			$departure["day"] = strtok($departure["day"], ",");
			
			$arrival["month"] = strtok($arv, " ");
			$arrival["day"] = strtok(" ");
			$arrival["year"] = strtok(" ");
			
			if ($departure["month"] == $arrival["month"] && $departure["year"] == $arrival["year"]) $date = $departure["month"] . " " . $departure["day"] . " - " . $arrival["day"]  . " " . $departure["year"];
			else if ($departure["year"] == $arrival["year"]) $date = $departure["month"] . " " . $departure["day"] . " - " . $arv;
			else $date = $dep . " - " . $arv;
		}
		
		return $date;
	}
	
	function getTravelOrderEmployeeID($id)
	{
		$query = $this->getRowsFromTable("travel_orders", "*", "travel_order_id", $id);
		$row = $query->row();
		return $row->travel_order_employee_id;
	}
	
	function getTravelOrders($offset = NULL, $limit = NULL, $month = NULL, $year = NULL)
	{
		if ($month != NULL && $year != NULL) return $this->getRowsFromTable("travel_orders", "*", NULL, NULL, "destinations", "destination_id = travel_orders.travel_order_destination_id", "travel_order_id", "desc", $limit, $offset, "travel_order_date_departure", $year . "-" . $month, "after");
		else return $this->getRowsFromTable("travel_orders", "*", NULL, NULL, "destinations", "destination_id = travel_orders.travel_order_destination_id", "travel_order_id", "desc", $limit, $offset);
	}
	
	function getTravelOrderStatus($id)
	{
		$table = array("cancelled", "expired", "reported", "disapproved", "approved", "recommended");
		$notation = array("Cancelled", "Expired", "For cancellation", "Disapproved", "Approved", "For approval");
		
		// Special case: The travel order has been auto-cancelled.
		if ($this->getQueryCount($this->getRowsFromTable("expired_travel_orders", "*", "expired_travel_order_id = '" . $id . "' AND expired_travel_order_auto = 'true'")) != 0 &&
			$this->getQueryCount($this->getRowsFromTable("cancelled_travel_orders", "*", "cancelled_travel_order_id", $id)) != 0)
			return "Auto-cancelled";
		
		for ($i = 0; $i < 6; $i++)
			if ($this->getQueryCount($this->getRowsFromTable($table[$i] . "_travel_orders", "*", $table[$i] . "_travel_order_id", $id)) != 0) return $notation[$i];
		
		return "For recommendation";
	}
	
	function getTripTicketStatus($id)
	{
		$table = array("cancelled", "expired", "reported", "disapproved", "approved", "approved");
		$notation = array("Cancelled", "Expired", "For cancellation", "Disapproved", "Approved", "For approval");
		
		// Special case: The travel order has been auto-cancelled.
		if ($this->getQueryCount($this->getRowsFromTable("expired_trip_tickets", "*", "expired_trip_ticket_id = '" . $id . "' AND expired_trip_ticket_auto = 'true'")) != 0 &&
			$this->getQueryCount($this->getRowsFromTable("cancelled_trip_tickets", "*", "cancelled_trip_ticket_id", $id)) != 0)
			return "Auto-cancelled";
		
		for ($i = 0; $i < 6; $i++)
			if (($i == 5 && $this->getQueryCount($this->getRowsFromTable($table[$i] . "_trip_tickets", "*", $table[$i] . "_trip_ticket_id", $id)) != 0) || $this->getQueryCount($this->getRowsFromTable($table[$i] . "_trip_tickets", "*", $table[$i] . "_trip_ticket_id", $id)) != 0)
				return $notation[$i];
	}
	
	function getTravelOrdersWithinScope($departure_date, $destination_id)
	{
		$n = " AND ";
	
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("travel_orders");
		$ticketingsystem->where("travel_orders.travel_order_date_departure", $departure_date);
		$ticketingsystem->where("travel_orders.travel_order_destination_id", $destination_id);
		$ticketingsystem->where($this->exists["recommended"]["to"] . $n . $this->exists["approved"]["to"] . $n . $this->not_exists["disapproved"]["to"] . $n . $this->not_exists["cancelled"]["to"] . $n . $this->not_exists["reported"]["to"], NULL, FALSE);
		$ticketingsystem->order_by("travel_orders.travel_order_id", "desc");
		return $ticketingsystem->get();
	}
	
	function getTripTicket($id)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);		
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("vehicles", "vehicle_id = trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->order_by("trip_ticket_id", "desc");
		$ticketingsystem->where("trip_tickets.trip_ticket_id", $id);
		return $ticketingsystem->get();
	}
	
	function getTripTickets($offset = NULL, $limit = NULL, $month = NULL, $year = NULL, $day = NULL, $app = NULL)
	{
		$n = " AND ";
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);		
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("vehicles", "vehicle_id = trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->order_by("trip_ticket_id", "desc");
		if ($day != NULL)
		{
			$ticketingsystem->where("trip_ticket_date_travel", $year . "-" . $month . "-" . $day);
			$ticketingsystem->where($this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"], NULL, FALSE);
		}
		else if ($month != NULL && $year != NULL) $ticketingsystem->like("trip_ticket_date_travel", $year . "-" . $month, "after");
		if ($app != NULL) $ticketingsystem->where($this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"], NULL, FALSE);
		if ($offset != NULL) $ticketingsystem->limit($offset, $limit);
		return $ticketingsystem->get();
	}
	
	function getUpcomingTrips($offset = NULL, $limit = NULL)
	{
		$n = " AND ";
		
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);		
		$ticketingsystem->select("*");
		$ticketingsystem->from("trip_tickets");
		$ticketingsystem->where($this->exists["approved"]["tt"] . $n . $this->not_exists["disapproved"]["tt"] . $n . $this->not_exists["cancelled"]["tt"] . $n . $this->not_exists["reported"]["tt"], NULL, FALSE);
		$ticketingsystem->where("trip_ticket_date_travel >= ", date("Y-m-d"));
		$ticketingsystem->join("destinations", "destination_id = trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("vehicles", "vehicle_id = trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->order_by("trip_ticket_date_travel", "desc");
		if ($offset != NULL) $ticketingsystem->limit($offset, $limit);
		return $ticketingsystem->get();
	}
	
	function hasDirector($query)
	{	
		foreach ($query->result() as $row)
			if ($this->Employee->isDirector($row->travel_order_employee_id)) return TRUE;
			
		return FALSE;
	}
	
	// Options are: 1) With seconds included in the result, 2) Seconds are excluded in the result.
	function humanizeDate($date, $option = 1)
	{
		$year = strtok($date, "-");
		$month = strtok("-");
		$day = strtok(strtok("-"), " ");
		$hours = strtok (strtok(" "), ":");
		$minutes = strtok(":");
		$seconds = strtok(":");
		
		$humanized_month = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		$data = $humanized_month[$month - 1] . " " . ltrim($day, "0") . ", $year";
		
		$meridian = "PM";
		
		if ($hours > 12) $hours /= 2;
		else $meridian = "AM";
		
		if ($hours != FALSE)
		{
			if ($option == 1) $data = $data . " " . $hours . ":" . $minutes . ":" . $seconds . " " . $meridian;
			else $data = $data . " " . $hours . ":" . $minutes . " " . $meridian;
		}
		
		return $data;
	}
	
	function insertToTable($table, $array, $user = NULL, $action = NULL, $recepient_id = NULL, $document_id = NULL, $object = NULL)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->insert($table, $array);
	}
	
	function retrieveExistence($id, $key)
	{
		if ($key == "travel orders") $key = "travel_order";
		else if ($key == "trip tickets") $key = "trip_ticket";
	
		return array(
			"cancelled" => $this->getQueryCount($this->getRowsFromTable("cancelled_" . $key . "s", "*", "cancelled_" . $key . "_id", $id)),
			"reported" => $this->getQueryCount($this->getRowsFromTable("reported_" . $key . "s", "*", "reported_" . $key . "_id", $id)),
			"approved" => $this->getQueryCount($this->getRowsFromTable("approved_" . $key . "s", "*", "approved_" . $key . "_id", $id)),
			"disapproved" => $this->getQueryCount($this->getRowsFromTable("disapproved_" . $key . "s", "*", "disapproved_" . $key . "_id", $id)),
			"recommended" => $this->getQueryCount($this->getRowsFromTable("recommended_" . $key . "s", "*", "recommended_" . $key . "_id", $id))
		);
	}
	
	function searchTravelOrders($query, $limit = NULL, $offset = NULL)
	{
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("ticketingsystem.travel_orders");
		$ticketingsystem->join("ticketingsystem.destinations", "ticketingsystem.destinations.destination_id = ticketingsystem.travel_orders.travel_order_destination_id");
		$ticketingsystem->join("employee_db.employee", "employee_db.employee.employee_id = ticketingsystem.travel_orders.travel_order_employee_id", "left");
		$ticketingsystem->order_by("ticketingsystem.travel_orders.travel_order_id", "desc");
		if ($offset != NULL) $ticketingsystem->limit($offset, $limit);
		
		$query = str_replace("%20", " ", $query);
		$string = strtok($query, " ");
		$q = array();
		
		while ($string != NULL)
		{
			$q[] = $string;
			$string = strtok(" ");
		}
		
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		
		foreach ($q as $text)
		{
			if (strpos($text, "-") == 4) $ticketingsystem->where("travel_order_id", $text);
			else
			{
				$ismonth = FALSE;
				
				if (in_array($text, $months))
				{
					$text = str_pad(array_search($text, $months) + 1, 2, "0", STR_PAD_LEFT);
					$ismonth = TRUE;
				}
			
				if ($ismonth == TRUE)
				{
					$ticketingsystem->like("travel_order_date_departure", $text);
					$ticketingsystem->or_like("travel_order_date_arrival", $text);
				}
				else
				{
					$ticketingsystem->like("travel_order_id", $text);
					$ticketingsystem->or_like("travel_order_date_departure", $text);
					$ticketingsystem->or_like("travel_order_date_arrival", $text);
					$ticketingsystem->or_like("destination_name", $text);
					$ticketingsystem->or_like("first", $text);
					$ticketingsystem->or_like("middle", $text);
					$ticketingsystem->or_like("last", $text);
					$ticketingsystem->or_like("travel_order_purpose", $text);
					$ticketingsystem->or_like("travel_order_perdiems", $text);
					$ticketingsystem->or_like("travel_order_cashadvance", $text);
					$ticketingsystem->or_like("travel_order_appropriation", $text);
				}
			}
		}
		return $ticketingsystem->get();
	}
	
	function searchTripTickets($query, $limit = NULL, $offset = NULL)
	{
		$this->load->helper('file');
		$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
		$ticketingsystem->select("*");
		$ticketingsystem->from("ticketingsystem.trip_tickets");
		$ticketingsystem->join("ticketingsystem.destinations", "ticketingsystem.destinations.destination_id = ticketingsystem.trip_tickets.trip_ticket_destination_id");
		$ticketingsystem->join("ticketingsystem.vehicles", "ticketingsystem.vehicles.vehicle_id = ticketingsystem.trip_tickets.trip_ticket_vehicle_id");
		$ticketingsystem->join("employee_db.employee", "employee_db.employee.employee_id = ticketingsystem.trip_tickets.trip_ticket_driver_id", "left");
		$ticketingsystem->order_by("ticketingsystem.trip_tickets.trip_ticket_id", "desc");
		if ($limit != NULL) $ticketingsystem->limit($offset, $limit);
		
		$query = str_replace("%20", " ", $query);
		$string = strtok($query, " ");
		$q = array();
		
		while ($string != NULL)
		{
			$q[] = $string;
			$string = strtok(" ");
		}
		
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		
		foreach ($q as $text)
		{
			if (strpos($text, "-") == 4) $ticketingsystem->where("trip_ticket_id", $text);
			else
			{
				$ismonth = FALSE;
				
				if (in_array($text, $months))
				{
					$text = str_pad(array_search($text, $months) + 1, 2, "0", STR_PAD_LEFT);
					$ismonth = TRUE;
				}
			
				if ($ismonth == TRUE) $ticketingsystem->like("trip_ticket_date_travel", $text);
				else
				{
					$ticketingsystem->like("trip_ticket_id", $text);
					$ticketingsystem->or_like("trip_ticket_date_travel", $text);
					$ticketingsystem->or_like("destination_name", $text);
					$ticketingsystem->or_like("first", $text);
					$ticketingsystem->or_like("middle", $text);
					$ticketingsystem->or_like("last", $text);
					$ticketingsystem->or_like("trip_ticket_purpose", $text);
				}
			}
		}
		return $ticketingsystem->get();
	}
}

/* End of file records.php */
/* Location: ./application/models/records.php */

?>