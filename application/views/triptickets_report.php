<?php

/* View:		Report Trip Tickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 */

include("header.php");

$row = $query->row();

echo heading("Trip Tickets &rarr; " . $row->trip_ticket_id, 1, "class = \"triptickets\"");

echo form_open("triptickets/report/" . $row->trip_ticket_id, array("name" => "processes", "id" => "myform"));
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
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo form_fieldset("Requesting Officials");
		$req = $this->Records->getRowsFromTable("requesting_officials", "*", "requesting_official_trip_ticket_id", $row->trip_ticket_id);
		$reqli = array();
		
		foreach ($req->result() as $requesting_official) $reqli[] = $this->Employee->getName($requesting_official->requesting_official_employee_id);
		
		echo ol($reqli);
	echo form_fieldset_close();
	
	echo form_fieldset("Passengers");
		$pass = $this->Records->getRowsFromTable("passengers", "*", "passenger_trip_ticket_id", $row->trip_ticket_id);
		$passli = array();
		
		foreach ($pass->result() as $passenger) $passli[] = $this->Employee->getName($passenger->passenger_employee_id);
		
		echo ol($passli);
	echo form_fieldset_close();

	$this->table->clear();
	
	echo form_fieldset('Rationale');
		$errors_array = array();
		foreach ($reported_errors->result() as $r)
			$errors_array[$r->reported_error_id] = $r->reported_error_name;
	
		echo $this->table->add_row('Reason:', form_dropdown("reason", $errors_array));
		echo $this->table->add_row('Specifics:', form_input('specifics', ''));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Report Travel Order');
	echo "</div>";
	
echo form_close();

include("footer.php");

/* End of file triptickets_report.php */
/* Location: ./application/views/triptickets_report.php */

?>