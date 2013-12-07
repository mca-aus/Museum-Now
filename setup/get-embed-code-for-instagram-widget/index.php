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

/**
 * By now in the setup process, we should have our Instagram feed downloaded
 * and up-to-date. At this point, we will start the scheduler that ensures
 * that the feed gets updated. 
 */
start_instagram_downloader();

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
				<h1 class="fancy-title center-text">Your Instagram Stream</h1>
				<p>With Museum Now, you can create your own embedded timeline that you can place straight on to your museum website or CMS. If you ever need to grab the code, just bookmark this URL.</p>
				<p>Does your museum also have a Twitter account? Why not <a href="https://twitter.com/settings/widgets/new">add an embedded Twitter timeline to your site?</a></p>
				<a href="../digitalsign/" class="btn btn-default" role="button">Continue</a>
			</div>
			<div class="col-xs-7">
				<form role="form" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Theme:</label>
						<div class="col-sm-5">
							<select class="form-control" id="instagram-widget-theme">
								<option>Light</option>
								<option>Dark</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Width:</label>
						<div class="col-sm-4">
							<div class="input-group">
								<input type="text" value="420" class="form-control" id="instagram-widget-width">
								<span class="input-group-addon">px</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Height:</label>
						<div class="col-sm-4">
							<div class="input-group">
								<input type="text" value="600" class="form-control" id="instagram-widget-height">
								<span class="input-group-addon">px</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Embed Code:</label>
						<div class="col-sm-9">
							<textarea id="iframe-code-text-area" class="form-control" rows="5" readonly="readonly"></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="col-xs-5">
				<iframe class="embedded-timeline" src="<?php echo get_metadata('museum-now-root') ?>instagram-feed/" width="100%" height="600px" frameborder="0"></iframe>
			</div>
		</div>
	</body>
	<script type="text/javascript">
	
	$(document).ready(function() {
		
		updateEmbedCodeAndTimeline();
		$(".form-control").change(updateEmbedCodeAndTimeline);
		
	});
	
	function updateEmbedCodeAndTimeline()
	{
		var selectedWidth = $("#instagram-widget-width").val();
		var selectedHeight = $("#instagram-widget-height").val();
		var selectedTheme = $("#instagram-widget-theme").val();
		
		console.log(selectedWidth);
		console.log(selectedHeight);
		console.log(selectedTheme);
		
		var URLForEmbeddedTimeline = $('.embedded-timeline').attr('src');
		console.log(URLForEmbeddedTimeline);
		var iFrameCode = '<iframe src="<?php echo get_metadata('museum-now-root') ?>instagram-feed/?thm=' + selectedTheme.toLowerCase() + '" width="' + selectedWidth + '" height="' + selectedHeight + '" frameborder="0"></iframe>';
		$("#iframe-code-text-area").text(iFrameCode);
		$('.embedded-timeline').attr({'src': '<?php echo get_metadata('museum-now-root') ?>instagram-feed/?thm=' + selectedTheme.toLowerCase() });
	}
	
	</script>
</html>