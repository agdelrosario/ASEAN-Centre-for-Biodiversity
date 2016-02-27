<?php

/* View:		Calendar
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	The calendar.
 */

include("header.php");

$role = $this->session->userdata("user_role");

echo heading('Reports &rarr; Drivers &rarr; ' . $year . " &rarr; " . $months[$month], 1, "class=\"reports\"");

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

$monthtotal = 0;

echo "<div id=\"documents\">";
	if ($query->num_rows != 0)
	{
		echo $this->table->set_heading("Name", "Number of Trips This Month");
		foreach($query->result() as $row)
		{
			$c = $this->Records->countDriverTrips($row->employee_id, str_pad($month, "2", "0", STR_PAD_LEFT), $year);
			echo $this->table->add_row($this->Employee->getName($row->employee_id), $c);
			
			$monthtotal += $c;
		}
		echo $this->table->generate();
	}
	else echo "<p>Sorry, there are no trips that correspond to your request at the moment.</p>";
echo "</div>";

if ($total > 0)
{
	$m = intval(ltrim($month, "0"));
	if ($m == 1)
	{
		$prev_link = ($year - 1) . "/12";
		$next_link = $year . "/" . ($m + 1);
	}
	else if ($m == 12)
	{
		$prev_link = $year . "/" . ($m - 1);
		$next_link = ($year + 1) . "/1";
	}
	else
	{
		$prev_link = $year . "/" . ($m - 1);
		$next_link = $year . "/" . ($m + 1);
	}
	
	$prev = anchor("reports/drivers/" . $prev_link, "&larr; Previous");
	$next = anchor("reports/drivers/" . $next_link, "Next &rarr;");

	echo "<div id=\"pagination\">";
		echo "<strong>Total</strong>: " . $monthtotal . " &emsp; <strong>Total for " . $year . "</strong>: " . $this->Records->countTripsForYear($year);
		echo "<div id=\"links\">" . $prev . " " . $next . "</div>";
	echo "</div>";
}

include("footer.php");

/* End of file reports_calendar_day.php */
/* Location: ./application/views/reports_calendar_day.php */

?>