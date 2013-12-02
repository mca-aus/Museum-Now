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

/**
 * Process form submission events 
 */
if (isset($_POST['institution-name']) && isset($_POST['instagram-account-name']) && isset($_POST['instagram-hashtag']) && isset($_POST['instagram-account-url']) && isset($_POST['digitalsign-ref-url']))
{
	// Save details to config file. TODO: Some better sanity checking,
	// remove redundant Instagram profile URL field
	set_config('institution-name', $_POST['institution-name']);
	set_config('instagram-account-name', $_POST['instagram-account-name']);
	set_config('instagram-hashtag', $_POST['instagram-hashtag']);
	set_config('instagram-account-url', $_POST['instagram-account-url']);
	set_config('digital-sign-ref-url',$_POST['digitalsign-ref-url']);
	
	// If all goes well, then redirect to /instagram-app-registration
	$redirectURL = determine_museum_now_root(TRUE)."setup/instagram-app-registration";
	header("Location: {$redirectURL}");
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
				<h1 class="fancy-title">First, a Few Details ...</h1>
				<form role="form" method="POST" action="./">
					<div class="form-group">
						<p>The name of your museum or gallery</p>
						<input type="text" class="form-control" id="institution-name" name="institution-name">
					</div>
					<div class="alert alert-danger" style="display:none">Please enter the name of your museum or gallery</div>
					<div class="form-group">
						<p>Your museum's Instagram account username</p>
						<input type="text" class="form-control" id="instagram-account-name" name="instagram-account-name" placeholder="Your_Instagram_Account_Name">
					</div>
					<div class="alert alert-danger" style="display:none">Please enter your Instagram account name</div>
					<div class="form-group">
						<p>The public URL of your museum's profile on <b>instagram.com</b>.</p>
						<input type="text" class="form-control" id="instagram-account-url" name="instagram-account-url" placeholder="http://instagram.com/your_account_url">
					</div>
					<div class="alert alert-danger" style="display:none">Please enter your museum's profile on <b>instagram.com</b>.</div>
					<div class="form-group">
						<p>The <b>#hashtag</b> that your visitors will use to get their posts displayed on the public feeds.</p>
						<input type="text" class="form-control" id="instagram-hashtag" name="instagram-hashtag" placeholder="#hashtag">
					</div>
					<div class="alert alert-danger" style="display:none">Please enter a hashtag.</div>
					<div class="form-group">
						<p>A URL where visitors can go to find out more about what's going on at the museum.</p>
						<input type="text" class="form-control" id="digitalsign-ref-url" name="digitalsign-ref-url" placeholder="http://your-museum-website/more-info">
					</div>
					<button id="submit-form" type="submit" class="btn btn-default">Continue</button>
				</form>
			</div>			
		</div>
	</body>
	<script type="text/javascript">
		
		// Basic Form Validation
		
		var fieldsThatCannotBeEmpty = [
			'institution-name',
			'instagram-account-name',
			'instagram-account-url',
			'instagram-hashtag'
		];
		
		setUpFormValidation(fieldsThatCannotBeEmpty);
		
		// Dynamically update #instagram-account-url and #instagram-hashtag
		// as the user types in #instagram-account-name
		$("#instagram-account-name").keyup(function() {
			console.log("changed");
			$("#instagram-account-url").val("http://instagram.com/" + $("#instagram-account-name").val()); 
			$("#instagram-hashtag").val("#" + $("#instagram-account-name").val() + "_now"); 
		});
		
	</script>
</html>