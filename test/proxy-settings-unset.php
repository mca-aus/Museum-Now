<?php

/**
 * Test file that sets custom proxy settings for testing on the UOW server
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

require_once(realpath(dirname(__FILE__).'/../core/core.php'));

set_metadata("proxy-server", null, PROXY_FILE);
set_metadata("proxy-port", null, PROXY_FILE);
set_metadata("proxy-username", null, PROXY_FILE);
set_metadata("proxy-password", null, PROXY_FILE);

?>

