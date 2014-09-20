<?php
set_time_limit(300);
error_reporting(E_ALL);
$root = str_replace('\\','/',(isset($argv[1])?$argv[1]:dirname(__file__)));
if(@$root{strlen($root)-1}!='/') @$root.='/';
$null = new scrapemyass($root, (isset($argv[2])?$argv[2]:null));

class scrapemyass {
	private $curl;
	private $overwrite = true;
	private $curls     = array();
	private $pages     = array();
	private $auto      = 'O';// Automatically overwrite proxies.txt if it already exists in the target directory
	public  $proxies   = array();
	
	public function __construct($root, $max=25) {
		$this->root = is_dir($root)?$root:'./';
		print "Working directory is\n    {$this->root}\n";
		print "- - - - - - - - - - - - - - - - - - - -\n";// I'm aware "$this->root" is dynamic therefore this may not be the right size
		if(file_exists($this->root.'proxies.txt') && $this->auto==false) {
			while(true) {
				print "Proxies.txt already exists...\n";
				print "    Overwrite or Append to file? (O/A/QUIT): ";
				$return = strtoupper(trim(fgets(STDIN)));
				if(in_array($return,array('O','A','QUIT'))) break;
			} switch($return) {
				case 'O': $this->overwrite=true; break;
				case 'A': $this->overwrite=false; break;
				case 'QUIT': exit; break;
			}
			print "- - - - - - - - - - - - - - - - - -\n";
		} elseif($this->auto!=false) {
			$this->overwrite = $this->auto;
		}
		$this->curl_init($max);
		$this->scrape();
		$this->write();
		exit;
	}
	
	public function write() {
		$p = 0;
		switch($this->overwrite) {
			case true:
				$p = count($this->proxies);
				file_put_contents($this->root.'proxies.txt', implode("\n", $this->proxies));
			break;
			case false:
				$current = file_get_contents($this->root.'proxies.txt');
				foreach($this->proxies as $proxy) 
					if(!is_numeric(strpos($current,$proxy))) {$current=$proxy."\n".$current;$p++;}
				file_put_contents($this->root.'proxies.txt', $current);
			break;
		}
		print "{$p} proxies have been written to\n    \"{$this->root}proxies.txt\"\n    - Closing in 30 seconds...\n";
		print "- - - - - - - - - - - - - - - - - - - -\n";
//		exit(sleep(30));
	}
	
	public function scrape($styles=array(), $tstyles=array()) {
		foreach($this->pages as $p) {
			preg_match("/<\/thead>(.*)<\/table>/Umis", $p, $page);
			if(!isset($page[1])) continue;
			preg_match_all("/<tr[^>]*>(.*)<\/tr>/Umis", $page[1], $page);
			foreach($page[1] as $content) {
				preg_match("/<style>(.*)<\/style>/Umis", $content, $style);
				preg_match_all("/\.(.*)\{display\:(.*)\}/Umis", $style[1], $style);
				foreach($style[1] as $k=>$s) {
					$styles[$s] = $style[2][$k];
					if($styles[$s]=='none') $tstyles[] = $s;
				}
				preg_match("/<\/style>(.*)<\/td>/Umis", $content, $ip);
				$ip = str_replace("<span></span>", "", $ip[1]);
				$ip = preg_replace("/<[\w]* style=\"display:none\">[\w]*<\/[\w^>]*>/","",$ip);
				if($tstyles) {
					$t  = count($tstyles) == 1 ? $tstyles[0] : "(" . implode("|", $tstyles) . ")";
					$ip = preg_replace("/<[\w]* class=\"{$t}\">[\w]*<\/[\w^>]*>/","",$ip);
				}
				$ip = preg_replace("/<[^>]*>/","",$ip);
				$port = explode("<td>\n", $content);
				$port = substr($port[1], 0, strpos($port[1],"</td>"));
				$this->proxies[] = $ip.':'.$port;
			}
		}
	}
	
	public function curl_init($max, $null=null) {
		$max = (!is_numeric($max)||$max>25||$max<1)?25:$max;
		print "Fetching {$max} records..\n";
		$this->curl = curl_multi_init();
		for($i=1; $i<=$max; $i++) {
			$curl = curl_init("http://hidemyass.com/proxy-list/{$i}/");
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($this->curl, $curl);
			$this->curls[] = $curl;
		} do curl_multi_exec($this->curl, $null); while($null);
		foreach($this->curls as $curl) {
			$return = curl_multi_getcontent($curl);
			if(is_numeric(strpos($return, '<html>'))) {
				$this->pages[] = $return;
			}
		}
		print "    Complete.\n";
		print "    xSidewinderx.\n";
		print "- - - - - - - - - - - - - - - - - - - -\n";
	}
	
}
