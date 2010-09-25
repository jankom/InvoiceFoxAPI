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
	
	echo "<br/><div class='type'>response</div><pre class='resp'>"; print_r($resp->res); echo "</pre>";
	
		$rd = json_decode($r, true);
		
		if ($rd[0] == 'ok') {
			
			$clientId = $rd[1][0]['id'];

			$r = $api->createInvoice(
							array(
							
							),
							array(
								array(

								)
							)
			);

			$rd = json_decode($r, true);

			if ($rd[0] == 'ok') {

				$api->downloadPDF($rd[1][0]['id']);

			}

		}

	}

}
?>
<form method="post"><input type="submit" name="call" value="Call" /></form>
<?php include "style/footer.php"; ?>
