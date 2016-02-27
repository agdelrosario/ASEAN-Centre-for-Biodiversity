<?php

/* Class:		Header
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	The header.
 */

// Defining the current class.
$current_class = $this->uri->rsegment(1);

$links = array(
	array("url" => "home", "text" => "Home"),
	array("url" => "travelorders", "text" => "Travel Orders"),
	array("url" => "triptickets", "text" => "Trip Tickets"),
	array("url" => "reports", "text" => "Reports"),
	array("url" => "administration", "text" => "Administration"),
	array("url" => "settings", "text" => "Settings")
);

$navigation = array();
 
echo doctype();
echo "<html lang=\"en\">";
echo 	"<head>";
echo 		meta('Content-type', 'text/html; charset=utf-8', 'equiv');
echo 		"<title>ACB Ticketing System</title>";
echo		link_tag('css/style.css');
echo		link_tag('css/headings.css');
echo		"<link rel=\"icon\" href=\"" . base_url() . "images/favicon.ico\" />";

// Creating navigation links.
for ($i = 0; $i < 6; $i++)
{
	if ($current_class == $links[$i]["url"] || $current_class == "")
	{
		$links[$i]["id"] = 'current_class';
		
		if ($current_class == '') echo link_tag('css/home.css');
		else if ($current_class == 'travelorders' || $current_class == 'triptickets') echo link_tag('css/records.css');
		else echo link_tag('css/' . $current_class . '.css');
	}
	else $links[$i]["id"] = "";

	if ($links[$i]['url'] == 'administration')
	{
		if ($this->session->userdata('user_logged') && ($this->session->userdata('user_role') == 'Administrator'))
			$navigation[$i] = anchor($links[$i]['url'], $links[$i]['text'], "id=\"" . $links[$i]['id'] . "\"");
	}
	else if ($links[$i]['url'] == 'reports')
	{
		if ($this->session->userdata('user_logged') && ($this->session->userdata('user_role') == 'Administrator' || $this->session->userdata('user_role') == 'Moderator'))
			$navigation[$i] = anchor($links[$i]['url'], $links[$i]['text'], "id=\"" . $links[$i]['id'] . "\"");
	}
	else
		$navigation[$i] = anchor($links[$i]['url'], $links[$i]['text'], "id=\"" . $links[$i]['id'] . "\"");
}

$curpage = $this->uri->rsegment(2);

$this->load->helper("file");
if ($curpage != "add") write_file("errors.txt", "");

echo		"<script src='" . base_url() . "/js/ticketingsystem.js'></script>";
echo 	"</head>";
echo	"<body>";
echo		"<div id=\"placeholder\" class=\"center\">";
echo 			"<div id=\"panel\">";
echo				"<div id=\"navigation\">";
echo					ol($navigation);
echo				"</div>";
echo				"<div id=\"user\">";
echo					"Logged in as <strong>" . $this->session->userdata('user_username') . "</strong>. &emsp; ";

echo					anchor('accounts/logout', 'Logout');
echo				"</div>";
echo			"</div>";
echo			"<div id=\"content\">";

/* End of file header.php */
/* Location: ./application/views/header.php */

?>