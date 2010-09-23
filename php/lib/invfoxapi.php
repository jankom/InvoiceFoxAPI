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
		$res = $this->api->call('invoice-sent', 'insert', $header);
		if ($res->isErr()) {
			echo 'error' . $res->getErr();
		} else {
			foreach ($body as $bl) {
				$res2 = $this->api->call('invoice-sent-b', 'insert', $bl);
				if ($res->isErr()) {
					echo 'error' . $res->getErr();
				} 
			}
		}
		return $res->getData();
	}

	function downloadPDF($id) {
		$res = $apipdf->load($id, $id.".pdf");
		if ($res->isErr()) {
			echo 'error' . $res->getErr();
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
