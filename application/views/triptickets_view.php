<?php

/* View:		View Trip Tickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays a certain trip ticket and enables the user to perform transactions on the said trip ticket.
 */

include("header.php");

$row = $query->row();

echo heading("Trip Tickets &rarr; " . $row->trip_ticket_id, 1, "class = \"triptickets\"");

$status = $this->Records->getTripTicketStatus($row->trip_ticket_id);

if ($status == "For cancellation")
	$status = anchor($this->session->userdata('current_page'), $status, "title=\"Reason: " . $this->Records->getReason("trip ticket", $row->trip_ticket_id) . ". Specifics: " . $this->Records->getSpecifics("trip ticket", $row->trip_ticket_id) . ".\"");

echo form_open("triptickets/process", array("name" => "processes", "id" => "myform"));
	echo form_fieldset("Trip Information");
		echo $this->table->add_row(
			"Date of Travel:", $this->Records->humanizeDate($row->trip_ticket_date_travel),
			"Date prepared:", $this->Records->humanizeDate($row->trip_ticket_date_prepared)
		);
		echo $this->table->add_row(
			"Destination:", $row->destination_name,
			"Purpose of Trip:", $row->trip_ticket_purpose
		);
		echo $this->table->add_row(
			"Name of Driver:", $this->Employee->getName($row->trip_ticket_driver_id),
			"Plate No.:", $row->vehicle_platenum
		);
		echo $this->table->add_row("Status:", $status);
		echo $this->table->generate();
	echo form_fieldset_close();
	
	$reqli = $passli = array();
	$req = $this->Records->getRowsFromTable("requesting_officials", "*", "requesting_official_trip_ticket_id", $row->trip_ticket_id);
	$pass = $this->Records->getRowsFromTable("passengers", "*", "passenger_trip_ticket_id", $row->trip_ticket_id);
	
	foreach ($req->result() as $requesting_official) $reqli[] = $this->Employee->getName($requesting_official->requesting_official_employee_id);
	foreach ($pass->result() as $passenger) $passli[] = $this->Employee->getName($passenger->passenger_employee_id);
	
	echo $this->table->add_row(form_fieldset("Requesting Officials") . ol($reqli) . form_fieldset_close(), form_fieldset("Passengers") . ol($passli) . form_fieldset_close());
	echo $this->table->set_template(array("table_open" => "<table class='notmuchspace'>"));
	echo $this->table->generate();

	$status = $this->Records->getTripTicketStatus($row->trip_ticket_id);
	$role = $this->session->userdata("user_role");
	
	echo form_input("trip_ticket[]", $row->trip_ticket_id, "class=\"invisible\"");
	
	echo "<div id=\"actions\" align=\"right\">";
		echo form_button("back", "Back", "onclick=\"window.history.back()\"");
		if (($role == "Approving Official" || $role == "Administrator") && $status == "For approval")
		{
			echo form_submit("approve", "Approve");
			echo form_submit("disapprove", "Disapprove");
		}
		else if (($role == "Administrator" || $role == "Moderator") && $status == "For cancellation") echo form_submit("cancel", "Cancel");
		if ($status != "For cancellation" && $status != "Cancelled" && $status != "Auto-cancelled") echo anchor("triptickets/report/" . $row->trip_ticket_id, "Report", "id=\"button\"");
		if ($status == "For cancellation") $report = anchor("travelorders/report/" . $row->trip_ticket_id . "/dismiss", "Dismiss Report", "id=\"button\"");
		if ($status == "Approved" && $role != "Unit Head") echo anchor("triptickets/pdf/" . $row->trip_ticket_id, "PDF", "id=\"button\"");
	echo "</div>";
	
echo form_close();

include("footer.php");

/* End of file triptickets_view.php */
/* Location: ./application/views/triptickets_view.php */

?>