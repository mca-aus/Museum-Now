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
 * Setup is 'complete' - we can mark it as such in the config file 
 */
set_config('setup-complete', true);

if (isset($_FILES['banner-image-asset-upload']) && !empty($_FILES['banner-image-asset-upload']['tmp_name']))
{
	copy($_FILES['banner-image-asset-upload']['tmp_name'], '../../digitalsign/img/banner-image.png');
}

if (isset($_FILES['logo-asset-upload']) && !empty($_FILES['logo-asset-upload']['tmp_name']))
{
	copy($_FILES['logo-asset-upload']['tmp_name'], '../../digitalsign/img/logo.png');
}

if (isset($_POST['theme-select']))
{
	if (strtolower($_POST['theme-select']) == 'black')
	{
		set_config("digitalsign-bgcolor", "#000000");
	}
	else if (strtolower($_POST['theme-select']) == 'gunmetal')
	{
		set_config("digitalsign-bgcolor", "#333333");
	}
}

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
				<h1 class="fancy-title center-text">Your Digital Sign</h1>
				<p>Here you can customise your digital sign and upload custom branding for it. You can use any display to access your digital sign at <a href="<?php echo get_config('museum-now-root')."digitalsign/" ?>"><?php echo get_config('museum-now-root')."digitalsign/" ?></a></p>
			</div>
			
			<div class="digital-sign-container">
				<iframe src="../../digitalsign/" width="100%" height="100%" frameborder="none"></iframe>
			</div>
			
			<div class="col-xs-6 pull-left custom-sign-content">
				<h3>Content and Branding</h3>
				<p>You can upload custom brand assets and logos for your digital signs. Files must be in the PNG file format.</p>
				<form role="form" class="form-horizontal" enctype="multipart/form-data" method="POST" action="./">
					<div class="form-group">
						<label class="col-sm-3 control-label">Top-left logo :</label>
						<div class="col-sm-6">
							<input type="file" id="banner-image-asset-upload" name="banner-image-asset-upload">
							<p class="help-block">Appears in the form of a long banner at the top-left corner of the display</p>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Top-right logo :</label>
						<div class="col-sm-6">
							<input type="file" id="logo-asset-upload" name="logo-asset-upload">
							<p class="help-block">Appears in the form of a long banner at the top-right corner of the display</p>
						</div>
					</div>
					<button type="submit" class="btn btn-info upload-files-btn">Upload Files</button>
				</form>
			</div>
			<div class="col-xs-6 pull-right">
				<h3>Theme and Style</h3>
				<form role="form" id="theme-select-form" class="form-horizontal" method="POST" action="./">
					<div class="form-group">
						<label class="col-sm-3 control-label">Theme :</label>
						<div class="col-sm-6">
							<select class="form-control" id="theme-select" name="theme-select">
								<option <?php if(get_config("digitalsign-bgcolor") == "#000000") {echo "selected"; } ?>>Black</option>
								<option <?php if(get_config("digitalsign-bgcolor") == "#333333") {echo "selected"; } ?>>Gunmetal</option>
							</select>
						</div>
					</div>
				</form>
			</div>
		</div>
	</body>
	<script type="text/javascript">
	
	$(document).ready(function() {
		
		$("#theme-select").change(function() {
			$("#theme-select-form").submit();
		});
		
	});
	
	</script>
</html>