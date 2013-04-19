#! /bin/sh
# /etc/init.d/mpdplaycounter

touch /var/lock/mpdplaycounter

case "$1" in
	start)
		echo "Starting MPD Playcounter ... "
		php /var/lib/mpd/mpdplaycountstickers.php > /dev/null &
		;;
	stop)
		echo "Killing MPD Playcounter ..."
		killall php /var/lib/mpd/mpdplaycountstickers.php
		;;
	*)
		echo "Usage: /etc/init.d/mpdplaycounter {start|stop}"
		exit 1
		;;
esac
exit 0