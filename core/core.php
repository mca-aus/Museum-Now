<?php

/**
 * File that encapsulates main logic / functionality of Musuem Now.
 *
 * Museum Now is a brain-child of the Museum of Contemporary Art Australia,
 * made with much love by Tim Wray and is based on the work of MCA Now,
 * a product produced by Rory McKay.
 *
 * Contact: timwray.mail@gmail.com; rorymckay@gmail.com
 */

date_default_timezone_set('Australia/Sydney');
define('CONFIG_FILE', realpath(dirname(__FILE__)).'/../config/config.json');
define('PHOTOS_FROM_OWN_FEED_API_ENDPOINT', 'https://api.instagram.com/v1/users/self/media/recent');
define('LIKED_PHOTOS_API_ENDPOINT', 'https://api.instagram.com/v1/users/self/media/liked');
define('AMOUNT_OF_PHOTOS_TO_DOWNLOAD', '16');

/**
 * All pages / sections on Museum Now redirect to the setup page if Museum
 * Now is not installed (i.e., config/config.json is not there). 
 */
redirect_to_setup_page_if_not_installed();

/**
 * Indents a flat JSON string to make it more human-readable.
 * Kudos to http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
 *
 * @param string $json The original JSON string to process.
 * @return string Indented version of the original JSON string.
 */
function make_json_pretty($json) {

	$result = '';
	$pos = 0;
	$strLen = strlen($json);
	$indentStr = '  ';
	$newLine = "\n";
	$prevChar = '';
	$outOfQuotes = true;

	for ($i = 0; $i <= $strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
		} else if (($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos--;
			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}

		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element,
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos++;
			}

			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}

		$prevChar = $char;
	}

	return $result;
}

/**
 * Removes an entire directory and its files
 * @param type $dir The directory to remove
 * @see http://us3.php.net/manual/en/function.rmdir.php#107233
 */
function rrmdir($dir) 
{
	if (is_dir($dir)) 
	{
		$objects = scandir($dir);
		foreach ($objects as $object) 
		{
			if ($object != "." && $object != "..") 
			{
				if (filetype($dir . "/" . $object) == "dir")
					rrmdir($dir . "/" . $object); else
					unlink($dir . "/" . $object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
 * Recursive glob function, see: 
 * http://thephpeffect.com/recursive-glob-vs-recursive-directory-iterator/
 *
 * @param string $pattern The glob pattern
 * @param int $flags The flags to apply to the glob() command, see:
 * http://php.net/manual/en/function.glob.php
 * 
 * @return array A listing of filepaths matched by $pattern
 */
function rglob($pattern, $flags = 0) 
{
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
		$files = array_merge($files, rglob($dir . '/' . basename($pattern), $flags));
	}
	return $files;
}

/**
 * Returns the root URL for Museum Now - only works if you're running this
 * through a Web browser - otherwise, an exception is thrown.
 * 
 * @param boolean $getRootURL If set to TRUE, the absolute root URL is returned.
 * Otherwise, the root path on the server is returned.
 * 
 * @return string The root URL, or file path if $getRootURL is set to FALSE;
 */
function determine_museum_now_root($getRootURL = TRUE)
{
	if (!is_running_from_command_line())
	{
		// Determine the name of the Museum Now directory
		$rootDirectoryInFileSystem = realpath(dirname(__FILE__).'/../');
		$rootDirectoryInFileSystemComponents = explode("/", $rootDirectoryInFileSystem);
		$fileSystemFolderName = strtolower(array_pop($rootDirectoryInFileSystemComponents));
		
		// Determine the document root on the server
		$pathOnServer = $_SERVER['PHP_SELF'];
		while (strtolower($currentPath) !=  $fileSystemFolderName)
		{
			$pathOnServerComponents = explode("/", $pathOnServer);
			$currentPath = array_pop($pathOnServerComponents);
			$pathOnServer = implode("/", $pathOnServerComponents);
		}
		$pathOnServer .= "/".$fileSystemFolderName;
		
		if ($getRootURL)
		{
			$protocol = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
			$rootURLHost = $protocol.$_SERVER['HTTP_HOST'];
			$root = $rootURLHost.$pathOnServer;
		}
		else
		{
			$root = $rootDirectoryInFileSystem;
		}
	
		return $root."/";
	}
	else
	{
		throw new Exception("get_museum_now_root() cannot be called from the command line");
	}
}

/**
 * Returns the absolute URL root for the current Museum Now installation.
 * Will only work if Museum Now has been installed - otherwise an exception
 * will be thrown
 */
function get_url_root()
{
	if ($root = get_config('museum-now-root'))
	{
		return $root;
	}
	else
	{
		echo "Please install Museum Now by running /install/index.php"."\r\n";
	}
}

/**
 * Returns the absolute path of the PHP binary file 
 * @return string The absolute path of the PHP Binary file
 */
function get_absolute_path_of_php_binary()
{
	return PHP_BINDIR."/php";
}

/**
 * Returns the absolute path of the Museum Now installation
 * @return string The absolute path of the Museum Now installation
 */
function get_absolute_path_of_museum_now_folder()
{
	return realpath(dirname(__FILE__).'/..');
}

/**
 * Determines if Museum Now is installed.
 * @return boolean TRUE if Museum Now is installed, otherwise, FALSE 
 */
function museum_now_is_installed()
{
	if (file_exists(CONFIG_FILE))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
 * Adds a log to a log file and optionally displays
 * it to the console.
 *
 * @param $message The message to log.
 */
function log_message($message)
{
   if (is_running_from_command_line())
   {
      echo $message."\r\n";
   }
	
	// Modify HTML log file
	
   $fileLines = array();
   // Open log file if it exists
   if (file_exists(realpath(dirname(__FILE__))."/../log/index.html"))
   {
      $logFileContents = file_get_contents(realpath(dirname(__FILE__))."/../log/index.html");
      $fileLines = explode("<br />", $logFileContents);
      
      // Get first 19 lines of that file
      $fileLines = array_splice($fileLines, 0, 19);
   }
   
   // Create the log message, write it to file
   $logMessage = "<strong>".date('l jS \of F Y h:i:s A')."</strong>\t\t".$message;
   array_unshift($fileLines, $logMessage);
   $updatedFileData = implode("<br />", $fileLines);
   
	file_put_contents(realpath(dirname(__FILE__))."/../log/index.html", $updatedFileData);
	
	// Open JSON log file if it exists
	$logJSON = array();
	if (file_exists(realpath(dirname(__FILE__))."/../log/log.json"))
	{
		$logJSON = json_decode(file_get_contents(realpath(dirname(__FILE__))."/../log/log.json"));
	}
	if (empty($logJSON))
	{
		$logJSON = array();
	}
	
	// Prepend JSON log object to the array
	$logMessage = (object) array('date' => date('l jS \of F Y h:i:s A'), 'message' => $message);
	array_unshift($logJSON, $logMessage);
	
	// Only store 20 last logged items in $logJSON
	$logJSONTruncated = array_splice($logJSON, 0, 20);
	
	// Store $logJSON as prettified JSON in /../log/log.json
	file_put_contents(realpath(dirname(__FILE__))."/../log/log.json", make_json_pretty(json_encode($logJSONTruncated)))	;
}

/**
 * Utility function used to determine if PHP is running from the command line,
 * or is running from the browser. 
 * 
 * @return boolean TRUE if running from the command line, otherwise, FALSE
 */
function is_running_from_command_line()
{
	return PHP_SAPI == 'cli' ? TRUE : FALSE;
}

/**
 * Retrieves a value from the configuration file for a givem key.
 * @param string $key The key to retrieve the value for.
 * @return string The value for the given key, or FALSE if the value doesn't
 * exist.
 */
function get_config($key)
{
	if (file_exists(CONFIG_FILE))
	{
		$configData = json_decode(file_get_contents(CONFIG_FILE));
		if (isset($configData->$key))
		{
			return $configData->$key;
		}
		else
		{
			return FALSE;
		}
	}
	else
	{
		return FALSE;
	}
}

/**
 * Stores a value to the configuration file for a given key-value pair.
 * 
 * @param string $key The key to store.
 * @param string $val The accompanying value.
 * 
 * @return int The number of bytes written to /config/config.json, or 
 * boolean FALSE on failure.
 */
function set_config($key, $val)
{
	if(file_exists(CONFIG_FILE))
	{
		$configData = json_decode(file_get_contents(CONFIG_FILE));
	}
	else
	{
		$configData = (object) array();
	}
	$configData->$key = $val;
	file_put_contents(CONFIG_FILE, make_json_pretty(json_encode($configData)));
	chmod(CONFIG_FILE, 0777);
}

/**
 * Attempt to run the 'Instagram downloader' on a regular, scheduled basis.
 * The downloader runs every 5 minutes, and this creates a bash script that 
 * runs in the background that loops indefinitely: the loop runs
 * download-instagram-photos.php and then sleeps for 10 minutes.
 */
function start_instagram_downloader()
{
	$absolutePathOfPHP = PHP_BINDIR."/php";
	$absolutePathOfCronDirectory = realpath(dirname(__FILE__)."/../cron/");
	$absolutePathOfDownloadInstagramPhotosPHPScript = $absolutePathOfCronDirectory."/download-instagram-photos.php";
	$absolutePathOfScheduleFile = $absolutePathOfCronDirectory."/download-instagram-photos-scheduler.sh";
	$absolutePathOfSchedulingProcessPIDFile = $absolutePathOfCronDirectory."/download-instagram-photos-scheduler-pid.dat"; 
	
	// If there's an Instagram scheduler currently running (by virtue of the fact
	// that download-instagram-photos-scheduler-pid.dat exists and contains a
	// currently running Process ID), then get the proces ID, kill it, and start
	// a new scheduler
	stop_instagram_downloader();
	
	$scheduleFileContents = "
	#!/bin/sh
	echo $$
	while [ 1 ]; do
		{$absolutePathOfPHP} {$absolutePathOfDownloadInstagramPhotosPHPScript}
	   sleep 60
	done";

	file_put_contents($absolutePathOfScheduleFile, $scheduleFileContents);
	chmod($absolutePathOfScheduleFile, 0777);

	$commandToExecute = "{$absolutePathOfScheduleFile} >> {$absolutePathOfSchedulingProcessPIDFile} 2>&1 &";
	$outputForShellScriptInitialisationCommand = array();
	$outputForShellScriptInitialisationCommand = shell_exec($commandToExecute);
}

/**
 * Stops the background process that downloads / caches Instagram photos
 * @return boolean TRUE if there was an Instagram downloader process running,
 * otherwise, FALSE
 */
function stop_instagram_downloader()
{
	$absolutePathOfCronDirectory = realpath(dirname(__FILE__)."/../cron/");
	$absolutePathOfSchedulingProcessPIDFile = $absolutePathOfCronDirectory."/download-instagram-photos-scheduler-pid.dat"; 
	
	if (file_exists($absolutePathOfSchedulingProcessPIDFile))
	{
		$currentlyRunningSchedulingProcessPID = file_get_contents($absolutePathOfSchedulingProcessPIDFile);
		exec("kill {$currentlyRunningSchedulingProcessPID}");
		unlink($absolutePathOfSchedulingProcessPIDFile);
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
 * Creates the empty directory structures Museum Now needs to run. These include
 * - /cached/images
 * - /cached/profilephotos
 * - /log 
 */
function create_file_structures_for_museum_now()
{
	$directoriesToCreate = array(
		'cached',
		'cached/images',
		'cached/profilephotos',
		'log',
		'config'
	);
	$baseDir = determine_museum_now_root(FALSE);
	
	foreach ($directoriesToCreate as $directoryToCreate)
	{
		$fullPathOfDirectoryToCreate = $baseDir.$directoryToCreate;
		if (!file_exists($fullPathOfDirectoryToCreate))
		{
			mkdir($fullPathOfDirectoryToCreate);
		}
	}
}

/**
 * Resets the Museum Now installation 
 */
function reset_installation()
{
	stop_instagram_downloader();
	@unlink(realpath(dirname(__FILE__)."/../config/config.json"));
	
	$pathOfCachedImagesDirectory = realpath(dirname(__FILE__).'/../cached');
	$pathOfDigitalSignImagesDirectory = realpath(dirname(__FILE__).'/../digitalsign/img');
	
	if (file_exists($pathOfCachedImagesDirectory))
	{
		$imageFilePathsInCacheDirectory = rglob($pathOfCachedImagesDirectory.'/*.jpg');
		foreach ($imageFilePathsInCacheDirectory as $imageToDelete)
		{
			unlink($imageToDelete);
		}
	}
	if (file_exists($pathOfDigitalSignImagesDirectory))
	{
		$imageFilePathInDigitalSignDirectory = rglob($pathOfDigitalSignImagesDirectory.'/*.*');
		foreach ($imageFilePathInDigitalSignDirectory as $imageToDelete)
		{
			unlink($imageToDelete);
		}
	}
	file_put_contents(realpath(dirname(__FILE__)."/../get/instagram-photos.json"), make_json_pretty(json_encode(array())));
	file_put_contents(realpath(dirname(__FILE__)."/../get/instagram-users.json"), make_json_pretty(json_encode(array())));
	@unlink(realpath(dirname(__FILE__)."/../cron/download-instagram-photos-scheduler.sh"));
	@unlink(realpath(dirname(__FILE__)."/../cron/download-instagram-photos-scheduler-pid.dat"));
	
	// Delete /cached/images; /cached/profilephotos and /log 
	$directoriesToRemove = array(
		'cached',
		'cached/images',
		'cached/profilephotos',
		'log',
		'config'
	);
	$baseDir = determine_museum_now_root(FALSE);
	foreach ($directoriesToRemove as $directoryToRemove)
	{
		$fullPathOfDirectoryToRemove = $baseDir.$directoryToRemove;
		if (file_exists($fullPathOfDirectoryToRemove))
		{
			rrmdir($fullPathOfDirectoryToRemove);
		}
	}
	
}

/**
 * Generates the HTML to implement the Instagram widget.
 * @param int $width The width of the widget, defaults to 420px.
 * @param int $height The height of the widgetm defaults to 600px
 * @param int $theme The ability to specify a 'light' or 'dark' theme - defaults to 'light'.
 * @return string The generated HTML for the widget
 */
function generate_html_for_embeddable_instagram_widget($width = 420, $height = 600, $theme = 'light')
{
	return '<iframe src="'.get_url_root().'instagram-feed/?thm='.$theme.'" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';
}

/**
 * Outputs common Bootstrap code used in the HTML <head> sections for the 
 * setup screens.
 */
function output_setup_html_head()
{
	$html = '	
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1.0">
						<meta name="description" content="">
						<meta name="author" content="">
						<link rel="shortcut icon" href="../../docs-assets/ico/favicon.png">

						<title>Museum Now - Installation</title>

						<!-- Bootstrap core CSS -->
						<link href="../css/bootstrap.css" rel="stylesheet">
						<!-- Bootstrap theme -->
						<link href="../css/bootstrap-theme.min.css" rel="stylesheet">

						<!-- Custom styles -->
						<link href="../css/style.css" rel="stylesheet">

						<!-- Just for debugging purposes. Don\'t actually copy this line! -->
						<!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
						
						<!-- jQuery! -->
						<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
						<script src="../js/script.js" ></script>

						<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
						<!--[if lt IE 9]>
						<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
						<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
						<![endif]-->
					</head>
		';
	echo $html;
}

/**
 * Displays the file permissions error message as HTML 
 */
function output_permissions_error()
{
	$html = '
		<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Museum Now needs your permission!</h1>
				<p>Museum Now needs to be able to write files to your Web Server in order to function correctly. You are most likely receiving this message because it doesn\'t have permission to write files on the server.</p>
				<p>In order to continue, you will need to grant your Web Server and PHP processes <b>read</b> and <b>write</b> access to the following directories:</p>
				<ul>
					<li><code>'.get_url_root().'cron/</code></li>
					<li><code>'.get_url_root().'get/</code></li>
				</ul>
				<p>Most likely you will need to use the <code>chmod</code> command or some other tool to manage file permissions on the server.</p>
				<p><a href="#" class="btn btn-primary btn-lg" role="button" onclick="window.location.reload(true)">OK, I fixed the permissions. Let\'s Go!</a></p>
			</div>
		</div>
	</body>
			  ';
	echo $html;
}

/**
 * Redirects the user to the installation screen if Museum Now is not set-up.
 */
function redirect_to_setup_page_if_not_installed()
{
	global $suppressSetupPageRedirect;
	if (!museum_now_is_installed() && !isset($suppressSetupPageRedirect) && !is_running_from_command_line())
	{
		$redirectURL = determine_museum_now_root()."setup";
		header("Location: {$redirectURL}");
	}
}

/**
 * Stores a session variable indicating that Museum Now is being setup 
 */
function establish_setup_session()
{
	session_start();
	$_SESSION['setup-running'] = TRUE;
}

/**
 * Determines if a tutorial setup session is running
 * @return boolean TRUE if it is running, otherwise FALSE 
 */
function setup_session_is_running()
{
	session_start();
	return isset($_SESSION['setup-running']);
}

/**
 * Detects if the user is currently running through the tutorial setup, and 
 * if not,  
 */
function go_to_tutorial_introduction_if_not_in_setup_session()
{
	if(!setup_session_is_running())
	{
		$redirectURL = determine_museum_now_root()."setup/intro";
		header("Location: {$redirectURL}");
	}
}

/**
 * Checks if Museum Now can write / place data in its required directories.
 * @return boolean TRUE if permissions are okay, otherwise, FALSE.
 */
function permissions_ok()
{
	$filePathsToTest = array(
		realpath(dirname(__FILE__)).'/../cron/',
		realpath(dirname(__FILE__)).'/../get/'
	);
	foreach ($filePathsToTest as $filePathToTest)
	{
		if(!@file_put_contents($filePathToTest."test", "test"))
		{
			return FALSE;
		}
		if (@!file_get_contents($filePathToTest."test"))
		{
			return FALSE;
		}
		unlink($filePathToTest."test");
	}
	return TRUE;
}

?>