<?php

/* View:		Add Trip Tickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2011
 * Description:	Displays a form that will allow the user to add a trip tickets in the database. (Second page, where the requesting officials and passengers are added.)
 */
 
include("header.php");

echo heading("Trip Tickets &rarr; Add", 1, 'class="triptickets"');

echo "<div id=\"main_content\">";
	if ($query_travelorders->num_rows == 0)
		echo "<p>Sorry, you cannot add a trip ticket without any travel order in the system. " . anchor("travelorders/add", "Add a travel order") . " or <a onClick=\"window.history.back()\">go back to previous window &rarr;</a></p>";
	else
	{
		echo "<p><strong>Instructions:</strong> Please accomplish the form below with accuracy. <u>Editing of records is not allowed</u> so please be sure of the information you submit. Also, trip tickets must be processed (added, approved) <u>at least one day before the event</u>. If there are errors, you have to input everything again.</p>";
		
		echo validation_errors();
		if ($invalid == "true") echo "<div class=\"error\">Sorry, but a duplicate travel order already exists OR the departure/arrival date exceeded the allowed timeframe (Departure: at least one day before the event, Arrival: at least the same date as the departure).</div>";
		
		echo form_open("triptickets/add", array("id" => "myform"));
			echo form_input("page2", "true", "class=\"invisible\"");
			echo form_fieldset("Requesting Officials");
				foreach ($employees->result() as $row)
					echo form_checkbox("requesting_officials[]", $row->travel_order_id) . " " . anchor("travelorders/document/" . $row->travel_order_id,
					$this->Employee->getName($this->Records->getTravelOrderEmployeeID($row->travel_order_id)), "title=\"Click to view travel order\"") . br();
			echo form_fieldset_close();
			
			echo form_fieldset("Passengers");
				foreach ($employees->result() as $row)
					echo form_checkbox("passengers[]", $row->travel_order_id) . " " . anchor("travelorders/document/" . $row->travel_order_id, $this->Employee->getName($this->Records->getTravelOrderEmployeeID($row->travel_order_id)), "title=\"Click to view travel order\"") . br();
			echo form_fieldset_close();
			
			echo "<div id=\"traversal\">";
				echo form_button("back", "Cancel", "onclick=\"window.history.back()\"");
				echo "&emsp;";
				echo form_submit("submit", "Submit");
			echo "</div>";
		echo form_close();
	}
echo "</div>";

include("footer.php");

/* End of file triptickets_add2.php */
/* Location: ./application/views/triptickets_add2.php */

?>