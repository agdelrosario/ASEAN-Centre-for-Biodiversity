<?php

/* Class:		Reports
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2012
 * Description:	Displays the various summaries that showcases different data perspectives.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends CI_Controller {

	public function index($page = 0)
	{
		if ($this->session->userdata('user_logged') && ($this->session->userdata("user_role") == "Administrator" || $this->session->userdata("user_role") == "Moderator"))
		{
			$data["upcoming"] = $this->Records->getUpcomingTrips($page * 10, 10);
			$data["total"] = $this->Records->countUpcomingTrips();
			if ($data["total"] != 0) include("pagination.php");
			$this->load->view("reports", $data);
		}
		else redirect('account');
	}
	
	function calendar($year = NULL, $month = NULL, $day = NULL)
	{
		if ($this->session->userdata('user_logged'))
		{
			$months = array(
				"01" => "January",
				"02" => "February",
				"03" => "March",
				"04" => "April",
				"05" => "May",
				"06" => "June",
				"07" => "July",
				"08" => "August",
				"09" => "September",
				"10" => "October",
				"11" => "November",
				"12" => "December"
			);
			
			if ($day == NULL)
			{
				if ($year == NULL || $month == NULL)
				{
					$month = date('m');
					$year = date('Y');
				}
				
				$data["months"] = $months;
				
				$prefs = array (
					'show_next_prev'  => base_url() . "reports/calendar/",
					'next_prev_url'   => base_url() . "reports/calendar/"
				);
			
				$prefs['template'] = '
					{table_open}<table border="0" cellpadding="0" cellspacing="0" id="bigcal">{/table_open}

					{heading_row_start}<tr>{/heading_row_start}

					{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
					{heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
					{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}

					{heading_row_end}</tr>{/heading_row_end}

					{week_row_start}<tr>{/week_row_start}
					{week_day_cell}<td>{week_day}</td>{/week_day_cell}
					{week_row_end}</tr>{/week_row_end}

					{cal_row_start}<tr>{/cal_row_start}
					{cal_cell_start}<td>{/cal_cell_start}

					{cal_cell_content}<a href="{content}" title="View trips departing on {day} of ' . $months[$month] . ', ' . $year . '">{day}</a>{/cal_cell_content}
					{cal_cell_content_today}<span class="highlight"><a href="{content}" title="View trips departing on {day} of ' . $month . ', ' . $year . '">{day}</a></span>{/cal_cell_content_today}

					{cal_cell_no_content}{day}{/cal_cell_no_content}
					{cal_cell_no_content_today}<span class="highlight">{day}</span>{/cal_cell_no_content_today}

					{cal_cell_blank}&nbsp;{/cal_cell_blank}

					{cal_cell_end}</td>{/cal_cell_end}
					{cal_row_end}</tr>{/cal_row_end}

					{table_close}</table>{/table_close}
				';
				
				$data['cal_data'] = array();
				
				$this->load->helper('date');
				$this->load->library('calendar', $prefs);
				$this->load->model('Account');
				
				$query = $this->Records->getTripTickets(NULL, NULL, $month, $year, NULL, TRUE);
				
				foreach ($query->result() as $row)
				{
					$dep = $row->trip_ticket_date_travel;
					
					$str = strtok($dep, '-');
					$ctr = 0;
					
					while ($str != NULL)
					{
						if ($ctr == 2) $day = $str;
						$str = strtok('-');
						$ctr++;
					}
					
					$data['cal_data'][$day] = base_url() . "reports/calendar/" . $year . '/' . $month . '/' . $day;
				}
				
				$data["month"] = $month;
				$data["year"] = $year;
				$data["day"] = $day;
				
				$this->load->view("reports_calendar", $data);
			}
			else
			{
				$data["query"] = $this->Records->getTripTickets(NULL, NULL, $month, $year, $day, TRUE);
				$data["total"] = $data["query"]->num_rows;
				$data["months"] = $months;
				$data["month"] = $month;
				$data["year"] = $year;
				$data["day"] = $day;
				
				$this->load->view("reports_calendar_day", $data);
			}
		}
		else redirect('account');
	}
	
	function employee ($page = 0, $id = NULL)
	{
		if ($this->session->userdata('user_logged') && ($this->session->userdata("user_role") == "Administrator" || $this->session->userdata("user_role") == "Moderator"))
		{
			if (is_numeric($page))
			{
				$data["query"] = $this->Employee->loadEmployees($page * 10, 10);
				$q = $this->Employee->loadEmployees();
				$data["total"] = $q->num_rows;
				if ($data["total"] != 0) include("pagination.php");
				$this->load->view("reports_employee", $data);
			}
			else if ($page == "document")
			{
				$data["to"] = $this->Employee->getTravelOrders($id);
				$data["tt"] = $this->Employee->getTripTickets($id);
				$data["id"] = $id;
				$this->load->view("reports_employee_document", $data);
			}
		}
		else redirect('account');
	}
	
	function drivers ($year = NULL, $month = NULL)
	{
		if ($this->session->userdata('user_logged') && ($this->session->userdata("user_role") == "Administrator" || $this->session->userdata("user_role") == "Moderator"))
		{
			if ($year == NULL || $month == NULL)
			{
				$year = date("Y");
				$month = date("m");
			}
			
			$months = array(
				"1" => "January",
				"2" => "February",
				"3" => "March",
				"4" => "April",
				"5" => "May",
				"6" => "June",
				"7" => "July",
				"8" => "August",
				"9" => "September",
				"10" => "October",
				"11" => "November",
				"12" => "December"
			);
			
			$data["months"] = $months;
			$data["month"] = ltrim($month, "0");
			$data["year"] = $year;
			$data["query"] = $this->Employee->getDrivers();
			$data["total"] = $data["query"]->num_rows;
			
			$this->load->view("reports_drivers", $data);
		}
		else redirect('account');
	}
}

/* End of file reports.php */
/* Location: ./application/controllers/reports.php */

?>