<?php
	echo "
			</div>
		</div>
		<div id=\"footer\" class=\"center\">
			Ticketing System &copy; ";
	$copyYear = 2011; 
	$curYear = date('Y'); 
	$year = $copyYear . (($copyYear != $curYear) ? ' - ' . $curYear : '');
	
	echo $year . " &bullet; ASEAN Centre for Biodiversity
		</div>
	</body>
</html>";
?>