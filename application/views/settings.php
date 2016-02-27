<?php

/* View:		Settings
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Allows the user to change some of their account's settings.
 */

include("header.php");

$role = $this->session->userdata("user_role");

$subnav = array(
	array("url" => "settings", "text" => "Account")//,
	//array("url" => "settings/messages", "text" => "Messages")
);

$current_filter = $this->uri->rsegment(2);
if ($current_filter == "index" || $current_filter == 'page') 
	$current_filter = "settings";
else $current_filter = "settings/" . $current_filter;
	
foreach ($subnav as $row)
	if ($row["url"] == $current_filter)
		echo heading('Settings &rarr; ' . $row["text"], 1, "class = \"settings\"");

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

echo form_open("settings/edit", array("id" => "myform"));
	echo form_fieldset("Account Information");
		echo $this->table->add_row("Username:", form_input("username", $this->session->userdata("user_username"), "disabled=\"disabled\""), form_submit("change_username", "Change Username"));
		echo $this->table->add_row("Password:", form_password("password", $this->encrypt->decode($this->session->userdata("user_password")), "disabled=\"disabled\""), form_submit("change_password", "Change Password"));
		echo $this->table->add_row("E-mail Address:", form_input("email", $this->session->userdata("user_email"), "disabled=\"disabled\""), form_submit("change_email", "Change E-mail"));
		echo $this->table->add_row("Account Status:", form_input("status", ucwords($this->session->userdata("user_status")), "disabled=\"disabled\""));
		echo $this->table->add_row("Account Role:", form_input("role", $role, "disabled=\"disabled\""));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	$name = $this->Employee->getName($this->session->userdata("user_employee_id"));
	
	if ($name != NULL)
	{
		echo form_fieldset("Employee Information");
			echo $this->table->add_row("Name:", $name);
			echo $this->table->add_row("Position:", $this->Employee->getEmployeePosition($this->session->userdata("user_employee_id")));
			echo $this->table->generate();
		echo form_fieldset_close();
	}
echo form_close();

include("footer.php");

/* End of file settings.php */
/* Location: ./application/views/settings.php */

?>
 