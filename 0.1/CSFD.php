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
	public $folders_config = array('info_folder'=>'download/info','html_folder'=>'download/html','status_folder'=>'download/status','time_folder'=>'download/time','time_stamp_folder'=>'download/time_stamp');
	
	public function __construct(){
		date_default_timezone_set('Europe/London');
		$this->cli_proc_title(get_class($this));
	}
		
	public function download_folder($download_folder){
		$this->download_folder = $download_folder;
	}
	
	public function setupFolders(){
		foreach($this->folders_config AS $folder_name=>$folder_path){
			$folder = $this->download_folder.'/'.$folder_path;
			if(!file_exists($folder)){
				mkdir($folder,true);
			}
			$this->{$folder_name} = str_replace('\\','/',realpath($folder)).'/';
		}
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
			$ret .= $days."day ";
		}else{
			$ret .= $days."days ";
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
	
	public function cli_proc_title($proc_title){
		if(function_exists('cli_set_process_title')){
			cli_set_process_title($proc_title);
		}
	}
	
	public function showInfo(){
		if (file_exists($this->time_stamp_file)){
			$last_time_run = date('d.m.Y H:i:s', file_get_contents($this->time_stamp_file));
		}else{
			$last_time_run = '--> First time run <--';
		}
		file_put_contents($this->time_stamp_file,time());
		$final_time = ($this->download_max_id * $this->waiting_time);
		$final_timestamp = $final_time + time();
		if (file_exists($this->record_file)){
			$this->last_number = file_get_contents($this->record_file);
			$current_time = ($this->last_number * $this->waiting_time);
			$finish_timestamp = $final_timestamp - $current_time;
			$this->finish_time = $final_time - $current_time;
			$rest_of_films = $this->download_max_id - $this->last_number;
		}else{
			$finish_timestamp = $final_timestamp;
			$this->finish_time = $final_time;
			$rest_of_films = $this->download_max_id;
			$this->last_number = 0;
			$current_time = 0;
		}
		$info = get_class($this).' | [ Done : '.$this->percentage($this->download_max_id,$this->last_number).']';
			$this->cli_proc_title($info);
		$this->final_date = date('d.m.Y H:i:s', $finish_timestamp);
		$PMI = (60 / $this->waiting_time);
		$PH = ($PMI*60);
		$PD = ($PH*24);
		$PMO = ($PD*30);
		$header = 'Waiting time for each page to download : '.$this->waiting_time." seconds\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= 'Downloading speed :	'.$PMI.' pages/min'."\n";
		$header .= '			'.$PH.' pages/hour'."\n";
		$header .= '			'.$PD.' pages/day'."\n";
		$header .= '			'.$PMO.' pages/month'."\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= '[Date of run : '.date('d.m.Y H:i:s', time()).']'."\n";
		$header .= '[Elapsed run time of download : '.$this->ConvertSeconds($current_time).']'."\n";
		$header .= $info."\n";
		$header .= '[Date of last run : '.$last_time_run.']'."\n";
		$header .= '[Expected day to finish : '.$this->final_date.']'."\n";
		$header .= '[Expected run time to finish : '.$this->ConvertSeconds($this->finish_time).']'."\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		$header .= '[Number of pages to download : '.$this->download_max_id.']'."\n";
		$header .= '[Number of downloaded data : '.$this->last_number.']'."\n";
		$header .= '[Rest of pages to download : '.$rest_of_films.']'."\n";
		$header .= '--------------------------------------------------------------------------------'."\n";
		if($this->set_debug){
			echo $header;
			sleep(5);
		}
	}
	
	public function download_run(){
		if(!$this->download_folder){
			echo 'Please set download folder!'."\n\r";
			sleep(3);
			die();
		}
		$this->setupFolders();
		$this->time_stamp_file = $this->time_stamp_folder.'time.info';
		$this->record_file = $this->time_stamp_folder.'record.info';
		if($this->download_url){
				$this->cli_proc_title(get_class($this).' | Page : '.$this->download_url);
			$this->download_info();
		}else{
			if(!$this->base_url){
				echo 'Please set base_url!'."\n\r";
				sleep(3);
				die();
			}
			if($this->download_max_id){
				$this->showInfo();
				if($this->last_number>$this->download_min_id){
					$start_id = $this->last_number;
				}else{
					$start_id = $this->download_min_id;
				}
				for($csfd_id=$start_id;$csfd_id<=$this->download_max_id;$csfd_id++){
					if(!$this->waiting_time){
						echo 'Please set waiting_time!'."\n\r";
						sleep(3);
						die();
					}
					if($this->last_number<$csfd_id){
						$info = get_class($this).' | [Done : '.$this->percentage($this->download_max_id,$csfd_id).'] -> Page : '.$csfd_id.' <- [Finish date : '.$this->final_date.']';
							$this->cli_proc_title($info);
						$this->download_info($csfd_id);
						if($this->set_debug){
							echo $info."	\r";
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
					$info = get_class($this).' Downloader -> [Done : '.$this->percentage($this->download_id,$csfd_id).'] -> page : '.$this->download_id;
					$this->cli_proc_title($info);
					if($this->download_id==$csfd_id){
					$this->download_info($csfd_id);
					$this->cli_proc_title($info);
						if($this->set_debug){
							echo $info."	\r";
							sleep(3);
						}
					}
				}
			}
		}
	}
	
	public function download_info($csfd_id=false){
		if($csfd_id==true){
			$page = $this->get_page($this->base_url.$csfd_id);
		}else{
			$page = $this->get_page($this->download_url);
			$csfd_id = uniqid();
		}
				$hash = sha1($page['csfd_url_location']);
						if($page['csfd_url_status']=='con_error'){/*do nothing*/}else{
							if($this->set_proxy){
								$html_content = @file_get_contents($page['csfd_url_location'],false,stream_context_get_default());
								
							}else{
								$html_content = @file_get_contents($page['csfd_url_location']);
							}
							if($html_content==true){
								file_put_contents($this->html_folder.'/'.$csfd_id.'_'.$hash.'.html',$html_content);
							}else{
								$page['csfd_url_status'] = 'con_error';
							}
						}
						file_put_contents($this->info_folder.'/'.$csfd_id.'.info',$page['csfd_url_location']);
						file_put_contents($this->status_folder.'/'.$csfd_id.'_status.info',$page['csfd_url_status']);
						file_put_contents($this->time_folder.'/'.$csfd_id.'_time.info',time());
						file_put_contents($this->time_stamp_file,time());
						file_put_contents($this->record_file,$csfd_id);
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
