<?php

/* View:		Administration
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the reports.
 */

include("header.php");

$role = $this->session->userdata("user_role");

if ($role == "Administrator") echo anchor("administration/reasons/add", "Add New Reason", "class=\"linker\" id=\"add\"") . anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration', 1, "class=\"administration\"");

echo "<div id=\"subnav\">";
	$subnav = array(
		array("url" => "administration", "text" => "Accounts"),
		array("url" => "administration/reasons", "text" => "Reasons")
		//array("url" => "administration/announcements", "text" => "Announcements")
	);

	$current_filter = $this->uri->rsegment(2);
	if ($current_filter == "index" || $current_filter == "account") $current_filter = "administration";
	else $current_filter = "administration/" . $current_filter;

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

	echo form_open("administration/", array("name" => "processes", "id" => "myform"));

	echo $this->table->set_heading("Username", "Employee", "Role", "E-mail Address", "Status", "");
	
	foreach ($query->result() as $row)
		$this->table->add_row($row->user_username, $this->Employee->getName($row->user_employee_id), $row->user_role_name, $row->user_email, ucwords($row->user_status), anchor("administration/change/" . $row->user_id, "Change", "id=\"sublink\""));
	
	echo $this->table->generate();

echo form_close();
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

/* End of file administration.php */
/* Location: ./application/views/administration.php */

?>
 