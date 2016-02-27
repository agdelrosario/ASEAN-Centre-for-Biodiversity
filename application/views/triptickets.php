<?php

/* View:		Trip Tickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays and enables the users to make transactions with trip tickets.
 */
 
include("header.php");

$role = $this->session->userdata("user_role");

if ($role == "Administrator" || $role == "Moderator")
	echo anchor("triptickets/add", "Add New", "class=\"linker\" id=\"add\"");

$subnav = array(
	array("url" => "triptickets", "text" => "All"),
	array("url" => "triptickets/pending", "text" => "Pending"),
	array("url" => "triptickets/approved", "text" => "Approved"),
	array("url" => "triptickets/disapproved", "text" => "Disapproved"),
	array("url" => "triptickets/cancelled", "text" => "Cancelled"),
	array("url" => "triptickets/expired", "text" => "Expired")
);

$current_filter = $this->uri->rsegment(2);

if ($current_filter == "index" || $current_filter == 'page') $current_filter = "triptickets";
else $current_filter = "triptickets/" . $current_filter;

if ($key == "search") echo heading('Trip Tickets &rarr; "' . $searchphrase . '"', 1, "class = \"triptickets\"");
else
	foreach ($subnav as $row)
		if ($row["url"] == $current_filter)
			echo heading('Trip Tickets &rarr; ' . $row["text"], 1, "class = \"triptickets\"");

echo "<div id=\"subnav\">";
	echo "<div id=\"searchbar\">";
		echo form_open("triptickets/inquire");
			echo form_input(
				array("name" => "search", "id" => "search", "value" => "Search", "maxlength" => "120", "size" => "26",
					"onFocus" => "if (this.value == 'Search') this.value = ''", "onBlur" => "if (this.value == '') this.value = 'Search'")
			);
			echo form_submit("submit", " ");
		echo form_close();
	echo "</div>";

	$subnavigation = array();

	for ($i = 0; $i < 6; $i++)
	{
		if ($current_filter == $subnav[$i]["url"]) $subnav[$i]['id'] = "selected";
		else $subnav[$i]['id'] = '';
		
		$subnavigation[$i] = anchor($subnav[$i]["url"], $subnav[$i]["text"], "id=\"" . $subnav[$i]["id"] . "\"");
	}
	
	echo ol($subnavigation);
echo "</div>";

echo "<div id=\"documents\">";
	if ($query->num_rows != 0)
	{
		$current_filter = $this->uri->rsegment(2);
		if ($current_filter == "index" || $current_filter == "page") $current_filter = "";
		
		echo form_open("triptickets/process", array("name" => "processes", "id" => "myform"));
			if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search" || $current_filter == "expired")
			{
				echo "<div id=\"actions\">";
					if (($role == "Approving Official" || $role == "Administrator") && $current_filter != "expired")
					{
						echo form_submit("approve", "Approve");
						echo form_submit("disapprove", "Disapprove");
					}
					if ($role == "Administrator" || $role == "Moderator") echo form_submit("cancel", "Cancel");
				echo "</div>";
			}
			
			$check_all = form_checkbox("trip_ticket_all", "trip_ticket_all", FALSE, "onClick=\"check('processes', 'trip_ticket[]', this.checked)\"");
			
			if ($role == "Administrator" || $role == "Moderator" || $role == "Unit Head" || $role == "Approving Official")
			{
				if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search")
					echo $this->table->set_heading($check_all, "ID", "Driver", "Prepared", "Date", "Destination", "Plate Number", "Status", "");
				else if ($current_filter == "expired")
					echo $this->table->set_heading($check_all, "ID", "Driver", "Prepared", "Date", "Destination", "Plate Number", "Status");
				else echo $this->table->set_heading("ID", "Driver", "Prepared", "Date", "Destination", "Plate Number", "Status", "");
			}
			else echo $this->table->set_heading("ID", "Driver", "Prepared", "Date", "Destination", "Plate Number", "Status", "");
			
			foreach ($query->result() as $doc)
			{
				$status = $this->Records->getTripTicketStatus($doc->trip_ticket_id);
				$report = $pdf = "";
				$id = $doc->trip_ticket_id;
				
				if ($role == "Approving Official" || $role == "Moderator" || $role == "Administrator" || $role == 'Unit Head')
				{
					$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
					$ticketingsystem->select("*");
					$ticketingsystem->from("trip_tickets");
					$ticketingsystem->where("trip_ticket_date_travel > ", date("Y-m-d"));
					$ticketingsystem->where("trip_ticket_id", $id);
					$query = $ticketingsystem->get();
					
					if ($status == "Approved" && $role != "Unit Head") $pdf = anchor("triptickets/pdf/" . $id, "PDF", "id=\"sublink\"");
					if ($status != "Cancelled" && $status != "For cancellation") $report = anchor("triptickets/report/" . $id, "Report", "id=\"sublink\"");
					if ($status == "For cancellation")
					{
						$status = anchor($this->session->userdata('current_page'), $status, "title=\"Reason: " . $this->Records->getReason("trip ticket", $id) . ". Specifics: " . $this->Records->getSpecifics("trip ticket", $id) . ".\"");
						$report = anchor("triptickets/report/" . $id . "/dismiss", "Dismiss Report", "id=\"sublink\"");
					}
					if ($status == "Expired" || $status == "Auto-cancelled" || (($status == "Approved" || $status == "Disapproved") && $query->num_rows == 0)) $report = "";
				
					if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search")
						echo $this->table->add_row(
							form_checkbox("trip_ticket[]", $id),
							anchor("triptickets/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->trip_ticket_driver_id, $this->Employee->getName($doc->trip_ticket_driver_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->trip_ticket_date_prepared),
							$this->Records->humanizeDate($doc->trip_ticket_date_travel),
							$doc->destination_name,
							$doc->vehicle_platenum,
							$status,
							$report . " " . $pdf
						);
					else if ($current_filter == "expired")
						echo $this->table->add_row(
							form_checkbox("trip_ticket[]", $id),
							anchor("triptickets/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->trip_ticket_driver_id, $this->Employee->getName($doc->trip_ticket_driver_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->trip_ticket_date_prepared),
							$this->Records->humanizeDate($doc->trip_ticket_date_travel),
							$doc->destination_name,
							$doc->vehicle_platenum,
							$status
						);
					else
						echo $this->table->add_row(
							anchor("triptickets/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->trip_ticket_driver_id, $this->Employee->getName($doc->trip_ticket_driver_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->trip_ticket_date_prepared),
							$this->Records->humanizeDate($doc->trip_ticket_date_travel),
							$doc->destination_name,
							$doc->vehicle_platenum,
							$status,
							$report . " " . $pdf
						);
				}
				else
					echo $this->table->add_row(
						anchor("triptickets/document/" . $id, $id, "title=\"View full travel order.\""),
						anchor("reports/employee/document/" . $doc->trip_ticket_driver_id, $this->Employee->getName($doc->trip_ticket_driver_id), "title=\"Click to view travel activities of employee\""),
						$this->Records->humanizeDate($doc->trip_ticket_date_prepared),
						$this->Records->humanizeDate($doc->trip_ticket_date_travel),
						$doc->destination_name,
							$doc->vehicle_platenum,
						$doc->trip_ticket_purpose,
						$status
					);	
			}
		
			echo $this->table->generate();
		echo form_close();
	}
	else echo "<p>Sorry, there are no trip tickets in the system that correspond to your request.</p>";
echo "</div>";

if ($total != 0)
{
	echo "<div id=\"pagination\">";
		echo "Total: " . $total;
		echo "<div id=\"links\">" . $this->pagination->create_links() . "</div>";
	echo "</div>";
}

include("footer.php");

/* End of file triptickets.php */
/* Location: ./application/views/triptickets.php */

?>