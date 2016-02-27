<?php

/* View:		Report Travel Orders
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2011
 * Description:	Displays series of forms that will allow the user to report a travel order.
 */
 
include("header.php");

$row = $query->row();

echo heading('Travel Orders &rarr; Report &rarr; ' . $row->travel_order_id, 1, 'class="travelorders"');

echo form_open('travelorders/report/' . $row->travel_order_id, array("id" => "myform"));

	$e = $this->Employee->loadEmployees();
	
	echo form_fieldset('Information Recap');
		foreach ($e->result() as $r)
		{
			if ($r->employee_id == $row->travel_order_employee_id)
			{
				echo $this->table->add_row(
					'Passenger:', anchor('reports/employee/document/' . $r->employee_id, $this->Employee->getName($r->employee_id), "title=\"" . $r->position . " (" . $r->acb_unit . ")\""),
					'Status:', $this->Records->getTravelOrderStatus($row->travel_order_id),
					'Date Filed:', $this->Records->humanizeDate($row->travel_order_filed)
				);
				echo $this->table->add_row(
					'Destination:', $row->destination_name,
					'Purpose:', $row->travel_order_purpose,
					'Departure Date:', $this->Records->getTravelOrderDuration($row->travel_order_id)
				);
				echo $this->table->add_row(
					'Per diems:', $row->travel_order_perdiems,
					'Cash Advance:', $row->travel_order_cashadvance,
					'Appropriation:', $row->travel_order_appropriation
				);
				echo $this->table->add_row('Assistants:', array('data' => $assistants, 'colspan' => '3'));
				echo $this->table->generate();
			}
		}
	echo form_fieldset_close();
	
	$this->table->clear();
	
	echo form_fieldset('Rationale');
		$errors_array = array();
		foreach ($reported_errors->result() as $r)
			$errors_array[$r->reported_error_id] = $r->reported_error_name;
	
		echo $this->table->add_row('Reason:', form_dropdown("reason", $errors_array));
		echo $this->table->add_row('Specifics:', form_input('specifics', ''));
		echo $this->table->generate();
	echo form_fieldset_close();
	
	echo "<div id=\"traversal\">";
		echo form_button('back', "Cancel", "onclick=\"window.history.back()\"");
		echo "&emsp;";
		echo form_submit('submit', 'Report Travel Order');
	echo "</div>";

echo form_close();
	
include("footer.php");

/* End of file travelorders_report.php */
/* Location: ./application/views/travelorders_report.php */

?>