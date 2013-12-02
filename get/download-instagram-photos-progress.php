<?php

/**
 * Web Service that returns the progress - expressed as a percentage - of the
 * Instagram downloader as part of the Installation process of Museum Now.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

require_once(realpath(dirname(__FILE__).'/../core/core.php'));

// Get number of images in /cached/images/
$cachedImages = glob(realpath(dirname(__FILE__).'/../cached/images').'/*.jpg');
$numCachedImages = sizeof($cachedImages);

echo make_json_pretty(json_encode(array('percent-complete' => floor($numCachedImages / AMOUNT_OF_PHOTOS_TO_DOWNLOAD * 100))));

?>
