<?php

/* View:		Add Account
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Adds another user account.
 */

include("header.php");

$role = $this->session->userdata("user_role");

if ($role == "Administrator") echo anchor("administration/reasons/add", "Add New Reason", "class=\"linker\" id=\"add\"") . anchor("administration/accounts/add", "Add New Account", "class=\"linker\" id=\"add\""); //anchor("administration/announcements/add", "Compose an Announcement", "class=\"linker\" id=\"add\"") . 

echo heading('Administration &rarr; Account &rarr; Add', 1, "class=\"administration\"");

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

$user_roles = array();

foreach ($roles->result() as $r)
	$user_roles[$r->user_role_id] = $r->user_role_name;

echo validation_errors();

echo form_open("administration/accounts/add", array("id" => "myform"));
	echo form_fieldset("Account Information");
		echo $this->table->add_row("Username:", form_input("username", ""), "Alphanumeric only.");
		echo $this->table->add_row("E-mail Address:", form_input("email", ""));
		echo $this->table->add_row("Account Role:", form_dropdown("role", $user_roles));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo form_fieldset("Employee Information");
		foreach ($query->result() as $row)
			$emp_names[$row->employee_id] = $this->Employee->getName($row->employee_id);
		echo $this->table->add_row("Name:", form_dropdown("employee", $emp_names, "", "class=\"wider\""));
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
 