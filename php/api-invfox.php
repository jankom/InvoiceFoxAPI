<?php
include "style/header.php";
require_once "lib/strpcapi.php";
require_once "lib/invfoxapi.php";

if (isset($_POST['call'])) {
	
	$api = new InvfoxAPI('vr64g6891lgatnf83dmxticsc10ashr2f4s9ye7w');
	
	$r = $api->assurePartner(array(
								'name' => 'asdasd',
								'street' => 'asdasds',
								'postal' => '12312',
								'city' => 'asdasd',
								'vatid' => '',
								'phone' => '',
								'website' => '',
								'email' => '',
								'notes' => '',
								'vatbound' => 0,
								'custaddr' => '',
								'payment_period' => 14,
								'street2' => 'asdsad'
	));
	
	echo "<br/><div class='type'>response</div><pre class='resp'>" . print_r($r->getData(), true) . "</pre>";
	
	if ($r->isOk()) {
		
		$clientIdA = $r->getData();
		$clientId = $clientIdA[0]['id'];

		$r = $api->createInvoice(
						array(
							'title' => 'INV00001',
							'date_sent' => '10/22/2010',
							'date_to_pay' => '11/04/2010',
							'id_partner' => $clientId,
							'vat_level' => 10
						),
						array(
							array(
								'title' => 'custom programming',
								'qty' => 20,
								'mu' => 'hour',
								'price' => 80
							),
							array(
								'title' => 'support on project',
								'qty' => 6,
								'mu' => 'hour',
								'price' => 60
							)
						)
		);

		if ($r->isOk()) {
			$invIdA = $r->getData();
			$invId = $invIdA[0]['id'];
			$api->downloadPDF($invId);
			
			echo "<p><a href='invoices/$invId.pdf'>Download PDF</a></p>";
		}

	}

}
?>
<form method="post"><input type="submit" name="call" value="Call" /></form>
<?php include "style/footer.php"; ?>
