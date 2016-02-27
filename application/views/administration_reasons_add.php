<?php

/* View:		Add Account
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Adds another user account.
 */

include("header.php");

$role = $this->session->userdata("user_role");

if ($role == "Administrator") echo anchor("administration/reasons/add", "Add New Reason", "class=\"linker\" id=\"add\"") . anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration &rarr; Reason &rarr; Add', 1, "class=\"administration\"");

echo "<div id=\"subnav\">";
	$subnav = array(
		array("url" => "administration", "text" => "Accounts"),
		array("url" => "administration/reasons", "text" => "Reasons")//,
		//array("url" => "administration/announcements", "text" => "Announcements")
	);

	$current_filter = $this->uri->rsegment(2);
	if ($current_filter == "index" || $current_filter == "change" || $current_filter == "accounts") $current_filter = "administration";
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

echo form_open("administration/reasons/add", array("id" => "myform"));
	echo form_fieldset("Rationale");
		echo $this->table->add_row("Reason:", form_input("reason", ""), "Alphanumeric only.");
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Submit changes');
	echo "</div>";
echo form_close();

include("footer.php");

/* End of file administration_account_add.php */
/* Location: ./application/controllers/administration_account_add.php */

?>
 