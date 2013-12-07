
	#!/bin/sh
	echo $$
	while [ 1 ]; do
		/usr/bin/php /Library/WebServer/Documents/museum-now/cron/download-instagram-photos.php
	   sleep 60
	done