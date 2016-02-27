<?php

/* View:		Add Account
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Adds another user account.
 */

include("header.php");

$role = $this->session->userdata("user_role");

echo heading('Reports &rarr; Employee &rarr; ' . $this->Employee->getName($id), 1, "class=\"reports\"");

echo "<div id=\"subnav\">";
	
	$subnav = array(
		array("url" => "reports", "text" => "Trips"),
		array("url" => "reports/calendar", "text" => "Calendar"),
		array("url" => "reports/employee", "text" => "Employees"),
		array("url" => "reports/drivers", "text" => "Drivers")
	);

	$current_filter = $this->uri->rsegment(2);
	if ($current_filter == "index" || $current_filter == "page") $current_filter = "reports";
	else $current_filter = "reports/" . $current_filter;

	$subnavigation = array();

	for ($i = 0; $i < count($subnav); $i++)
	{
		if ($current_filter == $subnav[$i]["url"]) $subnav[$i]['id'] = "selected";
		else $subnav[$i]['id'] = '';
		
		$subnavigation[$i] = anchor($subnav[$i]["url"], $subnav[$i]["text"], "id=\"" . $subnav[$i]["id"] . "\"");
	}
	
	echo ol($subnavigation);
echo "</div>";

echo form_open("administration/accounts/add", array("id" => "myform"));
	echo form_fieldset("Employee Information");
		echo $this->table->add_row("Name:", $this->Employee->getName($id));
		echo $this->table->add_row("Position:", $this->Employee->getEmployeePosition($id));
		echo $this->table->add_row("Unit:", $this->Employee->getUnit($id));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	if ($to->num_rows != 0)
	{
		echo form_fieldset("Travel Orders");
			echo "<div id=\"documents\" class=\"scroll\">";
				$this->table->set_heading("Travel Order ID", "Destination", "Purpose", "Date", "Status");
				foreach ($to->result() as $row)
					echo $this->table->add_row(anchor("travelorders/document/" . $row->travel_order_id, $row->travel_order_id), $row->destination_name, $row->travel_order_purpose, $this->Records->getTravelOrderDuration($row->travel_order_id), $this->Records->getTravelOrderStatus($row->travel_order_id));
				echo $this->table->generate();
			echo "</div>";
		echo form_fieldset_close();
	}
	else echo "<p>This employee has no travel orders.</p>";
	
	if ($tt->num_rows != 0)
	{
		echo form_fieldset("Trip Tickets");
			echo "<div id=\"documents\" class=\"scroll\">";
				$this->table->set_heading("Trip Order ID", "Destination", "Purpose", "Date", "Status");
				foreach ($tt->result() as $row)
					echo $this->table->add_row(anchor("triptickets/document/". $row->trip_ticket_id, $row->trip_ticket_id), $row->destination_name, $row->trip_ticket_purpose, $this->Records->humanizeDate($row->trip_ticket_date_travel), $this->Records->getTripTicketStatus($row->trip_ticket_id));
				echo $this->table->generate();
			echo "</div>";
		echo form_fieldset_close();
	}
	else echo "<p>This employee has no trip tickets.</p>";
	
	echo "<div id=\"actions\" align=\"right\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
	echo "</div>";
echo form_close();

include("footer.php");

/* End of file administration_account_add.php */
/* Location: ./application/controllers/administration_account_add.php */

?>
 