<?php

/* Class:		Manual
 * Author:		Aletheia Grace del Rosario
 * Date:		April 26, 2012
 * Description:	Displays the Manual and necessary documentation for the users of this system.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manual extends CI_Controller {

	public function index()
	{	
		if ($this->session->userdata("user_logged"))
		{
			$this->load->view("manual");
		}
		else redirect("accounts");
	}
	
}

/* End of file manual.php */
/* Location: ./application/controllers/manual.php */

?>