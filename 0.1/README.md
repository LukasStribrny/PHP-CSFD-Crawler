# PHP-CSFD-Crawler
PHP CSFD Crawler is about downloading html pages from csfd.cz for later usage.
* For example you can separate the info in html and insert the information to database.*
But this feature is not included.
## How to use

### 1. Set download folder -> this is always required
$this->CSFD->download_to($download_folder);

### 2. Set one of three function to download page , for example by id or url

##### * if you want to download just one page with the id you provided*
$this->CSFD->download_id($download_id);

##### * if you want to download all pages until the reach of provided id*
$this->CSFD->download_max_id($download_max_id);

##### * if you downloading pages by max id you must set waiting time*
$this->CSFD->waiting_time($waiting_time);

##### *this is required when you only downloading pages by id and not by url*
$this->CSFD->base_url($base_url);
##### example : http://csfd.cz/film/ or http://csfd.cz/tvurce/

#### * if you want to dowload page by provided url*
$this->CSFD->download_url($download_url);

#### 3. *if you want to use proxy settings*
$this->CSFD->set_proxy($set_proxy);

##### example :
$set_proxy = array(
array('PROXY_HOST'=> Your_Proxy_IP, 'PROXY_PORT'=>Your_Proxy_Port ,'PROXY_USER'=>Your_Proxy_User_Name ,'PROXY_PASS'=>Your_Proxy_User_Pass ),
array('PROXY_HOST'=> Your_Second_Proxy_IP, 'PROXY_PORT'=>Your_Second_Proxy_Port ,'PROXY_USER'=>Your_Second_Proxy_User_Name ,'PROXY_PASS'=>Your_Second_Proxy_User_Pass )
);

Add another array of proxy settings if you want to use more proxy -> this is because of the freqential usage if you donwload a lot's of pages.The website has restriction access for IP.
The proxy is randomly selected.
If you include more proxy you will have more chance to not get banned.

#### 4.set debug to false if you don't want to see basic info about downloading
##### info : This is used only when you download pages by id
$this->CSFD->set_debug = false;

#### 5.The last thing you have to do is run the download.
$this->CSFD->download_run();

#### Important information : 
###### Run it trought php cli to get best results then trought browser.Because the browser will/may crash after some time if there is a too long request of download.
