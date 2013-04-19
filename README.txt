HOW TO SET IT ALL UP:

* Make sure your mpd is up and running.
* install the python-mpd library included in this repository by calling "python setup.py install"
* put the autostart-initd.sh file to your init.d path and make it autostart
* configure the host, port and password of your mpd-instance in the following files:
	 * playcountListener.php
	 * sticker.py
* sudo service mpdplaycounter start

If you have questions or improvements feel free to write me :)