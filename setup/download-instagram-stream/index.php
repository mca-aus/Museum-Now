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

require_once(realpath(dirname(__FILE__).'/../../core/core.php'));

/**
 * Can't run this script through the command line 
 */
if(is_running_from_command_line())
{
	echo "Please run this script through your Web browser"."\r\n";
	exit();
}

go_to_tutorial_introduction_if_not_in_setup_session();

?>

<!DOCTYPE html>
<html lang="en">
	<?php require_once(realpath(dirname(__FILE__).'/../../core/core.php')); 
	
	output_setup_html_head();
		
	if(!permissions_ok())
	{
		output_permissions_error();
		exit();
	}

	?>
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Downloading Instagram Stream ... </h1>
				<p class="instagram-photo-download-status">Museum Now is downloading photos from your Instagram stream. This may take a minute or two ... <br /></p>
				<div class="progress">
					<div id="instagram-download-progress-bar" class="progress-bar progress-bar-success" role="progressbar" style="width: 0%"></div>
				</div>
			</div>			
		</div>
	</body>
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			setInterval(updateProgressBar, 1000);
			
			$.getJSON("../../cron/download-instagram-photos.php", function(data) {
				
				var instagramAPIStatusResponse = data.instagram_api_status_response;
				
				console.log(data);
				
				if (instagramAPIStatusResponse == 200)
				{
					window.location.replace('../get-embed-code-for-instagram-widget');
				}
				else
				{
					$('.instagram-photo-download-status').html("Awww snap. Something went wrong.");
				}
				
			});
			
			function updateProgressBar()
			{
				$.getJSON("../../get/download-instagram-photos-progress.php", function(data) {
					var percentWidth = data['percent-complete'];
					$("#instagram-download-progress-bar").css({"width": percentWidth + "%"});
				});
			}
			
		});
		
	</script>
</html>