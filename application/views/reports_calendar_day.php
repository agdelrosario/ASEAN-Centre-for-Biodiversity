<?php

/* View:		Calendar
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	The calendar.
 */

include("header.php");

$role = $this->session->userdata("user_role");

echo heading('Reports &rarr; Calendar &rarr; ' . $year . " &rarr; " . $months[$month] . " &rarr; " . ltrim($day, "0"), 1, "class=\"reports\"");

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

echo "<div id=\"documents\">";
	if ($query->num_rows != 0)
	{
		echo $this->table->set_heading("Destination", "Purpose", "Driver", "Plate Number", "ID");
		foreach($query->result() as $row)
			echo $this->table->add_row($row->destination_name, $row->trip_ticket_purpose, $this->Employee->getName($row->trip_ticket_driver_id), $row->vehicle_platenum, anchor("triptickets/document/" . $row->trip_ticket_id, $row->trip_ticket_id));
		echo $this->table->generate();
	}
	else echo "<p>Sorry, there are no trips that correspond to your request at the moment.</p>";
echo "</div>";

if ($total > 0)
{
	echo "<div id=\"pagination\">";
		echo "Total: " . $total;
	echo "</div>";
}

include("footer.php");

/* End of file reports_calendar_day.php */
/* Location: ./application/views/reports_calendar_day.php */

?>