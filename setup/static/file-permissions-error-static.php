<!DOCTYPE html>
<html lang="en">
	<?php require_once(realpath(dirname(__FILE__).'/../../core/core.php')); output_setup_html_head(); ?>
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1 class="fancy-title">Museum Now needs your permission!</h1>
				<p>Museum Now needs to be able to write files to your Web Server in order to function correctly. You are most likely receiving this message because it doesn't have permission to write files on the server.</p>
				<p>In order to continue, you will need to grant your Web Server and PHP processes <b>read</b> and <b>write</b> access to the following directories:</p>
				<ul>
					<li><code><?php echo determine_museum_now_root(FALSE)."config/" ?></code></li>
					<li><code><?php echo determine_museum_now_root(FALSE)."cron/" ?></code></li>
					<li><code><?php echo determine_museum_now_root(FALSE)."log/" ?></code></li>
					<li><code><?php echo determine_museum_now_root(FALSE)."cached/images/" ?></code></li>
					<li><code><?php echo determine_museum_now_root(FALSE)."cached/profilephotos/" ?></code></li>
				</ul>
				<p>Most likely you will need to use the <code>chmod</code> command or some other tool to manage file permissions on the server.</p>
				<p><a href="#" class="btn btn-primary btn-lg" role="button" onclick="window.location.reload(true)">OK, I fixed the permissions. Let's Go!</a></p>
			</div>
		</div>
	</body>
</html>
