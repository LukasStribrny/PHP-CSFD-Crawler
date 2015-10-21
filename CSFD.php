<?php
class CSFD {
	
	public $download_folder;
	public $download_id;
	public $download_min_id = 1;
	public $download_max_id;
	public $download_url;
	public $base_url;
	public $time_stamp_folder;
	public $waiting_time;
	public $set_proxy;
	public $set_debug = true;
	
	public function __construct(){
		date_default_timezone_set('Europe/London');
	}
		
	public function download_to($download_folder){
		$this->download_folder = $download_folder;
	}
	
	public function download_id($download_id){
		$this->download_id = $download_id;
	}
	public function download_max_id($download_max_id){
		$this->download_max_id = $download_max_id;
	}
	
	public function download_url($download_url){
		$this->download_url = $download_url;
	}
	
	public function base_url($base_url){
		$this->base_url = $base_url;
	}
	
	public function time_stamp_folder($time_stamp_folder){
		$this->time_stamp_folder = $time_stamp_folder;
	}
	
	public function waiting_time($waiting_time){
		$this->waiting_time = $waiting_time;
	}
	
	public function set_proxy($set_proxy){
		$this->set_proxy = $set_proxy;
	}
	
	public function percentage($max, $current, $precision=2) {
		$res = round( ($current / $max) * 100, $precision );
		return $res.'%';
	}
	
	public function ConvertSeconds($seconds){
	$ret = "";
	/*** get the days ***/
	$days = intval(intval($seconds) / (3600*24));
	if($days> 0){
		if ($days==1){
			$ret .= $days."den ";
		}elseif ($days>1 OR $days<5){
			$ret .= $days."dny ";
		}else{
			$ret .= $days."dnu ";
		}
	}
	/*** get the hours ***/
	$hours = (intval($seconds) / 3600) % 24;
	if($hours > 0){
	   if (strlen($hours) == 1 ){
		  $hours = '0'.$hours;
	   }
		$ret .= "$hours:";
	}else{
		$ret .= "00:";
	}

	/*** get the minutes ***/
	$minutes = (intval($seconds) / 60) % 60;
	if($minutes > 0){
	   if (strlen($minutes) == 1 ){
		  $minutes = '0'.$minutes;
	   }
		$ret .= "$minutes:";
	}else{
		$ret .= "00:";
	}

	/*** get the seconds ***/
	$seconds = intval($seconds) % 60;
	if ($seconds > 0) {
	   if (strlen($seconds) == 1 ){
		  $seconds = '0'.$seconds;
	   }
		$ret .= "$seconds";
	}else{
		$ret .= "00";
	}

	return $ret;
	}
	
	public function showInfo(){
		if(file_exists($this->time_stamp_file)){
			$get_last_run_time = file_get_contents($this->time_stamp_file);
			file_put_contents($this->time_stamp_file,time());
		}else{
			file_put_contents($this->time_stamp_file,time());
			$get_last_run_time = '--> Jeste nikdy nespusteno <--';
		}
		if (is_numeric($get_last_run_time)){
			$last_time_run = date('d.m.Y H:i:s', $get_last_run_time);
		}else{
			$last_time_run = $get_last_run_time;
		}
		$final_time = ($this->download_max_id * $this->waiting_time);
		$final_timestamp = ($this->download_max_id * $this->waiting_time) + time();
		if (file_exists($this->record_file)){
			$this->last_number = file_get_contents($this->record_file);
			$current_time = ($this->last_number * $this->waiting_time);
			$finish_timestamp = $final_timestamp - $current_time;
			$finish_time = $final_time - $current_time;
			$rest_of_films = $this->download_max_id - $this->last_number;
		}else{
			$finish_timestamp = $final_timestamp;
			$finish_time = $final_time;
			$rest_of_films = $this->download_max_id;
			$this->last_number = 0;
			$current_time = 0;
		}
		$this->final_date = date('d.m.Y H:i:s', $finish_timestamp);
		$speed = $this->waiting_time;
		$PM1 = (60 / $speed);
		$PH = ($PM1*60);
		$PD = $PH*24;
		$PM2 = $PD*30;
		$header = 'Cekaci doba pro kazdou stahovanou stranku : '.$speed." sekund\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= 'Rychlost stahovani :'."\n";
		$header .= $PM1.' stranek/min'."\n";
		$header .= $PH.' stranek/hodinu'."\n";
		$header .= $PD.' stranek/den'."\n";
		$header .= $PM2.' stranek/mesic'."\n\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= '[Datum spusteni stahovani: '.date('d.m.Y H:i:s', time()).']'."\n";
		$header .= '[Celkovy uplynuly cas stahovani: '.$this->ConvertSeconds($current_time).']'."\n";
		$header .= '[ Hotovo : '.$this->percentage($this->download_max_id,$this->last_number).']'."\r\n";
		$header .= '[Posledni spusteni stahovani: '.$last_time_run.']'."\n";
		$header .= '[Den ukonceni stahovani: '.$this->final_date.']'."\n";
		$header .= '[Potrebny celkovy cas ke stazeni vsech dat: '.$this->ConvertSeconds($finish_time).']'."\n\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= '[Celkovy pocet stranek: '.$this->download_max_id.']'."\n".'[Pocet stahnutych dat : '.$this->last_number.']'."\n".'[Zbyvajici pocet stranek : '.$rest_of_films.']'."\n\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		if($this->set_debug){
			echo $header;
		}
		sleep(10);
	}
	
	public function download_run(){
		if(!$this->download_folder){
			echo 'Please set download folder!'."\n\r";
			sleep(3);
			die();
		}
		if(!file_exists($this->download_folder)){
			mkdir($this->download_folder,true);
			mkdir($this->download_folder.'/info',true);
			mkdir($this->download_folder.'/html',true);
			mkdir($this->download_folder.'/status',true);
			mkdir($this->download_folder.'/time',true);
		}
		if(!$this->time_stamp_folder){
			echo 'Please set time_stamp folder!'."\n\r";
			sleep(3);
			die();
		}
		if(!file_exists($this->time_stamp_folder)){
			mkdir($this->time_stamp_folder,true);
		}
		$this->time_stamp_file = realpath($this->time_stamp_folder).'/time.info';
		$this->record_file = realpath($this->time_stamp_folder).'/record.info';
		if($this->download_url){
			$page = $this->get_page($this->download_url);
			$csfd_id = uniqid();
			$hash = sha1($page['csfd_url_location']);
					if($page['csfd_url_status']=='con_error'){/*do nothing*/}else{
						if($this->set_proxy){
							file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location'],false,stream_context_get_default()));
						}else{
							file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location']));
						}
					}
				file_put_contents(realpath($this->download_folder).'/info/'.$csfd_id.'.info',$page['csfd_url_location']);
				file_put_contents(realpath($this->download_folder).'/status/'.$csfd_id.'_status.info',$page['csfd_url_status']);
				file_put_contents(realpath($this->download_folder).'/time/'.$csfd_id.'_time.info',time());
				file_put_contents($this->time_stamp_file,time());
				file_put_contents($this->record_file,$csfd_id);
		}else{
			if(!$this->base_url){
				echo 'Please set base_url!'."\n\r";
				sleep(3);
				die();
			}
			$count = 1;
			if($this->download_max_id){
				$this->showInfo();
				for($csfd_id=$this->download_min_id;$csfd_id<=$this->download_max_id;$csfd_id++){
					if(!$this->waiting_time){
						echo 'Please set waiting_time!'."\n\r";
						sleep(3);
						die();
					}
					if($this->last_number<$csfd_id){
						$page = $this->get_page($this->base_url.$csfd_id);
						$hash = sha1($page['csfd_url_location']);
						if($page['csfd_url_status']=='con_error'){/*do nothing*/}else{
							if($this->set_proxy){
								file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location'],false,stream_context_get_default()));
							}else{
								file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location']));
							}
						}
						file_put_contents(realpath($this->download_folder).'/info/'.$csfd_id.'.info',$page['csfd_url_location']);
						file_put_contents(realpath($this->download_folder).'/status/'.$csfd_id.'_status.info',$page['csfd_url_status']);
						file_put_contents(realpath($this->download_folder).'/time/'.$csfd_id.'_time.info',time());
						file_put_contents($this->time_stamp_file,time());
						file_put_contents($this->record_file,$csfd_id);
						if($this->set_debug){
							echo '{'.$count++.'} |'.$this->percentage($this->download_max_id,$csfd_id).'| [stranka cislo: '.$csfd_id.']'."\n";
							echo '[Ocekavany cas dokonceni : '.$this->final_date.']'."\n";
							echo '--------------------------------------------------------------------------------'."\n";
						}
						sleep($this->waiting_time);
					}
					
				}
			}else{
				if(!$this->download_id){
						echo 'Please set download_id or download_max_id!'."\n\r";
						sleep(3);
						die();
				}
				for($csfd_id=$this->download_min_id;$csfd_id<=$this->download_id;$csfd_id++){
					if($this->download_id==$csfd_id){
						$page = $this->get_page($this->base_url.$csfd_id);
						$hash = sha1($page['csfd_url_location']);
						if($page['csfd_url_status']=='con_error'){/*do nothing*/}else{
							if($this->set_proxy){
								file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location'],false,stream_context_get_default()));
							}else{
								file_put_contents(realpath($this->download_folder).'/html/'.$csfd_id.'_'.$hash.'.html',file_get_contents($page['csfd_url_location']));
							}
						}
						file_put_contents(realpath($this->download_folder).'/info/'.$csfd_id.'.info',$page['csfd_url_location']);
						file_put_contents(realpath($this->download_folder).'/status/'.$csfd_id.'_status.info',$page['csfd_url_status']);
						file_put_contents(realpath($this->download_folder).'/time/'.$csfd_id.'_time.info',time());
						file_put_contents($this->time_stamp_file,time());
						file_put_contents($this->record_file,$csfd_id);
						if($this->set_debug){
							echo '{'.$count++.'} |'.$this->percentage($this->download_id,$csfd_id).'| [stranka cislo: '.$csfd_id.']'."\n";
							echo '--------------------------------------------------------------------------------'."\n";
							sleep(3);
						}
					}
				}
			}
		}
	}
	
	public function get_page($get_page){
		if($this->set_proxy){
			if(!is_array($this->set_proxy)){
						echo 'Your proxy settings must be in array!'."\n\r";
						sleep(3);
						die();
			}
			if(!empty($this->set_proxy)){
				$proxy_settings = $this->set_proxy[array_rand($this->set_proxy,1)];
				//print_r($proxy_settings);
				$PROXY_HOST = $proxy_settings['PROXY_HOST']; // Proxy server address
				$PROXY_PORT = $proxy_settings['PROXY_PORT'];    // Proxy server port
				$PROXY_USER = $proxy_settings['PROXY_USER'];    // Username
				$PROXY_PASS = $proxy_settings['PROXY_PASS'];   // Password
				// Username and Password are required only if your proxy server needs basic authentication

				$auth = base64_encode("$PROXY_USER:$PROXY_PASS");
				stream_context_set_default(
				 array(
				  'http' => array(
				   'proxy' => "tcp://$PROXY_HOST:$PROXY_PORT",
				   'request_fulluri' => true,
				   'header' => "Proxy-Authorization: Basic $auth"
				   // Remove the 'header' option if proxy authentication is not required
				  )
				 )
				);
			}
		}
		$page_info = @get_headers($get_page);
		$page_location = @preg_grep('/^Location: (\w+)/i',$page_info);
		if(!empty($page_location)){
			$page_location_reset_array = array_values($page_location);
			$page_location_array = str_replace(array('Location: '),array(''),$page_location_reset_array);
			$Location = trim(end($page_location_array));
			$csfd_url['csfd_url_location'] = $Location;
			$csfd_url['csfd_url_status'] = 'page_alive';
		}else{
			if(empty($page_info)){
				$csfd_url['csfd_url_location'] = $get_page;
				$csfd_url['csfd_url_status'] = 'con_error';
			}else{
				$csfd_url['csfd_url_location'] = $get_page;
				$csfd_url['csfd_url_status'] = 'page_404';
			}
		}
		return $csfd_url;
	}
}
?>
