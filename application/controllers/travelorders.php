<?php

/* Class:		TravelOrders
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the Travel Orders page and performs the functions to maintain the Travel Order records.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TravelOrders extends CI_Controller {

	public function index()
	{	
		($this->session->userdata('user_logged')) ? $this->page() : redirect('accounts');
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
		redirect("travelorders/search/" . $trimming);
	}
	
	function search ($query, $page = 0)
	{
		$this->viewByStatus("search", $page, $query);
	}
	
	function viewByStatus($key, $page, $query = NULL)
	{
		if ($this->session->userdata('user_logged'))
		{
			$data["key"] = $key;
			$data["page"] = $page;
			
			if ($key == "search")
			{
				
				$data["query"] = $this->Records->searchTravelOrders($query, $page * 10, 10);
				$data["total"] = $this->Records->countTravelOrderSearchResults($query);
				$data["searchphrase"] = str_replace("%20", " ", $query);
			}
			else
			{
				$data["query"] = $this->Records->getTravelOrdersByStatus($key, $page * 10, 10);
				$data["total"] = $this->Records->countTravelOrders($key);
			}
			
			if ($data["total"] != 0) include("pagination.php");
			
			$data["hasDirector"] = $this->Records->hasDirector($data["query"]);
			
			$this->load->view("travelorders", $data);
			$this->session->set_userdata("current_page", uri_string());
				
			$this->session->set_userdata("current_page", uri_string());
		}
		else redirect('accounts');
	}
	
	function document ($id)
	{
		if ($this->session->userdata("user_logged"))
		{
			$data["query"] = $this->Records->getTravelOrder($id);
			
			if ($data["query"]->num_rows != 0)
			{
				$q = $this->Records->getAssistants($id);
				$q_row = $q->row();
				$data["assistants"] = ($q->num_rows == 0) ? "None" : $q_row->assistant_name;
				
				$this->load->view("travelorders_view", $data);
				
				$this->session->set_userdata("current_page", uri_string());
			}
			else redirect($this->session->userdata('current_page'));
		}
		else redirect("accounts");
	}

	function add()
	{
		$role = $this->session->userdata("user_role");
		
		if ($this->session->userdata("user_logged") && ($role == "Administrator" || $role == "Moderator"))
		{
			$this->load->library("form_validation");
			$this->load->helper("array");
			$this->load->helper("file");
			
			$data["query"] = $this->Employee->loadEmployees();
			$data["query_destination"] = $this->Records->getDestinations();
			
			if (isset($_POST["destination"]))
			{
				$destination = $_POST['destination'];				
				if ($destination == "addnew") $this->form_validation->set_rules("add_destination", "add destination", "required");
			}
			
			$this->form_validation->set_rules("employee", "employee name", "required");
			$this->form_validation->set_rules("destination", "destination", "required");
			$this->form_validation->set_rules("perdiems", "per diems/expenses allowed", "required");
			$this->form_validation->set_rules("cashadvance", "cash advances/allowances allowed", "required");
			$this->form_validation->set_rules("appropriation", "appropriation to which travel should be charged", "required");
			
			$this->form_validation->set_error_delimiters("<div id=\"info\">", "</div>");

			if ($this->form_validation->run() == FALSE) $this->load->view("travelorders_add", $data);
			else
			{
				$user_id = $this->session->userdata("user_id");
				
				if ($destination == "addnew")
				{
					$destination = $data["query_destination"]->num_rows + 1;
					$this->Records->add("destination", array("destination_id" => $destination, "destination_name" => $_POST["add_destination"]));
				}
				
				$departure_date = $_POST['departure_year'] . "-" . $_POST['departure_month'] . "-" . $_POST['departure_day'];
				$arrival_date = $_POST['arrival_year'] . "-" . $_POST['arrival_month'] . "-" . $_POST['arrival_day'];
				
				$datevalid = TRUE;
				if (!checkdate($_POST["departure_month"], $_POST["departure_day"], $_POST["departure_year"]))
				{
					write_file("errors.txt", "departure date$", "a");
					$datevalid = FALSE;
				}
				if (!checkdate($_POST["arrival_month"], $_POST["arrival_day"], $_POST["arrival_year"]))
				{
					write_file("errors.txt", "arrival date$", "a");
					$datevalid = FALSE;
				}
				if ($datevalid && $this->Records->checkDateValidity($departure_date, $arrival_date) == 'invalid')
					write_file("errors.txt", "interval of the dates$", "a");
				
				$string = read_file("errors.txt");
				
				if ($string == "")
				{
					if ($this->Records->checkAddDuplicatesTravelOrder($_POST['employee'], $destination, $departure_date) == 'false')
					{
						$travelorders = $this->Records->getTravelOrders();
						
						$d = array(
							'travel_order_id' => $this->Records->getNewTravelOrderID(date('Y')),
							'travel_order_employee_id' => $_POST['employee'],
							'travel_order_filed' => date('Y') . "-" . date('m') . "-" . date('d'),
							'travel_order_date_departure' => $departure_date,
							'travel_order_date_arrival' => $arrival_date,
							'travel_order_destination_id' => $destination,
							'travel_order_purpose' => $_POST['purpose'],
							'travel_order_perdiems' => $_POST['perdiems'],
							'travel_order_cashadvance' => $_POST['cashadvance'],
							'travel_order_appropriation' => $_POST['appropriation'],
							'travel_order_remarks' => $_POST['remarks']
						);
						
						$this->Records->add("travel order", $d);
						write_file("errors.txt", "");
						
						if ($_POST["assistants"] != "None")
							$this->Records->add("assistant", array("assistant_travel_order_id" => $d["travel_order_id"], "assistant_name" => $_POST["assistants"]));
						
						redirect($this->session->userdata('current_page'));
					}
					else
					{
						write_file("errors.txt", "duplicate$", "a");
						redirect("travelorders/add");
					}
				}
				else redirect("travelorders/add");
			}
		}
		else redirect('accounts');
	}
	
	function process()
	{
		if ($this->session->userdata("user_logged"))
		{
			$arr = $_POST['travel_order'];
			$role = $this->session->userdata['user_role'];
			
			if (isset($_POST['recommend']) && (($role == 'Unit Head' && $this->Account->getUserUnit($user) == $this->Account->getUserUnit($this->getTravelOrderEmployeeID($id))) || $role == 'Administrator' || $role == 'Moderator' || ($role == "Approving Official" && ($this->Account->getUserUnit($user) == $this->Account->getUserUnit($this->getTravelOrderEmployeeID($id))))))
				$this->action('recommend', $arr);
			else if (isset($_POST['approve']) && ($role == 'Approving Official' || $role == 'Administrator'))
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
			for ($i = 0; $i < 10; $i++)
				if (isset($arr[$i])) $this->Records->addTravelOrderByStatus($key, $arr[$i], $this->session->userdata['user_id']);
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
				
				$query = $this->Records->getTravelOrder($id);
				$assistants_query = $this->Records->getAssistants($id);
				$employee_query = $this->Employee->loadEmployees();
				$approved_query = $this->Records->getTravelOrderByStatus('approved', $id);
				$recommended_query = $this->Records->getTravelOrderByStatus('recommended', $id);
				$row = $query->row();
				$assistants = $assistants_query->row();
				$approving_official = $approved_query->row();
				$recommending_official = $recommended_query->row();
				
				$approving_official_role = $this->Account->getUserRole($approving_official->approved_travel_order_user);
				$recommending_official_role = $this->Account->getUserRole($recommending_official->recommended_travel_order_user);
				
				$nature = "travel order";
				
				$appr_name = $recom_name = "";
				
				include("pdf.php");
				
				$title = "<div class=\"title\" align=\"center\"><b>TRAVEL ORDER</b><br />No. " . $id . "</div>";
				
				foreach ($employee_query->result() as $employee)
				{
					if ($employee->employee_id == $row->travel_order_employee_id)
					{
						$asst = ($assistants->num_rows != 0) ? $assistants->assistant_name : "None";
						
						$info_content = "<tr>
							<td width='20%' class='h'>Name:</td>
							<td width='40%' style=''>" . $this->Employee->getName($employee->employee_id) . "</td>
							<td width='18%' class='h'>Date Filed:</td>
							<td width='22%'>" . $this->Records->humanizeDate($row->travel_order_filed) . "</td>
						</tr>
						<tr>
							<td class='h'>Position:</td>
							<td>" . $employee->position . "</td>
							<td class='h'>Div./Sec./Unit:</td>
							<td>" . $employee->acb_unit . "</td>
						</tr>
						<tr>
							<td class='h'>Departure Date:</td>
							<td>" . $this->Records->humanizeDate($row->travel_order_date_departure) . "</td>
							<td class='h'>Official Station:</td>
							<td>ACB Los Ba&ntilde;os</td>
						</tr>
						<tr>
							<td class='h'>Destination:</td>
							<td>" . $row->destination_name . "</td>
							<td class='h'>Arrival Date:</td>
							<td>" . $this->Records->humanizeDate($row->travel_order_date_arrival) . "</td>
						</tr>
						<tr>
							<td class='h'>Purpose of Travel:</td>
							<td colspan='3'>" . $row->travel_order_purpose . "</td>
						</tr>
						<tr>
							<td class='h'>Per Diems/ Expenses Allowed:</td>
							<td colspan='3'>" . $row->travel_order_perdiems . "</td>
						</tr>
						<tr>
							<td class='h' colspan='4'>Cash Advances Allowances allowed:</td>
						</tr>
						<tr>
							<td colspan='4'>" . $row->travel_order_cashadvance . "&nbsp;</td>
						</tr>
						<tr>
							<td class='h'>Assistants or Laborers Allowed:</td>
							<td colspan='3'>" . $asst . "</td>
						</tr>
						<tr>
							<td class='h' colspan='2'>Appropriation to which travel should be charged:</td>
							<td colspan='2'>" . $row->travel_order_appropriation . "</td>
						</tr>
						<tr>
							<td class='h'>Remarks or Special Instructions:</td>
							<td colspan='3'>" . $row->travel_order_remarks . "</td>
						</tr>";
						
						$cert_content = '';
						
						$r_bool = "off";
						
						$apofficial = $this->Account->getEmployeeID($approving_official->approved_travel_order_user);
						$rmofficial = $this->Account->getEmployeeID($recommending_official->recommended_travel_order_user);
						
						// The requesting official is a director.
						if ($this->Employee->isDirector($row->travel_order_employee_id))
						{
							if ($this->Account->getUserRole($approving_official->approved_travel_order_user) == "Administrator")
							{
								$execdir = $this->Employee->getExecutiveDirector();
								$appr_name = $this->Employee->getName($execdir, 1);
								$appr_position = $this->Employee->getEmployeePosition($execdir);
							}
							else
							{
								$appr_name = $this->Employee->getName($apofficial, 1);
								$appr_position = $this->Employee->getEmployeePosition($apofficial);
							}
						}
						// From Finance and Administration.
						else if ($employee->acb_unit == "Finance and Administration")
						{
							if ($this->Account->getUserRole($approving_official->approved_travel_order_user) == "Administrator")
							{
								$fahead_query = $this->Employee->getFAHead();
								$fahead = $fahead_query->row();
								$appr_name = $this->Employee->getName($fahead->employee_id, 1);
								$appr_position = $this->Employee->getEmployeePosition($fahead->employee_id);
							}
							else
							{
								$appr_name = $this->Employee->getName($apofficial, 1);
								$appr_position = $this->Employee->getEmployeePosition($apofficial);
							}
						}
						else
						{
							if ($this->Account->getUserRole($approving_official->approved_travel_order_user) == "Administrator")
							{
								$fahead_query = $this->Employee->getFAHead();
								$fahead = $fahead_query->row();
								$appr_name = $this->Employee->getName($fahead->employee_id, 1);
								$appr_position = $this->Employee->getEmployeePosition($fahead->employee_id);
							}
							else
							{
								$appr_name = $this->Employee->getName($apofficial, 1);
								$appr_position = $this->Employee->getEmployeePosition($apofficial);
							}
							
							if ($this->Account->getUserRole($recommending_official->recommended_travel_order_user) == "Administrator")
							{
								$unithead_query = $this->Employee->getUnitHead($employee->acb_unit);
								$unithead = $unithead_query->row();
								$recom_name = $this->Employee->getName($unithead->employee_id, 1);
								$recom_position = $this->Employee->getEmployeePosition($unithead->employee_id);
							}
							else
							{
								$recom_name = $this->Employee->getName($rmofficial, 1);
								$recom_position = $this->Employee->getEmployeePosition($rmofficial);
							}
						}
					}
				}
				
				$certification = "<b>Certification:</b>
				<p><i>This is to certify that the travel is necessary and is connected with the functions of the Official/ Employee and this Division/Section/Unit.</i></p>
				<table>";
				
				if ($recom_name == "")
				{
					$certification = $certification . "
					<tr>
							<td width='50%' class='h'> </td>
							<td width='50%' class='h'>Approved: *</td>
						</tr>
						<tr>
							<td style='border: 0px'> </td>
							<td class='sig'>" . $appr_name . "</td>
						</tr>
						<tr>
							<td style='border: 0px'> </td>
							<td class='h' style='text-align: center'>" . $appr_position . "</td>
						</tr>";
				}
				else
				{
					$certification = $certification . "
						<tr>
							<td width='48%' class='h'>Recommending Approval:</td>
							<td width='4%' class='h'>&nbsp;</td>
							<td width='48%' class='h'>Approved: *</td>
						</tr>
						<tr>
							<td class='sig'>" . $recom_name . "</td>
							<td class='h'></td>
							<td class='sig'>" . $appr_name . "</td>
						</tr>
						<tr>
							<td class='h' style='text-align: center'>" . $recom_position . "</td>
							<td class='h'></td>
							<td class='h' style='text-align: center'>" . $appr_position . "</td>
						</tr>";
				}
					
				$certification = $certification . "</table>
				<p style='font-size: 8pt; text-indent: 0px;'><i>* Subject to the condition that 1) official/employee concerned has no outsanding cash advance in previous travel(s), 2) he/she shall settle the cash advance that may be given him/her pursuant hereto within thirty (30) days after date of return to perment official station.</i></p>";
				
				$form = "<div id=\"form\">" . br() . "<table width='100%'>" . $info_content . "</table>" . br() . $certification . "</div>";
				
				$body = "<body><center>" . $header . "<hr />" . $title . $form . "</center></body>";
				
				$mpdf = new mPDF();
				$mpdf->WriteHTML("<html>" . $head . $body . "</html>");
				$mpdf->Output("travel_order_" . $id . ".pdf", "I");
			}
			else redirect($this->session->userdata('current_page'));
		}
		else redirect('accounts');
	}
	
	function report ($id, $key = NULL)
	{
		if ($this->session->userdata('user_logged'))
		{
			$status = $this->Records->getTravelOrderStatus($id);
			
			if ($status == "Expired" || $status == "Auto-cancelled" || $status == "Cancelled" || ($status == "For cancellation" && $key == NULL)) redirect($this->session->userdata('current_page'));
			
			if ($key == NULL)
			{
				$this->load->library('form_validation');
				$this->form_validation->set_rules('reason', 'reason', 'required');
				$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

				if ($this->form_validation->run() == FALSE)
				{
					$data['query'] = $this->Records->getTravelOrder($id);
					
					$q = $this->Records->getAssistants($id);
					$q_row = $q->row();
					$data['assistants'] = ($q->num_rows == 0) ? 'None' : $q_row->assistant_name;
					
					$data['reported_errors'] = $this->Records->getReportedErrors();
					$this->load->view('travelorders_report', $data);
				}
				else
				{
					$this->Records->addTravelOrderByStatus("report", $id, $this->session->userdata['user_id'], $_POST['reason'], $_POST['specifics']);
					redirect($this->session->userdata('current_page'));
				}
			}
			else
			{
				$this->Records->deleteFromTable("reported_travel_orders", "reported_travel_order_id", $id);
				
				redirect($this->session->userdata('current_page'));
			}
		}
		else redirect('accounts');
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */

?>