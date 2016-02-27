<?php

/* View:		Reports
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the reports.
 */

include("header.php");

$role = $this->session->userdata("user_role");

echo heading('Reports &rarr; Employee', 1, "class=\"reports\"");

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
		echo $this->table->set_heading("Name", "Position", "Unit");
		foreach($query->result() as $row)
			echo $this->table->add_row(anchor("reports/employee/document/" . $row->employee_id, $this->Employee->getName($row->employee_id)), $row->position, $row->acb_unit);
		echo $this->table->generate();
	}
	else echo "<p>Sorry, there are no upcoming trips at the moment.</p>";

echo "</div>";

if ($total > 0)
{
	echo "<div id=\"pagination\">";
		echo "Total: " . $total;
		$links = $this->uri->total_segments();
		if (isset($searchphrase) && ((is_numeric($searchphrase) && $page == 0) || (strpos($searchphrase, "-") !== FALSE && $page == 0))) $links = $this->uri->total_segments() + 1;
		echo "<div id=\"links\">" . $this->pagination->create_links($links) . "</div>";
	echo "</div>";
}

include("footer.php");

/* End of file reports.php */
/* Location: ./application/views/reports.php */

?>
 