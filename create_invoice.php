<?php

class InvFoxAPI {

	function get( ) {
		$header = "GET /API.html?apitoken=9i7a6dnr4b0k82r6qaj583ssefy9otcp7am2dhr4&_r=partner&_m=select-all-safe&filter=all&page=0  HTTP/1.1\r\n".
			"Host:example.com\n".
			"Content-Type: application/x-www-form-urlencoded\r\n".
			"User-Agent: PHP-Code\r\n".
			"Content-Length: " . strlen($req) . "\r\n".
			"Authorization: Basic ".base64_encode($username.':'.$password)."\r\n".
			"Connection: close\r\n\r\n";

		//$req .= "&cmd=_initiate_query";

		$fp = fsockopen ('ssl://www.invoicefox.com', 443, $errno, $errstr, 30);
		if (!$fp) {
			// HTTP ERROR
		} else {
			fputs ($fp, $header);
			while (!feof($fp)) {
				$result .= fgets ($fp, 128);
			}
			fclose ($fp);
		}

	}

	function createInvoice() {


	}

}

print_r(InvFoxAPI::get());

?>