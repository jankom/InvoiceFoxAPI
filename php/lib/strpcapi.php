<?php

class StrpcAPI {
	
	var $apitoken = "";

	function StrpcAPI($token) {
		$this->apitoken = $token;
	}

	function call($resource, $method, $args) {

		$data = is_string($args) ? $args : $this->dictToParams($args, "", "&");

		$header = "POST /API?_r={$resource}&_m={$method} HTTP/1.1\r\n".         //todo -- domain is hardcoded now! FIX!!
			"Host:www.invoicefox.com\r\n".										//separate function for post (and get req with basic auth)
			"Content-Type: application/x-www-form-urlencoded\r\n".				//todo -- check encoding, should we add get case
			"User-Agent: PHP-invfox-client\r\n".
			"Content-Length: " . strlen($data) . "\r\n".
			"Authorization: Basic ".base64_encode($this->apitoken.':x')."\r\n". // todo -- enable when we do basic auth on srv
			"Connection: close\r\n\r\n";

		$result = '';
		$fp = fsockopen ('ssl://www.invoicefox.com', 443, $errno, $errstr, 30);  // todo -- turn to api.invoicefox.com
		if (!$fp) {
			// HTTP ERROR
		} else {
			//echo "<br/><div class='type'>request</div><pre class='req'>"; print_r($header . $data); echo "</pre>";
			fputs ($fp, $header . $data);
			while (!feof($fp)) {
				$result .= fgets ($fp, 128);
			}
			fclose ($fp);
		}
		//print_r($result);
		$resultD = str_replace("'", '"', trim(substr($result, strpos($result, "\r\n\r\n") + 4)));
		return new StrpcRes(json_decode($resultD, true));

	}

	function dictToParams($array, $startWith='?', $delim='&amp;')
	{
		$r = array();
		foreach ($array as $key => $val)
		{
			$r[] = "$key=$val";
		}
		return $startWith . implode($delim, $r);
	}
	

}

class StrpcRes {
	
	var $res;
	
	function StrpcRes($res) {
		$this->res = $res;
	}

	function isErr() { return $this->res[0] != 'ok'; }
	
	function isOk() { return $this->res[0] == 'ok'; }

	function getErr() { return print_r($this->res, true); }

	function getData() { return $this->res[1]; }

}

?>
