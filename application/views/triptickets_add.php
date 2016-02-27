<?php

/* View:		Add Trip Tickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2011
 * Description:	Displays a form that will allow the user to add a trip tickets in the database.
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
		$days = $destination_list = $years = $drivers_list = $platenumbers_list = array();
		
		echo form_open("triptickets/add", array("id" => "myform"));
			echo form_fieldset("Trip Information");
				for ($i = 1; $i <= 31; $i++) $days[$i] = $i;
				for ($i = 2010; $i < date("Y") + 3; $i++) $years[$i] = $i;
				
				$months = array(
					"1" => "January", "2" => "February", "3" => "March", "4" => "April", "5" => "May", "6" => "June",
					"7" => "July", "8" => "August", "9" => "September", "10" => "October", "11" => "November", "12" => "December"
				);
		
				if ($query_destination->num_rows != 0)
					foreach ($query_destination->result() as $row) $destination_list[$row->destination_id] = $row->destination_name;
				
				$destination_dropdown = form_dropdown("destination", $destination_list, "", "class=\"wider\"");
				
				foreach ($drivers->result() as $row)
					$drivers_list[$row->employee_id] = $this->Employee->getName($row->employee_id);
					
				$driver_dropdown = form_dropdown("driver", $drivers_list, "", "class=\"wider\"");
				
				foreach ($platenumbers->result() as $row)
					$platenumbers_list[$row->vehicle_id] = $row->vehicle_platenum;
					
				$platenumbers_list["addnew"] = "+ Add new plate number";			
				if (count($platenumbers_list) == 1) $add_platenumber_visibility = "visible";
				else $add_platenumber_visibility = "hidden";
				
				$platenumbers_dropdown = form_dropdown("platenumber", $platenumbers_list, "", "class=\"wider\" onChange=\"display(this.value, 'addnew', 'add_platenumber')\"");
				$add_platenumber =  array("name" => "add_platenumber", "id" => "add_platenumber", "value" => "Input new plate number here", "maxlength" => "15", "class" => $add_platenumber_visibility, "onFocus" => "this.value = ''");
				
				$departure_day_dropdown = form_dropdown("departure_day", $days, date('d'), "class=\"day\"");
				$departure_month_dropdown = form_dropdown("departure_month", $months, date('m'), "class=\"month\"");
				$departure_year_dropdown = form_dropdown("departure_year", $years, date('Y'), "class=\"year\"");
				
				echo $this->table->add_row("Destination:", $destination_dropdown, "&emsp; Date of Travel:", $departure_day_dropdown . " " . $departure_month_dropdown . " " . $departure_year_dropdown);
				echo $this->table->add_row("Driver:", $driver_dropdown);
				echo $this->table->add_row("Plate Number:", $platenumbers_dropdown, array("data" => form_input($add_platenumber), "colspan" => "2"));
				echo $this->table->add_row("Purpose:", array("data" => form_input("purpose"), "colspan" => "3"));
				echo $this->table->generate();
			echo form_fieldset_close();
		
			echo "<div id=\"traversal\">";
				echo form_button("back", "Cancel", "onclick=\"window.history.back()\"");
				echo "&emsp;";
				echo form_submit("submit", "Next step");
			echo "</div>";
		echo form_close();
	}
echo "</div>";

include("footer.php");

/* End of file triptickets_add.php */
/* Location: ./application/views/triptickets_add.php */

?>