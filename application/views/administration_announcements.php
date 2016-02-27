<?php

/* View:		Announcements
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the announcements and allows the administrators to compose and edit announcements.
 */

include("header.php");

$role = $this->session->userdata("user_role");

if ($role == "Administrator") echo anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration', 1, "class=\"administration\"");

echo "<div id=\"subnav\">";
	echo "<div id=\"searchbar\">";
		echo form_open("reports/inquire");
			echo form_input(
				array("name" => "search", "id" => "search", "value" => "Search", "maxlength" => "120", "size" => "26",
					"onFocus" => "if (this.value == 'Search') this.value = ''", "onBlur" => "if (this.value == '') this.value = 'Search'")
			);
			echo form_submit("submit", " ");
		echo form_close();
	echo "</div>";
	
	$subnav = array(
		array("url" => "administration", "text" => "Accounts")//,
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

echo "<div id=\"documents\" class=\"lefttext\">";

	echo form_open("administration/", array("name" => "processes", "id" => "myform"));

	if ($total > 0) echo $this->table->set_heading("ID", "Announcement", "Author", "Date", "Status", "");
	else echo "<p>Sorry, there are no announcements that correspond to your request at the moment.</p>";
	
	foreach ($query->result() as $row)
		$this->table->add_row($row->announcement_id, "<p align=\"left\">" . $row->announcement_proper . "</p>", $this->Account->getUsername($row->announcement_user), $this->Records->humanizeDate($row->announcement_date), $this->Account->getAnnouncementStatus($row->announcement_id), anchor("administration/announcements/edit/" . $row->announcement_id, "Edit", "id=\"sublink\""));
	
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
 