<?php

/* View:		View Travel Orders
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays a certain travel order and enables the user to perform transactions on the said travel order.
 */

include("header.php");

$row = $query->row();

echo heading("Travel Orders &rarr; " . $row->travel_order_id, 1, "class = \"travelorders\"");

echo form_open("travelorders/process", array("name" => "processes", "id" => "myform"));

	$e = $this->Employee->loadEmployees();

	foreach ($e->result() as $r)
	{
		if ($r->employee_id == $row->travel_order_employee_id)
		{
			$status = $this->Records->getTravelOrderStatus($row->travel_order_id);
			
			if ($status == "For cancellation")
				$status = anchor($this->session->userdata('current_page'), $status, "title=\"Reason: " . $this->Records->getReason("travel order", $row->travel_order_id) . ". Specifics: " . $this->Records->getSpecifics("travel order", $row->travel_order_id) . ".\"");
			
			echo form_fieldset("Passenger Information");		
				echo $this->table->add_row(
					"Passenger:", anchor("reports/employee/document/" . $r->employee_id, $this->Employee->getName($r->employee_id)),
					"Position/Unit:", $r->position . "/" . $r->acb_unit
				);
				echo $this->table->add_row("Assistants:", $assistants);
				echo $this->table->generate();
			echo form_fieldset_close();
			
			echo form_fieldset("Trip Information");			
				echo $this->table->add_row(
					"Destination:", $row->destination_name,
					"Purpose:", $row->travel_order_purpose,
					"Departure Date:", $this->Records->getTravelOrderDuration($row->travel_order_id)
				);
				echo $this->table->add_row(
					"Per diems:", $row->travel_order_perdiems,
					"Cash Advance:", $row->travel_order_cashadvance,
					"Appropriation:", $row->travel_order_appropriation
				);
				echo $this->table->add_row("Status:", $status);
				echo $this->table->generate();
			echo form_fieldset_close();			
		}
	}

	$status = $this->Records->getTravelOrderStatus($row->travel_order_id);
	$role = $this->session->userdata("user_role");

	echo form_input("travel_order[]", $row->travel_order_id, "class=\"invisible\"");
	
	echo "<div id=\"actions\" align=\"right\">";
		echo form_button("back", "Back", "onclick=\"window.history.back()\"");
		if (($role == "Unit Head" || $role == "Administrator") && $status == "For recommendation") echo form_submit("recommend", "Recommend");
		else if (($role == "Approving Official" || $role == "Administrator") && $status == "For approval")
		{
			echo form_submit("approve", "Approve");
			echo form_submit("disapprove", "Disapprove");
		}
		else if (($role == "Administrator" || $role == "Moderator") && $status == "For cancellation") echo form_submit("cancel", "Cancel");
		if ($status != "For cancellation" && ($status != "Cancelled" && $status != "Auto-cancelled")) echo anchor("travelorders/report/" . $row->travel_order_id, "Report", "id=\"button\"");
		if ($status == "For cancellation") $report = anchor("travelorders/report/" . $row->travel_order_id . "/dismiss", "Dismiss Report", "id=\"button\"");
		if ($status == "Approved" && $role != "Unit Head") echo anchor("travelorders/pdf/" . $row->travel_order_id, "PDF", "id=\"button\"");
	echo "</div>";
echo form_close();


include("footer.php");

/* End of file travelorders_view.php */
/* Location: ./application/views/travelorders_view.php */

?>