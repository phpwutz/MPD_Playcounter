<?php
	require_once 'mpd.class.php';
	
	define ('MPDEVENTLISTENER_ONSTOP', 'onStop');
	define ('MPDEVENTLISTENER_ONPAUSE', 'onPause');
	define ('MPDEVENTLISTENER_ONSONGCHANGE', 'onSongChange');
	define ('MPDEVENTLISTENER_ONPLAY', 'onPlay');
	define ('MPDEVENTLISTENER_ONREPEATCHANGE', 'onRepeatChange');
	define ('MPDEVENTLISTENER_ONSHUFFLECHANGE', 'onShuffleChange');
	define ('MPDEVENTLISTENER_ONPLAYLISTCHANGE', 'onPlaylistChange');	
	define ('MPDEVENTLISTENER_ONCROSSFADECHANGE', 'onCrossfadeChange');
	define ('MPDEVENTLISTENER_ONVOLUMECHANGE', 'onVolumeChange');
	define ('MPDEVENTLISTENER_ONCONSUMECHANGE', 'onConsumeChange');
	define ('MPDEVENTLISTENER_ONSINGLECHANGE', 'onSingleChange');
	define ('MPDEVENTLISTENER_ONTIMECHANGE', 'onTimeChange');
	define ('MPDEVENTLISTENER_ONOUTPUTCHANGE', 'onOutputChange');		

	class mpdEventListener {
		
		private $mpd; // MPD connection
		
		private $checkTime;
	
		private $bindings = array(); 
		
		private $status = array();
		
		private $playlist = array();
		
		/**
		 * Constructor
		 * 
		 * @param mpd $mpdConnection
		 * @param int $checkTime in seconds
		 */
		public function mpdEventListener($mpdConnection, $checkTime = 1) {
			if ($mpdConnection->connected) {
				$this->mpd =& $mpdConnection;
				$this->checkTime = $checkTime;
			} else
				return false;
		}
		
		/**
		 * Para comenzar a escuchar cambios
		 */
		public function startListening() {
			set_time_limit(0);
			do {
				$this->status = $this->mpd->GetStatus();
				$this->playlist = $this->mpd->GetPlaylist();
				sleep($this->checkTime);
				$this->mpd->RefreshInfo();
				$this->_checkStatus($this->mpd->GetStatus(), $this->mpd->GetPlaylist());
			} while (true);
		}
		
		/**
		 * Devuelve los segundos en que se escucharán los cambios
		 * 
		 * @return int
		 */
		public function getCheckTime() {
			return $this->$checkTime;
		}
		
		/**
		 * Establece los segundos en que se escucharán los cambios
		 * 
		 * @param int $checkTime
		 */
		public function setCheckTime(string $checkTime) {
			$this->checkTime = $checkTime;
		}
	
		/**
		 * Devuelve los bindings
		 * 
		 * @return array
		 */
		public function getBindings() {
			return $this->bindings;
		}
		
		/**
		 * Establece los bindings
		 * 
		 * @param array $bindings
		 */
		public function setBindings(string $bindings) {
			$this->bindings = $bindings;
		}
		
		/**
		 * Bind a function to an event
		 * 
		 * @param string $event
		 * @param string $function
		 */
		public function bind($event, $function) {
			$this->bindings[$event][] = $function;
			return true;
		}
		
		/**
		 * Unbind a function from an event. Return if it existed and done.
		 * 
		 * @param string $event
		 * @param string $function
		 */
		public function unbind($event, $function) {
			if ($this->bindings[$event]) {
				$func_key = array_search($function,$this->bindings[$event]);
				if ($func_key) {
					unset($this->bindings[$event][$func_key]);
					return true;
				} else {
					return false;
				}
			} else
				return false;
		}
		
		private function _checkStatus($newStatus, $newPlaylist) {
			$change_status = array_diff_assoc($newStatus,$this->status);
			
			
			foreach ($change_status as $attr => $value) {
				switch ($attr) {
					case 'volume':
						$this->_triggerEvent(MPDEVENTLISTENER_ONVOLUMECHANGE,$this->status['volume'],$value);
						break;
					case 'repeat':
						$this->_triggerEvent(MPDEVENTLISTENER_ONREPEATCHANGE,$this->status['repeat'],$value);
						break;
					case 'random':
						$this->_triggerEvent(MPDEVENTLISTENER_ONSHUFFLECHANGE,$this->status['random'],$value);
						break;
					case 'single':
						$this->_triggerEvent(MPDEVENTLISTENER_ONSINGLECHANGE,$this->status['single'],$value);
						break;
					case 'consume':
						$this->_triggerEvent(MPDEVENTLISTENER_ONCONSUMECHANGE,$this->status['consume'],$value);
						break;
					case 'playlist':
						$this->_triggerEvent(MPDEVENTLISTENER_ONPLAYLISTCHANGE,$this->playlist,$newPlaylist);
						break;
					case 'xfade':
						$this->_triggerEvent(MPDEVENTLISTENER_ONCROSSFADECHANGE,$this->status['xfade'],$value);
						break;
					case 'state':
						switch ($value) {
							case MPD_STATE_PAUSED:
								$this->_triggerEvent(MPDEVENTLISTENER_ONPAUSE,$this->status['state']);
								break;
							case MPD_STATE_PLAYING:
								$this->_triggerEvent(MPDEVENTLISTENER_ONPLAY,$this->status['state']);
								break;
							case MPD_STATE_STOPPED:
								$this->_triggerEvent(MPDEVENTLISTENER_ONSTOP,$this->status['state']);
								break;
						}
						break;
					case 'songid':
						$songFile = $newPlaylist['files'][$newStatus['song']]['file'];
						$this->_triggerEvent(MPDEVENTLISTENER_ONSONGCHANGE,$songFile);
						break;
					case 'time':
						$this->_triggerEvent(MPDEVENTLISTENER_ONTIMECHANGE,$this->status['time'],$value);
						break;
				}
			}
		}
		
		private function _triggerEvent($event) {
			if (isset($this->bindings[$event])) {
				$args = func_get_args();
				array_shift($args);
				foreach ($this->bindings[$event] as $func) {
					//echo "calling " . $func; 
					if (function_exists($func)) {
						call_user_func_array($func,$args);
					}
				}
			}
		} 
		
	} 
	
?>