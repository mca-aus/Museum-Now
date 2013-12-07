<?php

/**
 * Digital Sign for the Museum Now project.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 */

require_once(realpath(dirname(__FILE__).'/../core/core.php'));

/**
 * Load text content from configuration files 
 */
$textContent = array(
	'banner' => get_metadata('institution-name'),
	'ref-url' => get_metadata('digital-sign-ref-url'),
	'hashtag' => get_metadata('instagram-hashtag')
);

/**
 * Scan img/ directory for graphical logos / images. 
 */

$imagePlaceholders = array(
	'banner-image' => null,
	'banner-image-wide' => null,
	'logo' => null,
	'photos-provided-by' => null,
	'more-info' => null
);

$imagePlaceholderKeys = array_keys($imagePlaceholders);

foreach ($imagePlaceholderKeys as $imagePlaceholderKey)
{
	$imgURL = get_metadata($imagePlaceholderKey, DIGITALSIGN_ASSETS_METADATA_FILE);
	if ($imgURL)
	{
		$imagePlaceholders[$imagePlaceholderKey] = get_metadata($imagePlaceholderKey, DIGITALSIGN_ASSETS_METADATA_FILE);
	}
}

?>
<!DOCTYPE html>
<html>
<head>
   <title>Digital Signage</title>
   <script type="text/javascript" src="scripts/jquery.1.7.1.min.js"></script>
   <script type="text/javascript" src="scripts/jquery.easing.1.3.js"></script>		
	<script type="text/javascript" src="scripts/script.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="translucent-black">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">
</head>
<body <?php if(get_metadata('digitalsign-bgcolor')) { echo 'style="background-color: '.get_metadata('digitalsign-bgcolor').'"'; } ?>>
   <div class="container">
      <header class="banner">
         <div class="header" <?php if(!empty($imagePlaceholders['banner-image'])) { echo 'style="background-image: url(\''.$imagePlaceholders['banner-image'].'\')"'; } ?>>
			<?php 
			
			if (empty($imagePlaceholders['banner-image']))
			{
				if (!empty($textContent['banner'])) 
				{ 
					echo "<span>".$textContent['hashtag']." on Instagram</span>";
				} 
				else 
				{
					echo "Museum Now"; 
				} 
			}
			
			?>
			</div>
         <div class="logo" <?php if(!empty($imagePlaceholders['logo'])) { echo 'style="background-image: url(\''.$imagePlaceholders['logo'].'\')"'; } ?>>
			</div>
      </header>
      <div class="instagram-photo-area">
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
         <div class="instagram-photo">
				<div class="back"></div>
				<div class="front"></div>
			</div>
      </div>
      <footer class="footer">
         <div class="photos-provided-by">
				<!-- Required only for MCA Now: image caption of 'Photos provided by:' -->
				<span class="photos-provided-by-caption" style="<?php if(!empty($imagePlaceholders['photos-provided-by'])) { echo "display: none"; } ?>"><?php if(empty($imagePlaceholders['photos-provided-by'])) { echo "Photos provided by:"; } ?></span>
				<img class="photos-provided-by-caption" src="<?php if(!empty($imagePlaceholders['photos-provided-by'])) { echo 'img/'.$imagePlaceholders['photos-provided-by']; } ?>"/>
			</div>
         <div class="see-more" <?php if(!empty($imagePlaceholders['more-info'])) { echo 'style="background-image: url(\'img/'.$imagePlaceholders['more-info'].'\')"'; } ?>>
				<?php if(empty($imagePlaceholders['more-info']) && !empty($textContent['ref-url'])) { echo "See more at ".$textContent['ref-url']; } ?>
			</div>
      </footer> 
   </div>
</html>