<?php

/* Class:		Home
 * Author:		Aletheia Grace del Rosario
 * Date:		December 15, 2011
 * Description:	Displays the homepage.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index()
	{		
		if ($this->session->userdata('user_logged'))
		{
			// Routine check to keep records up to date.
			//$this->Records->eliminateExpired(); // (elimination of expired travel orders and trip tickets)
			
			$month = date('M');
			$year = date('Y');
		
			$prefs['template'] = '
				{table_open}<table border="0" cellpadding="0" cellspacing="0" id="minical">{/table_open}

				{heading_row_start}<tr>{/heading_row_start}

				{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
				{heading_title_cell}<th colspan="{colspan}"></th>{/heading_title_cell}
				{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}

				{heading_row_end}</tr>{/heading_row_end}

				{week_row_start}<tr>{/week_row_start}
				{week_day_cell}<td>{week_day}</td>{/week_day_cell}
				{week_row_end}</tr>{/week_row_end}

				{cal_row_start}<tr>{/cal_row_start}
				{cal_cell_start}<td>{/cal_cell_start}

				{cal_cell_content}<a href="{content}" title="View trips departing on {day} of ' . $month . ', ' . $year . '">{day}</a>{/cal_cell_content}
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
			
			$query = $this->Records->getTripTickets(NULL, NULL, date('m'), $year, NULL, TRUE);
			
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
				
				$data['cal_data'][$day] = 'reports/calendar/' . $year . '/' . date('m') . '/' . $day;
			}
			
			$data["forautocancel"] = $this->Records->autoCancelTravelOrders();
			$data["forautocanceltt"] = $this->Records->autoCancelTripTickets();
			$data["expired"] = $this->Records->getExpiredTravelOrders();
			$data["expiredtt"] = $this->Records->getExpiredTripTickets();
			$data["upcoming"] = $this->Records->getUpcomingTrips(5);
			
			$data['announcement'] = $this->retrieveAnnouncement($this->Account->adminAnnouncements()); // Retrieve latest announcement.
			
			$this->load->view('home', $data);
		}
		else redirect('accounts');
	}
	
	function retrieveAnnouncement($query)
	{
		$row = $query->last_row();
		$bullet = " &bullet; ";
	
		// Displaying announcements and restricted operations for the moderators and administrators.
		if ($row != NULL)
		{
			$date = "<span id=\"date\">" . $this->Records->humanizeDate($row->announcement_date, 2) . "</span>";
			$user_role = "<em>" . $this->Account->getUserRole($row->announcement_user) . "</em>";
			
			$announcement = $row->announcement_proper . " &mdash; " . $user_role . $bullet . $date;
			
			if ($this->session->userdata('user_role') == 'Administrator' || $this->session->userdata('user_role') == 'Moderator')
				$announcement = $announcement . $bullet . anchor('administration/announcements/edit/' . $row->announcement_id, 'Edit');
		}
		else
			$announcement = "Welcome to the ACB Ticketing System. Please make sure to attend to pending transactions before logging out.";

		if ($this->session->userdata('user_role') == 'Administrator' || $this->session->userdata('user_role') == 'Moderator')
			$announcement = $announcement . $bullet . anchor('administration/announcements/add/', 'Add new');
		
		return $announcement;
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */

?>