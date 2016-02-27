<?php
	echo doctype() . "
		<html lang=\"en\">
			<head>" .
				meta("Content-type", "text/html; charset=utf-8", "equiv") .
				"<title>ASEAN Centre for Biodiversity Ticketing System</title>" .
				link_tag("css/login.css") .
				"<link rel=\"icon\" href=\"" . base_url() . "images/favicon.ico\" />" .
			"</head>
			<body>
				<div id=\"login\" class=\"center\">
					<div id=\"panel\">
						ASEAN Centre for Biodiversity Ticketing System
					</div>
					<div id=\"content\">" .
						form_open("accounts/login") . validation_errors() .
							"<div id=\"field\">
								<input type=\"text\" name=\"username\" />
								Username
							</div>
							<div id=\"field\">
								<input type=\"password\" name=\"password\" />
								Password
							</div>
							<input type=\"submit\" value=\"Log in\" class=\"submit\" />" .
							"<div id=\"forgot\">
								&emsp;
							</div>
						" . form_close("</div></div>") . "
				<div id=\"footer\" class=\"center\">
			Ticketing System &copy; "; //<a href=\"accounts/forgot\">Forgot password or username?</a>
	$copyYear = 2011; 
	$curYear = date("Y"); 
	$year = $copyYear . (($copyYear != $curYear) ? " - " . $curYear : "");
	
	echo $year . ". ASEAN Centre for Biodiversity.
		</div>
			</body>
		</html>";
?>