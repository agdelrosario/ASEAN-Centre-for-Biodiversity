<?php

/* Class:		Pagination
 * Author:		Aletheia Grace del Rosario
 * Date:		May 3, 2011
 * Description:	Loads the configuration of the pagination.
 */
 
$this->load->library("pagination");
$current_filter = $this->uri->rsegment(2);

if ($current_filter == "index") $current_filter = "page";
 
$class = $this->uri->rsegment(1);
 
if (isset($key) && $key == "search")
$config["base_url"] = base_url() . $class . "/" . $current_filter . "/" . $query;
//else if (isset($key) && $key == "employee")
	//$config["base_url"] = base_url() . $class . "/" . ;
else 
	$config["base_url"] = base_url() . $class . "/" . $current_filter;
	
$config["total_rows"] = $data["total"]/10;
$config["per_page"] = 1;
$config["first_link"] = "First";
$config["last_link"] = "Last";
$config["prev_link"] = "&larr;";
$config["next_link"] = "&rarr;";
$config["full_tag_open"] = "Go to: ";
$config["full_tag_close"] = "</div>";

$this->pagination->initialize($config);

/* End of file pagination.php */
/* Location: ./application/controllers/pagination.php */

?>