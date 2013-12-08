<?php

/**
 * Admin panel / bootstrap loader of Museum Now. At this stage it just
 * simply redirects to the digital sign.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

// TODO : Create / implement administration panel.

require_once(realpath(dirname(__FILE__).'/core/core.php'));

if (museum_now_is_installed())
{
	$redirectURL = determine_museum_now_root()."digitalsign";
}
else
{
	$redirectURL = determine_museum_now_root()."setup";
}

header("Location: {$redirectURL}");
exit();

?>

