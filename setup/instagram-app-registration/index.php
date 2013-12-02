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
 * Process form submission events, redirect to instagram.com if Client ID 
 * and Secret are submitted
 */
if (isset($_POST['client-id']) && isset($_POST['client-secret']))
{
	set_config('instagram-client-id', $_POST['client-id']);
	set_config('instagram-client-secret', $_POST['client-secret']);
	
	$clientID = $_POST['client-id'];
	$redirectURI = get_config('museum-now-root')."setup/instagram-app-registration";
	$redirectURL = "https://api.instagram.com/oauth/authorize/?client_id={$clientID}&redirect_uri={$redirectURI}&response_type=code";
	header("Location: {$redirectURL}");
}

/**
 * If the code is sent to the request of this page, process it and use it
 * to again call Instagram to retrieve the access token.
 */
if (isset($_GET['code']))
{
	$clientID = get_config('instagram-client-id');
	$clientSecret = get_config('instagram-client-secret');
	$grantType = 'authorization_code';
	$redirectURI = get_config('museum-now-root')."setup/instagram-app-registration";
	$code = $_GET['code'];
	
	// Requires cURL to perform a POST request over HTTPS. Test this on
	// production servers?
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/oauth/access_token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
		 'client_id' => $clientID,
		 'client_secret' => $clientSecret,
		 'grant_type' => $grantType,
		 'redirect_uri' => $redirectURI,
		 'code' => $code
	)));
	
	$jsonResponse = curl_exec($ch);
	curl_close($ch);
	
	$jsonResponseAsArray = json_decode($jsonResponse);
	$accessToken = $jsonResponseAsArray->access_token;
	set_config('instagram-access-token', $accessToken);
	
	// If all goes well, then redirect to /instagram-app-registration
	$redirectURL = determine_museum_now_root()."setup/download-instagram-stream";
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
				<h1 class="fancy-title">Set up Instagram API</h1>
				<p>In the next step, we are going to get Instagram to give us permission to access its data. To continue, make sure you're logged into your museum's Instagram account.</p>
				<ol>
					<li>
						<p>Once logged in, navigate to <a target="_blank" href="http://instagram.com/developer/">instagram.com/developer</a></p>
					</li>
					<li>
						<p>In the top-right of the screen, click on <b>Manage Clients</b>, then click <b>Register New Client.</b> You may also need to agree to the Developer License Agreement if you haven't done so already.</p>
						<img class="tut-screen-image img-rounded" src="tut_screen_1.jpg" />
					</li>
					<li>
						<p>You will then be taken to the following page where you would need to register Museum Now with the Instagram API:</p>
						<img class="tut-screen-image img-rounded" src="tut_screen_2.jpg" />
						<p>Copy and paste from the following fields into the above page. Once done, click <b>Register</b>.</p>
						<div class="form-group">
							<label>Application Name:</label>
							<input type="text" class="form-control" value="Museum Now : <?php echo get_config('institution-name'); ?>" readonly="readonly">
						</div>
						<div class="form-group">
							<label>Description:</label>
							<input type="text" class="form-control" value="A live public feed for <?php echo get_config('institution-name'); ?>" readonly="readonly" />
						</div>
						<div class="form-group">
							<label>Website:</label>
							<input type="text" class="form-control" value="<?php echo get_config('digital-sign-ref-url'); ?>" readonly="readonly" />
						</div>
						<div class="form-group">
							<label>OAuth redirect_uri:</label>
							<input type="text" class="form-control" value="<?php echo get_config('museum-now-root')."setup/instagram-app-registration"; ?>" readonly="readonly" />
						</div>
					</li>
					<li>
						<p>The next page gives you a Client ID and a Client Secret. Copy and paste these into the fields below:</p>
						<img class="tut-screen-image img-rounded" src="tut_screen_3.jpg" />
						<form role="form" method="POST" action="./">
							<div class="form-group">
								<label>Client ID from Instagram.com</label>
								<input type="text" class="form-control" id="client-id" name="client-id">
							</div>
							<div class="alert alert-danger" style="display:none">Please enter the Client ID from Instagram.com</div>
							<div class="form-group">
								<label>Client Secret from Instagram.com</label>
								<input type="text" class="form-control" id="client-secret" name="client-secret">
							</div>
							<div class="alert alert-danger" style="display:none">Please enter the Client Secret from Instagram.com</div>
							<p>You will then be taken to Instagram.com where you will be asked to authenticate your app.</p>
							<button id="submit-form" type="submit" class="btn btn-info">Authenticate via Instagram.com</button>
						</form>
					</li>
				</ol>
			</div>			
		</div>
	</body>
	<script type="text/javascript">
		
		// Basic Form Validation
		
		var fieldsThatCannotBeEmpty = [
			'client-id',
			'client-secret'
		];
		
		setUpFormValidation(fieldsThatCannotBeEmpty);
		
	</script>
</html>