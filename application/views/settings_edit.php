<?php

/* View:		Edit Settings
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description: Allows the user to change settings in their account.
 */

include("header.php");

$role = $this->session->userdata("user_role");

$subnav = array(array("url" => "settings", "text" => "Account"));

$current_filter = $this->uri->rsegment(2);
if ($current_filter == "index" || $current_filter == "edit") 
	$current_filter = "settings";
else $current_filter = "settings/" . $current_filter;
	
foreach ($subnav as $row)
	if ($row["url"] == $current_filter)
		echo heading("Settings &rarr; " . $row["text"] . " &rarr; Change " . ucwords($key), 1, "class = \"settings\"");

echo "<div id=\"subnav\">";
	$subnavigation = array();

	for ($i = 0; $i < 1; $i++)
	{
		if ($current_filter == $subnav[$i]["url"]) $subnav[$i]['id'] = "selected";
		else $subnav[$i]['id'] = '';
		
		$subnavigation[$i] = anchor($subnav[$i]["url"], $subnav[$i]["text"], "id=\"" . $subnav[$i]["id"] . "\"");
	}
	
	echo ol($subnavigation);
echo "</div>";

if ($justnew != TRUE) echo validation_errors();

echo form_open("settings/edit/" . $key, array("id" => "myform"));
	echo form_fieldset();
		if ($key == "username")
		{
			echo $this->table->add_row("Old username:", form_input("old_username", $this->session->userdata("user_username"), "disabled=\"disabled\""));
			echo $this->table->add_row("New username:", form_input("new_username", ""));
		}
		else if ($key == "password")
		{
			echo $this->table->add_row("Old password:", form_password("old_password", $this->encrypt->decode($this->session->userdata("user_password")), "disabled=\"disabled\""));
			echo $this->table->add_row("New password:", form_password("new_password", ""));
			echo $this->table->add_row("Confirm new password:", form_password("confirm_new_password", ""));
		}
		else if ($key == "email")
		{
			echo $this->table->add_row("Old email:", form_input("old_email", $this->session->userdata("user_email"), "disabled=\"disabled\""));
			echo $this->table->add_row("New email:", form_input("new_email", ""));
		}
		
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Change ' . ucwords($key));
	echo "</div>";
echo form_close();

include("footer.php");

/* End of file settings_edit.php */
/* Location: ./application/views/settings_edit.php */

?>
 