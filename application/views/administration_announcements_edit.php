<?php

/* View:		Edit Announcements
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Allows the user to compose an announcement.
 */

include("header.php");

$role = $this->session->userdata("user_role");

$row = $query->row();

if ($role == "Administrator") echo anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration &rarr; Announcement &rarr; Edit &rarr; ' . $row->announcement_id, 1, "class=\"administration\"");

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
	if ($current_filter == "index" || $current_filter == "change") $current_filter = "administration";
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

echo validation_errors();

echo form_open("administration/announcements/edit/" . $row->announcement_id. $row->announcement_id, array("id" => "myform"));
	echo form_fieldset("Announcement");
		echo $this->table->add_row("Announcement:", form_input("announcement", $row->announcement_proper));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Submit Announcement');
	echo "</div>";
echo form_close();

include("footer.php");

/* End of file administration_announcements_edit.php */
/* Location: ./application/controllers/administration_announcements_edit.php */

?>
 