<?php

require_once 'Arr.php';

class Http
{

	static function lsf_joinParams($a, $k, $v)
	{
		return $a .= "&$k=".urlencode(stripslashes($v)); 
	}

	static function makePostString($postData){
		$req = Arr::reducek($postData, array('Http', 'lsf_joinParams'));
		return 	"POST /cgi-bin/webscr HTTP/1.0\r\n" .
				"Content-Type: application/x-www-form-urlencoded\r\n" . 
				"Content-Length: " . strlen($req) . "\r\n\r\n" . $req;
	}
}

if (0)
{
	echo Http::makePostString(array('a' => '1', 'b' => '2'));
}


/*
	sfun makePostString($postData){
		$req = Arr::reducek($postData, ~($acc, $k, $v) { return $a .= "&$k=".urlencode(stripslashes($v)); });
		return 	"POST /cgi-bin/webscr HTTP/1.0\r\n" .
				"Content-Type: application/x-www-form-urlencoded\r\n" . 
				"Content-Length: " . strlen($req) . "\r\n\r\n" . $req;
	}
*/
?>