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

$suppressSetupPageRedirect = TRUE;
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

if(permissions_ok())
{
	// If permissions are okay, then set the "museum-now-root" property in
	// CONFIG FILE and redrect to ../instagram-account-basics
	set_metadata('museum-now-root', determine_museum_now_root());
	$redirectURL = determine_museum_now_root(TRUE)."setup/instagram-account-basics";
	header("Location: {$redirectURL}");
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
	<?php require_once(realpath(dirname(__FILE__).'/../../core/core.php')); 
	
	output_setup_html_head();
		
	?>
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Set Up your Web Server</h1>
				<p>Museum Now works by downloading images from your Instagram account onto your server so they can be displayed on your digital signage.</p> 
				<p>In almost all cases, most Web servers don't allow programs to save files on them unless you give them explicit permission. In order to continue, you will need to change your file permissions so that Museum Now can download and save files on your server.</p>
				<?php if (isset($_POST['validate-permissions'])) : ?>
					<div class="alert alert-danger" style="display:block">Hmmm ... looks like the permissions are still not set. Please double-check that you have set the correct permissions, or contact your server administrator for help.</div>
				<?php endif; ?>
				<h3>Setting file permissions on your server</h3>
				<p>You can use your FTP program to set file permissions. In this example, we will use <a href="http://cyberduck.io/">Cyberduck</a>, a free and popular program for managing files on Web servers.</p>
				<p>You can <a href="http://cyberduck.en.softonic.com/mac/download">download Cyberduck for free</a> and follow these steps. Most other FTP programs follow a similar method for setting file permissions.</p>
				<ol>
					<li>
						If you haven't done so already, make sure that you're connected to your server via FTP or SFTP. In Cyberduck, you can create a new FTP connection by clicking on <b>Open Connection</b> in the top-right corner. If you don't know your server, username or password details, contact your IT administrator.
						<img class="tut-screen-image img-rounded" src="tut_screen_1.png" />
					</li>
					<li>
						Once done, navigate to your <b>Museum Now</b> directory. You should see a folder structure that looks something like this:
						<img class="tut-screen-image img-rounded" src="tut_screen_2.png" />
					</li>
					<li>
						Right click (or Control-click) on the folder named <b>store</b> and from the menu, select <b>Info</b>.
						<img class="tut-screen-image img-rounded" src="tut_screen_3.png" />
					</li>
					<li>In the <b>Info</b> window, navigate to the <b>Permissions</b> tab. Tick all of the 'Write' checkboxes and make sure the <b>Unix Permissions</b> field is set to <b>777</b>. Once done, click <b>Apply changes recursively</b>.</li>
					<img class="tut-screen-image img-rounded" src="tut_screen_4.png" />
					<li>That's it! Just wait a minute or two for Cyberduck to work its magic, and then you should be good to go!</li>
				</ul>
				<h3>Got access to a terminal?</h3>
				<p>Rather than follow the above steps, you can also set your file permissions using one single terminal command.</p>
				<p>If you're logged into your Web Server with administrator privileges, just copy and paste this command into your terminal:</p>
				<p><code><?php echo "chmod -R 777 ".STORE_DIR; ?></code></p>
				<form role="form" method="POST" action="./">
					<input type="hidden" name="validate-permissions" value="true" />
					<button id="submit-form" type="submit" class="btn btn-info">OK, I've set the permissions. Let's Go!</button>
				</form>
			</div>			
		</div>
	</body>
</html>
