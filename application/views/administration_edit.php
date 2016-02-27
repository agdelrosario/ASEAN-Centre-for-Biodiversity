<?php

/* View:		Administration Edit
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Edits the accounts in the database.
 */

include("header.php");

$role = $this->session->userdata("user_role");
$row = $query->row();

if ($role == "Administrator") echo anchor("administration/reasons/add", "Add New Reason", "class=\"linker\" id=\"add\"") . anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration &rarr; Change &rarr; ' . $row->user_username, 1, "class=\"administration\"");

echo "<div id=\"subnav\">";
	
	$subnav = array(
		array("url" => "administration", "text" => "Accounts"),
		array("url" => "administration/reasons", "text" => "Reasons")//,
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

$user_roles = array();

foreach ($roles->result() as $r)
	$user_roles[$r->user_role_id] = $r->user_role_name;
	

if ($row->user_id == $this->session->userdata("user_id")) echo "<p><strong>Notice:</strong> Please edit your own settings at the settings page. You cannot edit your own status and role. If there are no other administrators that you can ask to change your settings, please leave the account as it is; the site requires at least one administrator.</p>";
else echo "<p><strong>Notice:</strong> Sorry, but only the users can edit their own usernames, passwords, and e-mails. If you wish to stop the user from accessing his/her account, you can deactivate their account.</p>";

echo form_open("administration/change/" . $row->user_id, array("id" => "myform"));
	echo form_fieldset("Account Information");
		echo $this->table->add_row("Username:", form_input("username", $row->user_username, "disabled=\"disabled\""));
		echo $this->table->add_row("E-mail Address:", form_input("email", $row->user_email, "disabled=\"disabled\""));
		if ($row->user_id != $this->session->userdata("user_id"))
		{
			echo $this->table->add_row("Account Status:", form_dropdown("status", array("active" => "Active", "inactive" => "Inactive"), $row->user_status));
			echo $this->table->add_row("Account Role:", form_dropdown("role", $user_roles, $row->user_role_id));
		}
		else
		{
			echo $this->table->add_row("Account Status:", form_dropdown("status", array("active" => "Active", "inactive" => "Inactive"), $row->user_status, "disabled=\"disabled\""));
			echo $this->table->add_row("Account Role:", form_dropdown("role", $user_roles, $row->user_role_name, "disabled=\"disabled\""));
		}
		echo $this->table->generate();
	echo form_fieldset_close();
	
	$name = $this->Employee->getName($row->user_employee_id);
	
	if ($name != NULL)
	{
		echo form_fieldset("Employee Information");
			echo $this->table->add_row("Name:", $name);
			echo $this->table->add_row("Position:", $this->Employee->getEmployeePosition($row->user_employee_id));
			echo $this->table->generate();
		echo form_fieldset_close();
	}
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Submit changes');
	echo "</div>";
echo form_close();

include("footer.php");

/* End of file administration_edit.php */
/* Location: ./application/controllers/administration_edit.php */

?>
 