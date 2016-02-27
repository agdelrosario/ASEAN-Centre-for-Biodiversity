<?php

/* Class:		TripTickets
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the Trip Tickets page and performs the functions to maintain the Trip Ticket records.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TripTickets extends CI_Controller {

	public function index()
	{	
		if ($this->session->userdata('user_logged')) $this->page();
		else redirect('accounts');
	}
	
	function page($page = 0)
	{
		$this->viewByStatus(NULL, $page);
	}
	
	function pending($page = 0)
	{
		$this->viewByStatus("pending", $page);
	}
	
	function approved($page = 0)
	{
		$this->viewByStatus("approved", $page);
	}
	
	function cancelled($page = 0)
	{
		$this->viewByStatus("cancelled", $page);
	}
	
	function disapproved($page = 0)
	{
		$this->viewByStatus("disapproved", $page);
	}
	
	function expired($page = 0)
	{
		$this->viewByStatus("expired", $page);
	}
	
	function inquire ()
	{
		$trimming = trim($_POST["search"], ",");
		redirect("triptickets/search/" . $trimming);
	}
	
	function search ($query, $page = 0)
	{
		$this->viewByStatus("search", $page, $query);
	}
	
	function viewByStatus($key, $page, $query = NULL)
	{
		if ($this->session->userdata('user_logged'))
		{
			if ($key == "search")
			{
				$data["searchphrase"] = $query;
				$data["query"] = $this->Records->searchTripTickets($query, $page * 10, 10);
				$data["total"] = $this->Records->countTripTicketSearchResults($query);
			}
			else
			{
				$data["query"] = $this->Records->getTripTicketsByStatus($key, $page * 10, 10);
				$data["total"] = $this->Records->countTripTickets($key);
			}
			
			if ($data["total"] != 0) include("pagination.php");
			$data["key"] = $key;
			
			$this->load->view("triptickets", $data);
			$this->session->set_userdata("current_page", uri_string());
		}
		else redirect('accounts');
	}
	
	function document ($id)
	{
		if ($this->session->userdata("user_logged"))
		{
			$data["query"] = $this->Records->getTripTicket($id);
			
			if ($data["query"]->num_rows != 0)
			{
				$this->load->view("triptickets_view", $data);
				$this->session->set_userdata("current_page", uri_string());
			}
			else redirect($this->session->userdata('current_page'));
		}
		else redirect("accounts");
	}

	function add($invalid = "false")
	{
		$role = $this->session->userdata("user_role");
		
		if ($this->session->userdata("user_logged") && ($role == "Administrator" || $role == "Moderator"))
		{
			$this->load->library("form_validation");
			$this->load->helper("array");
			$this->load->helper("file");
			
			$data["invalid"] = $invalid;
			$data["query_travelorders"] = $this->Records->getTravelOrders();
			
			if (!isset($_POST["page2"]))
			{			
				$data["query_destination"] = $this->Records->getDestinations();
				$data["drivers"] = $this->Employee->getDrivers();
				$data["platenumbers"] = $this->Records->getPlateNumbers();
				
				if (isset($_POST["platenumber"]))
				{
					$platenumber = $_POST["platenumber"];				
					if ($platenumber == "addnew") $this->form_validation->set_rules("add_platenumber", "add plate number", "required");
				}
				
				$this->form_validation->set_rules("purpose", "purpose", "required");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");

				if ($this->form_validation->run() == FALSE) $this->load->view("triptickets_add", $data);
				else
				{
					$user_id = $this->session->userdata("user_id");
				
					if ($platenumber == "addnew")
					{
						$platenumber = $data["platenumbers"]->num_rows + 1;
						$this->Records->add("plate number", array("vehicle_id" => $platenumber, "vehicle_platenum" => $_POST["add_platenumber"]));
					}
					
					$departure_date = $_POST["departure_year"] . "-" . $_POST["departure_month"] . "-" . $_POST["departure_day"];
					$data["employees"] = $this->Records->getTravelOrdersWithinScope($departure_date, $_POST["destination"]);
					
					if ($data["employees"]->num_rows != 0)
					{
						write_file("temp.txt", $departure_date . "$" . $_POST["destination"] . "$" . $_POST["driver"] . "$" . $platenumber . "$" . $_POST["purpose"]);
						$this->load->view("triptickets_add2", $data);
					}
					else redirect('triptickets/add/true');
				}
			}
			else
			{
				$data["query"] = $this->Employee->loadEmployees();
				
				$this->form_validation->set_rules("passengers", "passengers", "required");
				$this->form_validation->set_rules("requesting_officials", "requesting_officials", "required");
				$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");

				$string = read_file("temp.txt");
				
				$depdate = strtok($string, "$");
				$dest = strtok("$");
				$driver = strtok("$");
				$platenumber = strtok("$");
				$purpose = strtok("$");
				
				if ($this->form_validation->run() == FALSE)
				{	
					$data["employees"] = $this->Records->getTravelOrdersWithinScope($depdate, $dest);
					$this->load->view("triptickets_add2", $data);
				}
				else
				{
					$user_id = $this->session->userdata("user_id");
					
					$triptickets = $this->Records->getTripTickets();
					
					$d = array(
						"trip_ticket_id" => $this->Records->getNewTripTicketID(date("Y")),
						"trip_ticket_date_prepared" => date("Y") . "-" . date("m") . "-" . date("d"),
						"trip_ticket_date_travel" => $depdate,
						"trip_ticket_driver_id" => $driver,
						"trip_ticket_destination_id" => $dest,
						"trip_ticket_vehicle_id" => $platenumber,
						"trip_ticket_purpose" => $purpose
					);
					
					$this->Records->add("trip ticket", $d);
					
					foreach ($_POST["requesting_officials"] as $row)
						$this->Records->add("requesting official", array("requesting_official_employee_id" => $this->Records->getTravelOrderEmployeeID($row), "requesting_official_travel_order_id" => $row, "requesting_official_trip_ticket_id" => $d["trip_ticket_id"]));
					
					foreach ($_POST["passengers"] as $row)
						$this->Records->add("passenger", array("passenger_employee_id" => $this->Records->getTravelOrderEmployeeID($row), "passenger_travel_order_id" => $row, "passenger_trip_ticket_id" => $d["trip_ticket_id"]));
					
					redirect($this->session->userdata('current_page'));
				}
			}
		}
		else redirect('accounts');
	}
	
	function process()
	{
		if ($this->session->userdata("user_logged"))
		{
			$arr = $_POST['trip_ticket'];
			$role = $this->session->userdata['user_role'];
			
			if (isset($_POST['approve']) && ($role == 'Approving Official' || $role == 'Administrator'))
				$this->action('approve', $arr);
			else if (isset($_POST['disapprove']) && ($role == 'Approving Official' || $role == 'Administrator'))
				$this->action('disapprove', $arr);
			else if (isset($_POST['cancel']) && ($role == 'Moderator' || $role == 'Administrator'))
				$this->action('cancel', $arr);
			
			redirect($this->session->userdata('current_page'));
		}
		else redirect('accounts');
	}
	
	function action($key, $arr)
	{
		if (isset($arr))
		{
			for ($i = 0; $i < 10; $i++)
				if (isset($arr[$i]))
					$this->Records->addTripTicketByStatus($key, $arr[$i], $this->session->userdata['user_id']);
		}
		else redirect($this->session->userdata('current_page'));
	}
	
	function pdf ($id)
	{
		if ($this->session->userdata('user_logged'))
		{
			if ($this->Records->getTravelOrderStatus($id) == 'Approved')
			{
				$this->load->library('mpdf');
				$this->load->model('Account');
				
				$query = $this->Records->getTripTicket($id);
				$row = $query->row();
				$approved_query = $this->Records->getTripTicketsByStatus('approved', $id);
				$approw = $approved_query->row();
				
				$multiple_rows = '';
				
				for ($i = 0; $i < 17; $i++) {
					$multiple_rows = $multiple_rows . "<tr>";
					for ($j = 0; $j < 8; $j++) {
						$multiple_rows = $multiple_rows . "<td>&nbsp;</td>";
					}
					$multiple_rows = $multiple_rows . "</tr>";
				}
				
				$reqq = $this->Records->getRowsFromTable("requesting_officials", "*", "requesting_official_trip_ticket_id", $row->trip_ticket_id);
				$passq = $this->Records->getRowsFromTable("passengers", "*", "passenger_trip_ticket_id", $row->trip_ticket_id);
				$req = $pass = "";
				
				foreach ($reqq->result() as $requesting_official)
				{
					if ($req != "") $req = $req . ", ";
					$req = $req . $this->Employee->getName($requesting_official->requesting_official_employee_id);
				}
				foreach ($passq->result() as $passenger)
				{
					if ($pass != "") $pass = $pass . ", ";
					$pass = $pass . $this->Employee->getName($passenger->passenger_employee_id);
				}
						
				if ($approw->approved_trip_ticket_user == 0)
				{
					$fahead_query = $this->Employee->getFAHead();
					$fahead = $fahead_query->row();
					$approving_official = $this->Employee->getName($fahead->employee_id, 1);
					$approving_official_unit = $this->Employee->getEmployeePosition($fahead->employee_id);
				}
				else
				{
					$approving_official = $this->Employee->getName($row->employee_id, 1);
					$approving_official_unit = $this->Employee->getEmployeePosition($row->employee_id);
				}
				
				$html = "
					<head>
						<style type='text/css'>
							body {
								font-family: Arial;
							}
							
							div#header {
								width: 100%;
								padding-right: 70px;
								text-align: center;
								font-size: 7pt;
								font-family: Times New Roman;
							}
							
							div#header .big {
								font-size: 10pt;
								margin: 0px;
								padding: 0px;
							}
							
							div#form, div#form table {
								font-size: 8pt;
								width: 100%;
							}
							
							div#form table td {
								padding-top: 5px;
								padding-bottom: 2px;
								border-bottom: 1px solid #000000;
							}
							
							div#form table td.h {
								font-weight: bold;
								border-bottom: 1px solid #FFFFFF;
							}
							
							div#form div.title {
								text-align: center;
								font-size: 10pt;
							}
							
							div#form div.title .header {
								font-weight: bold;
								font-size: 12pt;
							}
							
							div#sig table, div#gas table {
								font-size: 8pt;
								width: 100%;
								font-weight: normal;
							}
							
							div#sig table td.names {
								padding-top: 10px;
								font-size: 10pt;
								border-bottom: 1px solid #000000;
								text-align: center;
							}
							
							div#sig table th {
								text-align: left;
							}
							
							.c {
								text-align: center;
							}
							
							div#takeoff table {
								font-size: 8pt;
								width: 100%;
								border-spacing: 0px;
							}
							
							div#takeoff td, div#takeoff th {
								border: 1px solid #000000;
								margin: 0px;
							}
							
							div#gas table td.line {
								border-bottom: 1px solid #000000;
							}
							
							div#cert table {
								font-size: 8pt;
								border:	1px #000000 solid;
							}
							
							div#cert table td {
								border: 1px solid #000000;
								text-align: left;
							}
							
							div#cert table td.borderless {
								border: 0px;
								text-align: left;
							}
						</style>
					</head>
					<body>
						<center>
							<div id='header'>
								<img src='images/acbLogo.png' width='100' height='75' style='float:left; margin-left: 70px;' />
								<span class='big'>ASEAN Centre for Biodiversity</span><br />
								3F, ERDB Bldg. Forestry Campus, College, Laguna 4031, Philippines<br />
								or P.O. Box 35015 College, Laguna 4031, Philippines<br />
								Tel/Fax: +6349 584-4247; Tel: +6349 536 3989<br />
								Central e-mail: contact.us@aseanbiodiversity.org / Website: http://www.aseanbiodiversity.org/
							</div>
							<hr />
							<div id='form'>
								<div class='title'><span class='header'>DRIVER'S TRIP TICKET</span><br />No. " . $row->trip_ticket_id . "</div>
								<p style='font-size:7pt; text-align: justify;'><b>Instruction:</b> To be prepared in quadruplicate. Original copy to the driver to be returned to Finance and Adminisrative Unit upon completion of the Travel. Duplicate copy to the gasoline station and the triplicate copy to the dispatcher. General Services Section for control while the quadruplicate copy shall be retained by the security for reference.</p>
								<table width='100%'>
									<tr>
										<td width='20%' class='h'>Date of Travel:</td>
										<td width='40%'>" . $this->Records->humanizeDate($row->trip_ticket_date_travel) . "</td>
										<td width='15%' class='h'>Date Prepared:</td>
										<td width='25%'>" . $this->Records->humanizeDate($row->trip_ticket_date_prepared) . "</td>
									</tr>
									<tr>
										<td class='h'>Name of Driver:</td>
										<td>" . $this->Employee->getName($row->trip_ticket_driver_id) . "</td>
										<td class='h'>Plate No.:</td>
										<td>" . $row->vehicle_platenum . "</td>
									</tr>
									<tr>
										<td class='h'>Requesting Officials:</td>
										<td>" . $req . "</td>
										<td class='h'>Destination:</td>
										<td>" . $row->destination_name . "</td>
									</tr>
									<tr>
										<td class='h'>Authorized Passengers:</td>
										<td colspan='3'>" . $pass . "</td>
									</tr>
									<tr>
										<td class='h'>Purpose of Trip:</td>
										<td colspan='3'>" . $row->trip_ticket_purpose . "</td>
									</tr>
								</table>
							</div>
							<div id='sig'>
								<table width='100%'>
									<tr>
										<th width='45%'>REQUESTING PARTY:</th>
										<th width='25%'>&nbsp;</th>
										<th width='30%'>APPROVED:</th>
									</tr>
									<tr>
										<td class='names'>&nbsp;</td>
										<td>&nbsp;</td>
										<td class='names'>" . $approving_official . "</td>
									</tr>
									<tr>
										<td class='c'>&nbsp;</td>
										<td>&nbsp;</td>
										<td class='c'>" . $approving_official_unit . "</td>
									</tr>
								</table>
							</div>
							<div id='takeoff'>
								<table>
									<tr>
										<th colspan='2'>DEPARTURE</th>
										<th colspan='2'>ARRIVAL</th>
										<th colspan='2' class='smaller'>SPEEDOMETER READING</th>
										<th class='smaller' rowspan='2' width='12%'>TOTAL KILOMETERS TRAVELLED</th>
										<th class='smaller' rowspan='2' width='12%'>GASOLINE CONSUMED</th>
									</tr>
									<tr>
										<td width='13%' class='c'>Place</td>
										<td width='10%' class='c'>Time</td>
										<td width='13%' class='c'>Place</td>
										<td width='10%' class='c'>Time</td>
										<td width='10%' class='c'>Departure</td>
										<td width='10%' class='c'>Arrival</td>
									</tr>"
									. $multiple_rows . "
								</table>
							</div>
							<div id='gas'>
								<table width='100%'>
									<tr>
										<td width='24%'>Gasoline Used:</td>
										<td width='24%' class='line'>&nbsp;</td>
										<td width='4%'>&nbsp;</td>
										<td width='24%'>Gear Oil:</td>
										<td width='24%' class='line'>&nbsp;</td>
									</tr>
									<tr>
										<td>Balance in Tank before Trip:</td>
										<td class='line'>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Motor Oil:</td>
										<td class='line'>&nbsp;</td>
									</tr>
									<tr>
										<td>Issued from stock:</td>
										<td class='line'>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Purchase Outside:</td>
										<td class='line'>&nbsp;</td>
									</tr>
									<tr>
										<td>Gasoline:</td>
										<td class='line'>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Balance in Tank after trip:</td>
										<td class='line'>&nbsp;</td>
									</tr>
								</table>
							</div><br />
							<div id='cert'>
								<table width='100%' cellpadding='0' cellspacing='0' border='1px'>
									<tr>
										<td width='50%' rowspan='4'>
											CERTIFICATION<br />
											I/We hereby certify that the vehicle was used on official business as stated aboved.
										</td>
										<td colspan='4' width='50%'>TO: ANTONIO SHELL</td>
									</tr>
									<tr>
										<td colspan='4'>FUEL/LUBRICANTS REQUESTED</td>
									</tr>
									<tr>
										<td width='10%'>EXTRA</td>
										<td width='15%'>UNLEADED</td>
										<td width='10%'>DIESEL</td>
										<td width='15%'>LUB/OTHERS</td>
									</tr>
									<tr>
										<td rowspan='3'>&nbsp;</td>
										<td rowspan='3'>&nbsp;</td>
										<td rowspan='3'>FULL TANK</td>
										<td rowspan='3'>&nbsp;</td>
									</tr>
									<tr>
										<td><br /><center><b>" . $this->Employee->getName($row->trip_ticket_driver_id) . "</b></center></td>
									</tr>
									<tr>
										<td><center>DRIVER</center></td>
									</tr>
									<tr>
										<td><center>Passenger/s Signature</center></td>
										<td colspan='4' rowspan='2'>Garage Time of Departure: ___________<br />Garage Time of Arrival: ___________</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td rowspan='2' colspan='4'><br /><center>Signature of Security on Duty</center></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
									</tr>
								</table>
							</div>
						</center>
					</body>";
				
				$mpdf = new mPDF();
				$mpdf->WriteHTML("<html>" . $html . "</html>");
				$mpdf->Output("trip_ticket_" . $row->trip_ticket_id . ".pdf", "I");
			}
			else redirect($this->session->userdata('current_page'));
		}
		else redirect('accounts');
	}
	
	function report ($id, $key = NULL)
	{
		if ($this->session->userdata('user_logged'))
		{
			$status = $this->Records->getTripTicketStatus($id);
			
			if ($status == "Expired" || $status == "Auto-cancelled" || $status == "Cancelled" || ($status == "For cancellation" && $key == NULL)) redirect($this->session->userdata('current_page'));
			
			if ($key == NULL)
			{
				$this->load->library('form_validation');
				$this->form_validation->set_rules('reason', 'reason', 'required');
				$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

				if ($this->form_validation->run() == FALSE)
				{
					$data['query'] = $this->Records->getTripTicket($id);
					$data['reported_errors'] = $this->Records->getReportedErrors();
					$this->load->view('triptickets_report', $data);
				}
				else
				{
					$this->Records->addTripTicketByStatus("report", $id, $this->session->userdata['user_id'], $_POST['reason'], $_POST['specifics']);
					redirect($this->session->userdata('current_page'));
				}
			}
			else if ($key == "dismiss")
			{
				$this->Records->deleteFromTable("reported_trip_tickets", "reported_trip_ticket_id", $id);
				redirect($this->session->userdata('current_page'));
			}
		}
		else redirect('accounts');
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */

?>