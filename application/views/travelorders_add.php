<?php

/* View:		Add Travel Orders
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2011
 * Description:	Displays a form that will allow the user to add a travel order in the database.
 */
 
include("header.php");

echo heading("Travel Orders &rarr; Add", 1, 'class="travelorders"');

echo "<div id=\"main_content\">";
	echo "<p><strong>Instructions:</strong> Please accomplish the form below with accuracy. <u>Editing of records is not allowed</u> so please be sure of the information you submit. Also, travel orders must be processed (added, recommended, approved, etc.) <u>at least one day before the event</u>. If there are errors, you have to input everything again.</p>";
	
	echo validation_errors();
	
	$string = read_file("errors.txt");
	
	if ($string != NULL)
	{
		$tokens = strtok($string, "$");
		$str = array();
		
		while ($tokens != FALSE)
		{
			$str[] = $tokens;
			$tokens = strtok("$");
		}
		
		foreach ($str as $row)
		{
			if ($row == "duplicates") echo "<div id=\"info\">Sorry, but a duplicate travel order already exists.</div>";
			else echo "<div id=\"info\">Sorry, but the " . $row . " you entered is invalid.</div>";
		}
	}
	
	$days = $emp_names = $destination_list = $years = array();
	
	echo form_open("travelorders/add", array("id" => "myform"));
		echo form_fieldset("Employee Information");
			foreach ($query->result() as $row) $emp_names[$row->employee_id] = $this->Employee->getName($row->employee_id);
			$employee_names_dropdown = form_dropdown("employee", $emp_names, "", "class=\"wider\"");
			echo $this->table->add_row("Name:", array("data" => $employee_names_dropdown, "colspan" => "3"));
			echo $this->table->generate();
		echo form_fieldset_close();
	
		echo $this->table->clear();
	
		echo form_fieldset("Trip Information");
			for ($i = 1; $i <= 31; $i++) $days[$i] = $i;
			for ($i = 2010; $i < date("Y") + 3; $i++) $years[$i] = $i;
			
			$months = array(
				"1" => "January", "2" => "February", "3" => "March", "4" => "April", "5" => "May", "6" => "June",
				"7" => "July", "8" => "August", "9" => "September", "10" => "October", "11" => "November", "12" => "December"
			);
	
			if ($query_destination->num_rows != 0)
				foreach ($query_destination->result() as $row) $destination_list[$row->destination_id] = $row->destination_name;
			
			$destination_list["addnew"] = "+ Add new destination";
			
			if (count($destination_list) == 1) $add_destination_visibility = "visible";
			else $add_destination_visibility = "hidden";
			
			$add_destination = array("name" => "add_destination", "id" => "add_destination", "value" => "Input new destination here", "maxlength" => "60", "class" => $add_destination_visibility, "onFocus" => "this.value = ''");
			$destination_dropdown = form_dropdown("destination", $destination_list, "", "class=\"wider\" onChange=\"display(this.value, 'addnew', 'add_destination')\"");
			$departure_day_dropdown = form_dropdown("departure_day", $days, date('d'), "class=\"day\"");
			$departure_month_dropdown = form_dropdown("departure_month", $months, date('m'), "class=\"month\"");
			$departure_year_dropdown = form_dropdown("departure_year", $years, date('Y'), "class=\"year\"");
			$arrival_day_dropdown = form_dropdown("arrival_day", $days, date('d'), "class=\"day\"");
			$arrival_month_dropdown = form_dropdown("arrival_month", $months, date('m'), "class=\"month\"");
			$arrival_year_dropdown = form_dropdown("arrival_year", $years, date('Y'), "class=\"year\"");
	
			echo $this->table->add_row("Destination:", $destination_dropdown, array("data" => form_input($add_destination), "colspan" => "2"));
			echo $this->table->add_row("Departure:", $departure_day_dropdown . " " . $departure_month_dropdown . " " . $departure_year_dropdown, "Arrival Date:", $arrival_day_dropdown . " " . $arrival_month_dropdown . " " . $arrival_year_dropdown);
			echo $this->table->add_row("Purpose of Travel:", array("data" => form_input("purpose", "", "id=\"add_purpose\" maxlength=\"40\""), "colspan" => "3"));
			echo $this->table->add_row("Per Diems/Expenses allowed:", array("data" => form_input("perdiems", "", "maxlength=\"100\""), "colspan" => "3"));
			echo $this->table->add_row("Cash Advances/Allowances allowed:", array("data" => form_input('cashadvance', "", "maxlength=\"100\""), "colspan" => "3"));
			echo $this->table->add_row("Assistants/Laborers allowed:", array("data" => form_input("assistants", "None", "maxlength=\"200\""), "colspan" => "3"));
			echo $this->table->add_row("Appropriation to which travel should be charged:", array("data" => form_input('appropriation'), "colspan" => "3"));
			echo $this->table->add_row("Remarks/Special Instructions:", array("data" => form_input("remarks", "Return to station upon completion of mission."), "colspan" => "3"));
			echo $this->table->generate();
		echo form_fieldset_close();
	
		echo "<div id=\"traversal\">";
			echo form_button("back", "Cancel", "onclick=\"window.history.back()\"");
			echo "&emsp;";
			echo form_submit("submit", "Submit Travel Order");
		echo "</div>";
	echo form_close();
	
echo "</div>";

include("footer.php");

/* End of file travelorders_add.php */
/* Location: ./application/views/travelorders_add.php */

?>