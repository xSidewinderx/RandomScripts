<?php
do print("Enter pastebin link: ") and $link = fgets(STDIN); while (!@file_get_contents($link));
$cc = curl_multi_init();
foreach(file("") as $u) { /* PROXY LIST */
	$c = curl_init($link);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
	curl_setopt($c, CURLOPT_COOKIE, "cookie_key=1; realuser=1");
	curl_setopt($c, CURLOPT_TIMEOUT, 30);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($c, CURLOPT_PROXY, trim($u));
	curl_multi_add_handle($cc, $c);
}
$n = null;
do curl_multi_exec($cc, $n); while ($n >= 1);
