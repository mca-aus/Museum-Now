<?php

/**
 * Test file that reverts custom proxy settings back to their default state
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

require_once(realpath(dirname(__FILE__).'/../core/core.php'));

set_metadata("proxy-server", "http://proxy.uow.edu.au", PROXY_FILE);
set_metadata("proxy-port", "8080", PROXY_FILE);
set_metadata("proxy-username", "twray", PROXY_FILE);
set_metadata("proxy-password", "bogart10", PROXY_FILE);

?>

