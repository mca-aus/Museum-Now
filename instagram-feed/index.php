<?php
	
/**
 * Instagram widget for the Museum Now project.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 */

$suppressSetupPageRedirect = TRUE;
require_once(realpath(dirname(__FILE__).'/../core/core.php'));

/**
 * Get Instagram account details. 
 */
$instagramHashtag = get_metadata('instagram-hashtag');
$instagramAccountName = get_metadata('instagram-account-name');
$instagramAccountURL = get_metadata('instagram-account-url');

/**
 * Get Instagram data from /get/instagram-photos.json 
 */
$instagramData = json_decode(file_get_contents(INSTAGRAM_PHOTOS_METADATA_FILE));
$instagramDataAsShownInFeed = array();
foreach ($instagramData as $photo)
{
	$instagramPhotoAsShownInFeed = array(
		 'photo_image_standard_resolution_src' => $photo->images->standard_resolution->url,
		 'photo_url' => $photo->link,
		 'user_profile_image_src' => $photo->user->profile_picture,
		 'user_username' => $photo->user->username,
		 'user_instagram_profile_url' => "http://instagram.com/".$photo->user->username,
		 'photo_upload_date' => date("j M", $photo->created_time),
		 'photo_tags' => $photo->tags,
		 'photo_likes_count' => $photo->likes->count,
		 'photo_comments_count' => $photo->comments->count
	);
	$instagramDataAsShownInFeed[] = $instagramPhotoAsShownInFeed;
}

/**
 * Determine what theme to use - whether to show the dark or light theme based
 * on the 'thm' query string parameter. Defaults to 'light' theme. 
 */
$themeClass = "thm-light";
if (isset($_GET['thm']))
{
	if (strtolower($_GET['thm']) == 'dark')
	{
		$themeClass = "thm-dark";
	}
}


?>

<!DOCTYPE html>
<html>
	<head>
		<title>Instagram Feed</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="style.css" type="text/css" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script>
			
			$(document).ready(function() {
				
				positionElements();
				$(window).resize(positionElements);
				
			});
			
			function positionElements() {
				$('.stream').height($(window).outerHeight() - $('.timeline-header').outerHeight() - $('.timeline-footer').outerHeight() - 3);
				$('.timeline').width($('.timeline').width() - 2);
			}
			
		</script>
	</head>
	<body>
		
      <div class="root timeline customisable-border <?php echo $themeClass ?>">
			<div class="timeline-header customisable-bottom-border instagram-logo <?php echo $themeClass ?>">
				<h1 class="summary">Photos</h1>
				<div class="link-to-profile">
					<a href="<?php echo $instagramAccountURL; ?>" target="_blank"><span class="instagram-username"><?php echo $instagramAccountName; ?></span></a>
				</div>
			</div>
			<div class="stream customisable-bottom-border <?php echo $themeClass ?>">
				<ol class="h-feed">
					<?php foreach ($instagramDataAsShownInFeed as $photoToShow) : ?>
						<li class="h-entry">
							<div class="instagram-photo-post">
								<div class="instagram-photo-post-header">
									<a href="<?php echo $photoToShow['user_instagram_profile_url']; ?>" target="_blank">
										<div class="instagram-post-profile">
											<img class="instagram-post-profile-image" alt="<?php echo $photoToShow['user_username']; ?>" src="<?php echo $photoToShow['user_profile_image_src']; ?>" />
											<span class="instagram-post-profile-name"><?php echo $photoToShow['user_username']; ?></span>
										</div>
									</a>
									<div class="instagram-post-date"><?php echo $photoToShow['photo_upload_date']; ?></div>
								</div>
								<div class="instagram-image-placeholder">
									<a href="<?php echo $photoToShow['photo_url'] ?>" target="_blank">
										<img alt="<?php echo $photoToShow['user_username']; ?>" src="<?php echo $photoToShow['photo_image_standard_resolution_src']; ?>" />
									</a>
								</div>
								<div class="instagram-photo-post-footer">
									<ul class="instagram-post-tags">
										<?php foreach ($photoToShow['photo_tags'] as $photoTag) : ?>
											<li>#<?php echo $photoTag; ?></li>
										<?php	endforeach; ?>
									</ul>
									<a href="http://instagram.com/p/hFPe56KFx5/" target="_blank">
										<div class="instagram-likes-and-comments">
											<span class="instagram-likes"><?php echo $photoToShow['photo_likes_count']; ?></span>
											<span class="instagram-comments"><?php echo $photoToShow['photo_comments_count']; ?></span>
										</div>
									</a>
								</div>
							</div>
						</li>	
					<?php	endforeach; ?>
				</ol>
			</div>
			<div class="timeline-footer"> 
				<span class="tag-line">Tag your photos on Instagram with <span class="hash-tag"><?php echo $instagramHashtag; ?></span></span>
			</div>
		</div>
	</body>
</html>
