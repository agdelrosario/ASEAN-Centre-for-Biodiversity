<?php

/* View:		Travel Orders
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays and enables the users to make transactions with travel orders.
 */

include("header.php");

$role = $this->session->userdata("user_role");

$subnav = array(
	array("url" => "travelorders", "text" => "All"),
	array("url" => "travelorders/pending", "text" => "Pending"),
	array("url" => "travelorders/approved", "text" => "Approved"),
	array("url" => "travelorders/disapproved", "text" => "Disapproved"),
	array("url" => "travelorders/cancelled", "text" => "Cancelled"),
	array("url" => "travelorders/expired", "text" => "Expired")
);

$current_filter = $this->uri->rsegment(2);
if ($current_filter == "index" || $current_filter == 'page') $current_filter = "travelorders";
else $current_filter = "travelorders/" . $current_filter;

if ($role == "Administrator" || $role == "Moderator")
	echo anchor("travelorders/add", "Add New", "class=\"linker\" id=\"add\"");

if ($key == "search") echo heading('Travel Orders &rarr; "' . $searchphrase . '"', 1, "class = \"travelorders\"");
else
	foreach ($subnav as $row)
		if ($row["url"] == $current_filter)
			echo heading('Travel Orders &rarr; ' . $row["text"], 1, "class = \"travelorders\"");

echo "<div id=\"subnav\">";
	echo "<div id=\"searchbar\">";
		echo form_open("travelorders/inquire");
			echo form_input(
				array("name" => "search", "id" => "search", "value" => "Search", "maxlength" => "120", "size" => "26",
					"onFocus" => "if (this.value == 'Search') this.value = ''", "onBlur" => "if (this.value == '') this.value = 'Search'")
			);
			echo form_submit("submit", " ");
		echo form_close();
	echo "</div>";

	$subnavigation = array();

	for ($i = 0; $i < 6; $i++)
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
		$current_filter = $this->uri->rsegment(2);
		if ($current_filter == "index" || $current_filter == "page") $current_filter = "";
		if ($current_filter == "expired") echo "<p><strong>Note:</strong> Administrators and moderators can cancel a travel order until it has expired over a week. After that, it will be automatically cancelled.</p>";
		
		echo form_open("travelorders/process", array("name" => "processes", "id" => "myform"));
			if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search" || $current_filter == "expired")
			{
				echo "<div id=\"actions\">";
					if (($role == "Unit Head" || $role == "Administrator" || ($role == "Moderator" && $hasDirector)) && $current_filter != "expired") echo form_submit("recommend", "Recommend");
					if (($role == "Approving Official" || $role == "Administrator") && $current_filter != "expired")
					{
						echo form_submit("approve", "Approve");
						echo form_submit("disapprove", "Disapprove");
					}
					if (($role == "Administrator" || $role == "Moderator")) echo form_submit("cancel", "Cancel");
				echo "</div>";
			}
			
			$check_all = form_checkbox("travel_order_all", "travel_order_all", FALSE, "onClick=\"check('processes', 'travel_order[]', this.checked)\"");
			
			if ($role == "Administrator" || $role == "Moderator" || $role == "Unit Head" || $role == "Approving Official")
			{
				if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search")
					echo $this->table->set_heading($check_all, "ID", "Name", "Filed", "Date", "Destination", "Purpose", "Status", "");
				else if ($current_filter == "expired")
					echo $this->table->set_heading($check_all, "ID", "Name", "Filed", "Date", "Destination", "Purpose", "Status");
				else echo $this->table->set_heading("ID", "Name", "Filed", "Date", "Destination", "Purpose", "Status", "");
			}
			else echo $this->table->set_heading("ID", "Name", "Filed", "Date", "Destination", "Purpose", "Status");
			
			foreach ($query->result() as $doc)
			{
				$status = $this->Records->getTravelOrderStatus($doc->travel_order_id);
				$report = $pdf = "";
				$id = $doc->travel_order_id;
				
				if ($role == "Approving Official" || $role == "Moderator" || $role == "Administrator" || $role == 'Unit Head')
				{
					$ticketingsystem = $this->load->database("ticketingsystem", TRUE);
					$ticketingsystem->select("*");
					$ticketingsystem->from("travel_orders");
					$ticketingsystem->where("travel_order_date_arrival > ", date("Y-m-d"));
					$ticketingsystem->where("travel_order_id", $id);
					$query = $ticketingsystem->get();
				
					if ($status == "Approved" && $role != 'Unit Head')
						$pdf = anchor("travelorders/pdf/" . $id, "PDF", "id=\"sublink\"");
					if ($status != "Cancelled" && $status != "For cancellation")
						$report = anchor("travelorders/report/" . $id, "Report", "id=\"sublink\"");
					
					if ($status == "For cancellation")
					{
						$status = anchor($this->session->userdata('current_page'), $status, "title=\"Reason: " . $this->Records->getReason("travel order", $id) . ". Specifics: " . $this->Records->getSpecifics("travel order", $id) . ".\"");
						$report = anchor("travelorders/report/" . $id . "/dismiss", "Dismiss Report", "id=\"sublink\"");
					}
					
					if (($status == "Expired" || $status == "Auto-cancelled") || (($status == "Approved" || $status == "Disapproved") && $query->num_rows == 0))
						$report = "";
				
					if ($current_filter == "" || $current_filter == "pending" || $current_filter == "search")
						echo $this->table->add_row(
							form_checkbox("travel_order[]", $id),
							anchor("travelorders/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->travel_order_employee_id, $this->Employee->getName($doc->travel_order_employee_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->travel_order_filed),
							$this->Records->getTravelOrderDuration($doc->travel_order_id),
							$doc->destination_name,
							$doc->travel_order_purpose,
							$status,
							$report . " " . $pdf
						);
					else if ($current_filter == "expired")
						echo $this->table->add_row(
							form_checkbox("travel_order[]", $id),
							anchor("travelorders/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->travel_order_employee_id, $this->Employee->getName($doc->travel_order_employee_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->travel_order_filed),
							$this->Records->getTravelOrderDuration($doc->travel_order_id),
							$doc->destination_name,
							$doc->travel_order_purpose,
							$status
						);
					else
						echo $this->table->add_row(
							anchor("travelorders/document/" . $id, $id, "title=\"View full travel order\""),
							anchor("reports/employee/document/" . $doc->travel_order_employee_id, $this->Employee->getName($doc->travel_order_employee_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->travel_order_filed),
							$this->Records->getTravelOrderDuration($id),
							$doc->destination_name,
							$doc->travel_order_purpose,
							$status,
							$report . " " . $pdf
						);
				}
				else
					echo $this->table->add_row(
						anchor("travelorders/document/" . $id, $id, "title=\"View full travel order.\""),
						anchor("reports/employee/document/" . $doc->travel_order_employee_id, $this->Employee->getName($doc->travel_order_employee_id), "title=\"Click to view travel activities of employee\""),
							$this->Records->humanizeDate($doc->travel_order_filed),
						$this->Records->getTravelOrderDuration($id),
						$doc->destination_name,
						$doc->travel_order_purpose,
						$status
					);	
			}
		
			echo $this->table->generate();
		echo form_close();
	}
	else echo "<p>Sorry, there are no travel orders in the system that correspond to your request.</p>";
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

/* End of file travelorders.php */
/* Location: ./application/views/travelorders.php */

?>
 