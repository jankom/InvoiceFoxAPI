<?php

class InvfoxAPI {

	var $api;

	function InvfoxAPI($apitoken) {
		$this->api = new StrpcAPI($apitoken);
	}

	function assurePartner($data) {
		$res = $this->api->call('partner', 'assure', $data);
		if ($res->isErr()) {
			// echo 'error' . $res->getErr();
		}
		return $res;
	}

	function createInvoice($header, $body) {
		$res = $this->api->call('invoice-sent', 'insert-into', $header);
		if ($res->isErr()) {
			echo 'error' . $res->getErr();
		} else {
			foreach ($body as $bl) {
				$resD = $res->getData();
				$bl['id_invoice_sent'] = $resD[0]['id'];
				$res2 = $this->api->call('invoice-sent-b', 'insert-into', $bl);
				if ($res->isErr()) {
					echo 'error' . $res->getErr();
				} 
			}
		}
		return $res;
	}

	function downloadPDF($id) {
		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"Authorization: Basic ".base64_encode($this->api->apitoken.':x')."\r\n" 
		  )
		);
		$context = stream_context_create($opts);
		$data = file_get_contents("https://www.invoicefox.com/API-pdf?id=$id&res=invoice-sent", false, $context);
		
		if ($data === false) {
			echo 'error downloading PDF';
		} else {
			$file = "../invoices/".$id.".pdf";
			file_put_contents($file, $data);
		}
	}

	function markPayed() {
		$res = $this->api->call('invoice-sent', 'mark-payed', array('id' => $id));
		if ($res->isErr()) {
			echo 'error' . $res->getErr();
		}
	}

}

?>
