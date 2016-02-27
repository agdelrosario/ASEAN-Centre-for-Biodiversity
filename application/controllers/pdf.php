<?php

/* Class:		PDF
 * Author:		Aletheia Grace del Rosario
 * Date:		May 3, 2011
 * Description:	Loads the configuration of the PDF.
 */

if ($nature == "travel order");
{	
	$style = "<style type='text/css'>
		body {
			font-family: Arial;
		}
		
		div#header {
			width: 100%;
			padding-right: 100px;
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
			font-family: Arial;
			font-size: 10pt;
			width: 100%;
		}
		
		div#form table td {
			padding-top: 10px;
			padding-bottom: 5px;
			border-bottom: 1px solid #000000;
		}
		
		div#form table td.h {
			font-weight: bold;
			border-bottom: 1px solid #FFFFFF;
		}
		
		div#form table td.sig {
			padding-top: 70px;
			padding-bottom: 5px;
			border-bottom: 1px solid #000000;
			text-align: center;
			font-weight: bold;
			font-size: 12pt;
		}
		
		div#form div.title {
			text-align: center;
			font-size: 12pt;
			width: 100%;
			font-family: Arial;
		}
		
		div#form p {
			text-indent: 70px;
		}
	</style>";

	$head = "<head>" . $style . "</head>";
	$header = "<div id=\"header\">
		<img src=\"" . base_url() . "images/acbLogo.png\" width=\"100\" height=\"75\" style=\"float:left; margin-left: 100px;\" />
		<span class=\"big\">ASEAN Centre for Biodiversity</span><br />
		3F, ERDB Bldg. Forestry Campus, College, Laguna 4031, Philippines<br />
		Telefax: +6349 536 2865 ; +6349 536 3989<br /><br />
		contact.us@aseanbiodiversity.org | http://www.aseanbiodiversity.org/
	</div>";
}

/* End of file pdf.php */
/* Location: ./application/controllers/pdf.php */

?>