<?php

/**
 * File that 'installs' and configures Museum Now. This includes setting up
 * establishing certain server-side environment variables and of course,
 * sets up the cron jobs. This 'installation script' needs to be run from
 * a Web browser.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 */

require_once(realpath(dirname(__FILE__) . '/../core/core.php'));

// set_error_handler('install_error_handler');

/**
 * Step 0 - Are we running this from the server? You cannot run the install
 * script from the command line. 
 */
if (is_running_from_command_line())
{
	echo "Sorry, Museum Now can only be installed by running this script through your Web browser. Please refer to the documentation for more details."."\r\n";
	die();
}

/**
 * Step 1 - Determine the host name and document root on the server, used
 * to provide absolute paths for all file / image references in Museum Now. 
 * Store this in the file config/museum-now-root. Since we are attempting to 
 * save the file, we can check at this point whether we have write permissions 
 * on the server. Throw an error message and let the user know if we can't save 
 * this file. 
 */
set_config('museum-now-root', determine_museum_now_root());

/**
 * Step 2 - Set up Cron jobs by writing to a cron.txt file in cron and using
 * crontab to run a regular cron process 
 */
//$cronFileHandler = fopen('../cron/cron.txt', 'w+');
//$absolutePathOfPHPFile = get_absolute_path_of_php_binary();
//// $absolutePathOfDownloadInstagramPhotosCronProcess = get_absolute_path_of_museum_now_folder().'/cron/download-instagram-photos.php';
//fputs($cronFileHandler, "* * * * * {$absolutePathOfPHPFile} {$absolutePathOfDownloadInstagramPhotosCronProcess}"."\r\n");
//fclose($cronFileHandler);
//exec("crontab ../cron/cron.txt");

/**
 * If all went well, then display a 'success' message.
 */
echo "Museum Now has been successfully installed.";

/**
 * Error handling funcion for Museum Now, set by set_error_handler()
 * @param int $errorNumber The level of the error raised, as an integer
 * @param string $errorString Contains the error message as a string
 */
function install_error_handler($errorNumber, $errorString)
{
	if (strstr(strtolower($errorString), "failed to open stream: permission denied"))
	{
		echo "Oops! Museum Now can't run unless it can read and write files to the server. Please make sure you set the permissions correctly for all files in <code>".  determine_museum_now_root(FALSE)."</code>";
	}
	exit();
}

?>
