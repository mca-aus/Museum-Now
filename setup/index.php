<?php

/**
 * Museum Now setup configuration script.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

$suppressSetupPageRedirect = TRUE;
require_once(realpath(dirname(__FILE__).'/../core/core.php'));

/**
 * Can't run this script through the command line 
 */
if(is_running_from_command_line())
{
	echo "Please run this script through your Web browser"."\r\n";
	exit();
}

create_file_structures_for_museum_now();
set_config('museum-now-root', determine_museum_now_root());
$redirectURL = determine_museum_now_root()."setup/intro";
header("Location: {$redirectURL}");

?>
