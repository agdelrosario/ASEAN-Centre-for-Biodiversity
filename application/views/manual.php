<?php

/* View:		Home
 * Author:		Aletheia Grace del Rosario
 * Date:		January 2, 2011
 * Description:	The homepage.
 */
 
include("header.php");

echo heading('Manual', 1, "class = \"manual\"");

echo heading('About the Ticketing System', 2);

echo "<p>The ASEAN Centre for Biodiversity is consistent in its passion to serve the ASEAN countries in preserving their natural resources. This passion requires a lot of travelling around the ASEAN countries, as well as to attend various events in the base country, the Philippines. The Ticketing System is dedicated to compile and handle travel records, with ease of use as primary concern.</p>";

echo heading('Table of Contents', 2);

echo heading('Other Concerns', 2);

echo "<p>If you have any concerns that are not addressed in the manual, feel free to contact the system administrator.</p>";

include("footer.php");

/* End of file travelorders.php */
/* Location: ./application/views/travelorders.php */

?>