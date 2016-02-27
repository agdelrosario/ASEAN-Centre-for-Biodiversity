<?php

/* View:		Home
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	The homepage.
 */
 
include("header.php");

$role = $this->session->userdata("user_role");
$user_id = $this->session->userdata("user_id");
$tasks = $notifications = array();

echo heading("Home", 1, "class = \"home\"");

echo div_open("", "three-columns");
	echo div_open("first");
		echo div_open();
			echo heading(date("F") . " " . date("Y") . " Calendar", 1);
			echo $this->calendar->generate(date("Y"), date("m"), $cal_data);
			echo anchor("reports/calendar/" . date("Y") . "/" . date("m"), "View comprehensive " . date("F") . " schedule &raquo;", "id=\"linker\"");
		echo div_close();
	echo div_close();
	
	echo div_open("second");
		echo div_open();
			echo heading("Upcoming Trips", 1);
			
			if ($upcoming->num_rows != 0)
			{
				echo $this->table->set_heading("Date", "Destination");
				foreach($upcoming->result() as $row)
					echo $this->table->add_row($this->Records->humanizeDate($row->trip_ticket_date_travel), $row->destination_name);
				echo $this->table->generate();
			}
			else echo p("There are no upcoming trips.");
			
			if ($role == "Administrator" || $role == "Moderator")
				echo anchor("reports/", "View all upcoming trips &raquo;", 'id="linker"');
			else echo anchor(uri_string(), " ", "id=\"linker\"");
		echo div_close();
	echo div_close();
	
	$this->table->clear();
	
	if ($this->session->userdata('user_logged') && $role != 'User')
	{
		echo div_open("third");
			echo div_open();
				echo heading("Tasks", 1);
				
				$notation = array("Recommend", "Approve", "Cancel",);
				$status = array(
					"Recommend" => "forrecommendation",
					"Approve" => "forapproval", 
					"Cancel" => "forcancellation"
				);
				
				for ($i = 0; $i < 5; $i++)
				{
					$stat = ($i > 2) ? $i%3 + 1 : $i%3;
					$total = ($i <= 2) ? $this->Records->countTravelOrders($status[$notation[$stat]], $user_id) : $this->Records->countTripTickets($status[$notation[$stat]], $user_id);
					
					if ($total > 0)
					{
						$task = ($i <= 2) ? $notation[$stat] . " " . $total . " travel order" : $notation[$stat] . " " . $total . " trip ticket";
						if ($total > 1) $task = $task . "s";
						$tasks[] = $task;
					}
				}
				
				if (count($tasks) == 0) echo p("There are no pending tasks for you at the moment.");
				else echo ol($tasks);
				
				if ($forautocancel->num_rows != 0 || $forautocanceltt->num_rows != 0 || $expired->num_rows != 0 || $expiredtt->num_rows != 0)
				{
					if (count($tasks) == 0) echo br();
					
					echo heading("Notifications", 1);
					
					echo "<p><strong>Note:</strong> Once a travel order or a trip ticket passes one week beyond its travel date, it is automatically cancelled. Otherwise, administrators and moderators have the prerogative to cancel the said documents.</p>";
					
					if ($forautocancel->num_rows != 0)
					{	
						$autocancelled = "";
						
						foreach ($forautocancel->result() as $row)
						{
							if ($autocancelled != "") $autocancelled = $autocancelled . ", ";
							$autocancelled = $autocancelled . anchor("travelorders/document/" . $row->travel_order_id, $row->travel_order_id);
						}
						
						$s = ($forautocancel->num_rows > 1) ? "s" : "";
						$verb = ($forautocancel->num_rows > 1) ? "are" : "is";
						
						$notifications[] = $forautocancel->num_rows . " travel order" . $s . " (" . $autocancelled . ") " . $verb . " auto-cancelled";
					}
					
					if ($forautocanceltt->num_rows != 0)
					{	
						$autocancelledtt = "";
						
						foreach ($forautocanceltt->result() as $row)
						{
							if ($autocancelledtt != "") $autocancelledtt = $autocancelledtt . ", ";
							$autocancelledtt = $autocancelledtt . anchor("triptickets/document/" . $row->trip_ticket_id, $row->trip_ticket_id);
						}
						
						$s = ($forautocanceltt->num_rows > 1) ? "s" : "";
						$verb = ($forautocanceltt->num_rows > 1) ? "are" : "is";
						
						$notifications[] = $forautocanceltt->num_rows . " travel order" . $s . " (" . $autocancelledtt . ") " . $verb . " auto-cancelled";
					}
					
					if ($expired->num_rows != 0)
					{
						$exp = "";
						
						/*foreach ($expired->result() as $row)
						{
							if ($exp != "") $exp = $exp . ", ";
							$exp = $exp . anchor("travelorders/document/" . $row->travel_order_id, $row->travel_order_id);
						}*/
						
						$s = ($expired->num_rows > 1) ? "s" : "";
						$verb = ($expired->num_rows > 1) ? "are" : "is";
						
						if ($role == "Moderator" || $role == "Administrator")
							$delto = anchor("travelorders/expired/", "Delete expired travel orders &raquo;");
						else $delto = anchor("travelorders/expired/", "View expired travel orders &raquo;");
						
						$notifications[] = $expired->num_rows . " travel order" . $s . " " . $verb . " expired. " . $delto; // (" . $exp . ")
					}
					
					if ($expiredtt->num_rows != 0)
					{
						$exptt = "";
						
						/*foreach ($expiredtt->result() as $row)
						{
							if ($exptt != "") $exptt = $exptt . ", ";
							$exptt = $exptt . anchor("triptickets/document/" . $row->trip_ticket_id, $row->trip_ticket_id);
						}*/
						
						$s = ($expiredtt->num_rows > 1) ? "s" : "";
						$verb = ($expiredtt->num_rows > 1) ? "are" : "is";
						
						if ($role == "Moderator" || $role == "Administrator")
							$deltt = anchor("triptickets/expired/", "Delete expired trip tickets &raquo;");
						else $deltt = anchor("travelorders/expired/", "View expired travel orders &raquo;");
						
						$notifications[] = $expiredtt->num_rows . " trip ticket" . $s . " " . $verb . " expired. " . $deltt; //(" . $exptt . ") 
					}
					
					echo ol($notifications);
				}
				
				echo anchor(uri_string(), ' ', 'id="linker"');
			echo div_close();
		echo div_close();
	}
echo div_close();

include("footer.php");

/* End of file home.php */
/* Location: ./application/views/home.php */

?>