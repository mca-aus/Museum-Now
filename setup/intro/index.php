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

establish_setup_session();

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
				<h1 class="fancy-title">Museum Now</h1>
				<p>Museum Now uses Instagram to tell the real time story of your museum, as seen through the eyes of your staff, visitors and collaborators.</p>
				<p>To continue, you will need to be the administrator of your institution's Instagram account, or at least, know the username and password to log in.</p>
				<p><a href="../instagram-account-basics/" class="btn btn-primary btn-lg" role="button">Set Up Museum Now</a></p>
			</div>			
		</div>
	</body>
</html>