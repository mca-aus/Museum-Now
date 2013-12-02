<?php

/**
 * Returns JSON that provides the HTML embed code for the Instagram widget.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 *
 */

header('Content-type: application/json');
require_once(realpath(dirname(__FILE__).'/../core/core.php'));

$htmlEmbedCode = "";
if (isset($_GET['width']) && is_numeric($_GET['width']))
{
	if (isset($_GET['height']) && is_numeric($_GET['height']))
	{
		if (isset($_GET['theme']))
		{
			$htmlEmbedCode = generate_html_for_embeddable_instagram_widget($_GET['width'], $_GET['height'], $_GET['theme']);
		}
		else
		{
			$htmlEmbedCode = generate_html_for_embeddable_instagram_widget($_GET['width'], $_GET['height']);
		}
	}
	else
	{
		$htmlEmbedCode = generate_html_for_embeddable_instagram_widget($_GET['width']);
	}
}
else
{
	$htmlEmbedCode = generate_html_for_embeddable_instagram_widget();
}



isset($_GET['width']) ? $width = $_GET['width'] : $width = null;
isset($_GET['height']) ? $height = $_GET['height'] : $height = null;
isset($_GET['theme']) ? $theme = $_GET['theme'] : $theme = null;

$json = make_json_pretty(json_encode(array('html_embed_code' => $htmlEmbedCode)));
echo $json;

?>
