<?php

/* View:		Calendar
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	The "big" calendar that views individual months.
 */

include("header.php");

$role = $this->session->userdata("user_role");

echo heading('Reports &rarr; Calendar &rarr; ' . $year . " &rarr; " . $months[$month], 1, "class=\"reports\"");

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
	echo $this->calendar->generate($year, $month, $cal_data);
echo "</div>";

include("footer.php");

/* End of file reports_calendar.php */
/* Location: ./application/views/reports_calendar.php */

?>