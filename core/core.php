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

// Turn this on for debugging!
error_reporting(E_ALL);

date_default_timezone_set('Australia/Sydney');
define('PROXY_SETTINGS_KEYS_SERIALIZED', serialize(array('proxy-server', 'proxy-port', 'proxy-username', 'proxy-password')));
define('INSTAGRAM_WEBSITE', "http://instagram.com/");
define('PHOTOS_FROM_OWN_FEED_API_ENDPOINT', 'https://api.instagram.com/v1/users/self/media/recent');
define('LIKED_PHOTOS_API_ENDPOINT', 'https://api.instagram.com/v1/users/self/media/liked');
define('AMOUNT_OF_PHOTOS_TO_DOWNLOAD', '16');

// Define file locations
define('ROOT_DIR', realpath(dirname(__FILE__)."/../"));
define('STORE_DIR', realpath(dirname(__FILE__)."/../store/"));
define('CONFIG_DIR', realpath(dirname(__FILE__)."/../store/config/"));
define('CONFIG_FILE', realpath(dirname(__FILE__)."/../store/config")."/config.json");
define('PROXY_FILE', realpath(dirname(__FILE__)."/../store/config")."/proxy.json");
define('DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_SHELL_SCRIPT', realpath(dirname(__FILE__)."/../store/config")."/download-instagram-photos-scheduler.sh");
define('DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID', realpath(dirname(__FILE__)."/../store/config")."/download-instagram-photos-scheduler.pid");
define('LOG_FILE', realpath(dirname(__FILE__)."/../store/config")."/log.json");
define('DIGITALSIGN_ASSETS_DIR', realpath(dirname(__FILE__)."/../store/digitalsign-assets/"));
define('DIGITALSIGN_ASSETS_DIR_RELATIVE_TO_DIGITALSIGN', "../store/digitalsign-assets");
define('DIGITALSIGN_ASSETS_METADATA_FILE', realpath(dirname(__FILE__)."/../store/digitalsign-assets")."/metadata.json");
define('CACHED_DIR', realpath(dirname(__FILE__)."/../store/cached/"));
define('CACHED_DIR_RELATIVE_TO_DIGITALSIGN', "../store/cached");
define('INSTAGRAM_PHOTOS_DIR', realpath(dirname(__FILE__)."/../store/cached/instagram-photos/"));
define('INSTAGRAM_PHOTOS_DIR_RELATIVE_TO_DIGITALSIGN', "../store/cached/instagram-photos");
define('INSTAGRAM_PHOTOS_METADATA_FILE', realpath(dirname(__FILE__)."/../store/cached/instagram-photos")."/metadata.json");
define('INSTAGRAM_USERS_DIR', realpath(dirname(__FILE__)."/../store/cached/instagram-users/"));
define('INSTAGRAM_USERS_DIR_RELATIVE_TO_DIGITALSIGN', "../store/cached/instagram-users");
define('INSTAGRAM_USERS_METADATA_FILE', realpath(dirname(__FILE__)."/../store/cached/instagram-users")."/metadata.json");

/**
 * All pages / sections on Museum Now redirect to the setup page if Museum
 * Now is not installed (i.e., /store/config/config.json is not there). 
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
 * Determine if a directory is empty from 
 * {@link http://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty}
 * @param string $dir The directory to check.
 * @return null|boolean TRUE if empty, FALSE if not empty, NULL if not readable
 */
function is_dir_empty($dir) 
{
	if (!is_readable($dir))
	{
		return NULL;
	}
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) 
	{
		if ($entry != "." && $entry != "..") 
		{
			return FALSE;
		}
	}
	return TRUE;
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
		$fileSystemFolderName = array_pop($rootDirectoryInFileSystemComponents);
		
		// Determine the document root on the server
		$pathOnServer = $_SERVER['PHP_SELF'];
		$currentPath = null;
		while ($currentPath !=  $fileSystemFolderName)
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
	if ($root = get_metadata('museum-now-root'))
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
	if (get_metadata('museum-now-root'))
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
	
	// Open JSON log file if it exists
	$logJSON = array();
	if (file_exists(LOG_FILE))
	{
		$logJSON = json_decode(file_get_contents(LOG_FILE));
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
	
	// Store $logJSON as prettified JSON in /store/config/log.json
	file_put_contents(LOG_FILE, make_json_pretty(json_encode($logJSONTruncated)));
	@chmod(LOG_FILE, 0777);
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
 * Retrieves a value from a metadata file for a given key. By default,
 * it retrieves key-value pairs from the general configuration file
 * (CONFIG_FILE) although it can optionally retrieve / set key value
 * pairs from other metadata files as well. 
 * 
 * @param string $key The key to retrieve the value for.
 * 
 * @param string $configFile The full file path of the metadata file to set / get
 * keys from, defaults to CONFIG_FILE
 * 
 * @return string The value for the given key, or FALSE if the value doesn't
 * exist.
 */
function get_metadata($key, $configFile = CONFIG_FILE)
{
	if (file_exists($configFile))
	{
		$configData = json_decode(file_get_contents($configFile));
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
 * 
 * @param string $val The accompanying value.
 * 
 * @param string $configFile The full file path of the config to set / get
 * keys from, defaults to CONFIG_FILE
 * 
 * @return int The number of bytes written to /store/config/config.json, or 
 * boolean FALSE on failure.
 */
function set_metadata($key, $val, $configFile = CONFIG_FILE)
{
	if(file_exists($configFile))
	{
		$configData = json_decode(file_get_contents($configFile));
	}
	else
	{
		$configData = (object) array();
	}
	$configData->$key = $val;
	file_put_contents($configFile, make_json_pretty(json_encode($configData)));
	@chmod($configFile, 0777);
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
	// $absolutePathOfSchedulingProcessPIDFile = $absolutePathOfCronDirectory."/download-instagram-photos-scheduler-pid.dat"; 
	
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
		
	file_put_contents(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_SHELL_SCRIPT, $scheduleFileContents);
	@chmod(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_SHELL_SCRIPT, 0777);

	$commandToExecute = DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_SHELL_SCRIPT." >> ".DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID." 2>&1 &";
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
	if (file_exists(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID))
	{
		$currentlyRunningSchedulingProcessPID = file_get_contents(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID);
		if (is_numeric(trim($currentlyRunningSchedulingProcessPID)))
		{
			exec("kill {$currentlyRunningSchedulingProcessPID}");
			file_put_contents(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID, "");
			return TRUE;
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
 * Resets the Museum Now installation 
 * 
 * @param boolean $resetProxySettings If set to FALSE, proxy settings defined
 * in /store/config/proxy.json are NOT reset (by default, proxy settings are reset).
 */
function reset_installation($resetProxySettings = TRUE)
{
	stop_instagram_downloader();
	$pathOfCachedImagesDirectory = CACHED_DIR;
	$pathOfDigitalSignImagesDirectory = DIGITALSIGN_ASSETS_DIR;
	
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
	file_put_contents(CONFIG_FILE, make_json_pretty(json_encode((object) array())));
	file_put_contents(LOG_FILE, make_json_pretty(json_encode(array())));
	file_put_contents(INSTAGRAM_PHOTOS_METADATA_FILE, make_json_pretty(json_encode(array())));
	file_put_contents(INSTAGRAM_USERS_METADATA_FILE, make_json_pretty(json_encode(array())));
	file_put_contents(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_SHELL_SCRIPT, '');
	file_put_contents(DOWNLOAD_INSTAGRAM_PHOTOS_SCHEDULER_PID, '');
	
	if ($resetProxySettings)
	{
		$keysToSetNullAndReset = unserialize(PROXY_SETTINGS_KEYS_SERIALIZED);
		foreach ($keysToSetNullAndReset as $keyToSetNullAndReset)
		{
			set_metadata($keyToSetNullAndReset, null, PROXY_FILE);
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
 * Displays the file permissions error message as a HTML page 
 */
function output_permissions_error()
{
	$html = '
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>Museum Now - Installation</title>

		<!-- Bootstrap core CSS -->
		<link href="css/bootstrap.css" rel="stylesheet">
		<!-- Bootstrap theme -->
		<link href="css/bootstrap-theme.min.css" rel="stylesheet">

		<!-- Custom styles -->
		<link href="css/style.css" rel="stylesheet">

		<!-- jQuery! -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
		<script src="js/script.js" ></script>

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>	
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Museum Now needs your permission!</h1>
				<p>Museum Now needs to be able to write files to your Web Server in order to function correctly. You are most likely receiving this message because it doesn\'t have permission to write files on the server.</p>
				<p>In order to continue, you will need to grant your Web Server and PHP processes <b>read</b> and <b>write</b> access to the following directories:</p>
				<p><code>'.STORE_DIR.'</code></p>
				<p>Most likely you will need to use the <code>chmod</code> command or some other tool to manage file permissions on the server.</p>
				<p><a href="#" class="btn btn-primary btn-lg" role="button" onclick="window.location.reload(true)">OK, I fixed the permissions. Let\'s Go!</a></p>
			</div>
		</div>
	</body>
			  ';
	echo $html;
}

/**
 * Displays the connectivity permissions error message as HTML 
 */
function output_connectivity_error()
{
	$html = '
		<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Cannot connect to Instagram.com</h1>
				<p>In order to continue, you will need to install Museum Now on a server that can connect to <b>Instagram.com</b> on the server-side, or configure your server such that it can access the Internet.</p>
				<p>If your server is sitting behind a proxy, you will need to set the appropriate proxy configuration details in <code>'.PROXY_FILE.'</code>. Please contact your server or IT administrator for these details</p>
				<p><a href="#" class="btn btn-primary btn-lg" role="button" onclick="window.location.reload(true)">OK, I fixed that. Let\'s Go!</a></p>
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
		 STORE_DIR,
		 CONFIG_DIR,
		 INSTAGRAM_PHOTOS_DIR,
		 INSTAGRAM_USERS_DIR
	);
	
	foreach ($filePathsToTest as $filePathToTest)
	{
		if(@!file_put_contents($filePathToTest."/test", "test"))
		{
			return FALSE;
		}
		if (@!file_get_contents($filePathToTest."/test"))
		{
			return FALSE;
		}
		unlink($filePathToTest."/test");
	}
	return TRUE;
}

/**
 * Checks to see if Museum Now can connect to the Instagram API 
 * @return boolean TRUE if it connect to the Instagram API
 */
function connectivity_ok()
{
	$httpStatus = null;
	file_get_contents_via_proxy(INSTAGRAM_WEBSITE, proxy_settings(), $httpStatus);
	if ($httpStatus == 200)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
 * Function that returns user defined proxy settings as defined in 
 * /store/config/proxy.json. Proxy settings need to be set if Museum Now operates
 * behind a firewall and needs to access Instagram via a proxy server. Note
 * that proxy.json is NOT erased when Museum Now resets.
 * 
 * The function returns the relevant key-value pairs from the proxy file if
 * proxy settings are set, or FALSE if no proxy settings are set.
 * 
 * @return boolean|array An array of key-value pairs of proxy settings if set 
 * within the config file, or FALSE if no proxy settings appear to be set.
 */
function proxy_settings()
{
	if (file_exists(PROXY_FILE))
	{
		$keysToRetrieve = unserialize(PROXY_SETTINGS_KEYS_SERIALIZED);
		$proxySettings = array();
		
		foreach ($keysToRetrieve as $keyToRetrieve)
		{
			if ($val = get_metadata($keyToRetrieve, PROXY_FILE))
			{
				if ($keyToRetrieve == 'proxy-server')
				{
					if (filter_var($val, FILTER_VALIDATE_URL) !== FALSE)
					{
						if (substr($val, -1) == "/")
						{
							$val = substr($val, 0, -1);
						}		
						$proxySettings[$keyToRetrieve] = $val;
					}
				}
				else if (!empty($val))
				{
					$proxySettings[$keyToRetrieve] = $val;
				}
			}
		}
		
		// If 'proxy-sever' is defined, but 'proxy-port' isn't, then set the
		// default port number of 8080
		if (isset($proxySettings['proxy-server']) && !isset($proxySettings['proxy-port']))
		{
			$proxySettings['proxy-port'] = "8080";
		}
		
		if (!empty($proxySettings))
		{
			return $proxySettings;
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
 * Configures a cURL handle for proxy settings (if set)
 * 
 * @param resource The cURL handle to configure
 * 
 * @param array $proxySettings An array of proxy settings, returned by 
 * proxy_settings()
 * 
 * @return resource The cURL handle, with the proxy settings applied
 */
function configure_curl_handle_for_proxy_settings($ch, $proxySettings = array())
{
	// Apply proxy settings if set
	if (isset($proxySettings['proxy-server']) && isset($proxySettings['proxy-port']))
	{
		curl_setopt($ch, CURLOPT_PROXY, $proxySettings['proxy-server'].":".$proxySettings['proxy-port']);
		curl_setopt($ch, CURLOPT_PROXYPORT, $proxySettings['proxy-port']);

		// Add authentication details if proxy-username and proxy-password
		// are ste
		if (isset($proxySettings['proxy-username']) && isset($proxySettings['proxy-password']))
		{
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxySettings['proxy-username'].":".$proxySettings['proxy-password']);
		}
	}
	return $ch;
}

/**
 * Extends PHP's file_get_contents() such that content requests can be made
 * through a proxy server. Note that this function only works on URLs
 * 
 * @param string $url The name of the URL to read.
 * 
 * @param mixed $proxySettings An array of proxy settings, returned by 
 * proxy_settings()
 * 
 * @param string $httpStatus Output parameter that returns the HTTP status of
 * the cURL request
 * 
 * @return mixed Returns the read data, or FALSE on failure.
 */
function file_get_contents_via_proxy($url, $proxySettings = FALSE, &$httpStatus = null)
{
	if (filter_var($url, FILTER_VALIDATE_URL))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if ($proxySettings)
		{
			$ch = configure_curl_handle_for_proxy_settings($ch, $proxySettings);
		}

		$data = curl_exec($ch);
				
		// Get HTTP Status. If 200 (OK), then return data, otherwise, return
		// FALSE
		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($httpStatus == 200)
		{
			return $data;
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

?>