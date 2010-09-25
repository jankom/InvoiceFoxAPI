<?php
include "style/header.php";
require_once "lib/strpcapi.php";
require_once "lib/invfoxapi.php";


$apidef = array(
	'invoice-sent' => array('select-all'),
	'invoice-recv' => array('select-all'),
	'partner' => array('select-all'),
	'preinvoice' => array('select-all')
);

$RESOURCE = isset($_POST['resource']) ? $_POST['resource'] : '';
$METHOD = isset($_POST['method']) ? $_POST['method'] : '';
$ARGS = isset($_POST['args']) ? $_POST['args'] : '';


if (isset($_POST['call'])) {
	$strpc = new StrpcAPI('vr64g6891lgatnf83dmxticsc10ashr2f4s9ye7w');
	$response = $strpc->call($RESOURCE, $METHOD, $ARGS);
	echo "<br/><div class='type'>response</div><pre class='resp'>"; print_r($response->res); echo "</pre>";
}
?>
<form method="post">
resource:<br/><input name="resource" value="<?php echo $RESOURCE ?>" />
<small><?php foreach ( $apidef as $res => $meths ) { echo $res . " &nbsp; "; } ?></small>
<br/>
method: <br/><input name="method"  value="<?php echo $METHOD ?>"/><br/>
<br/>
arguments: <br/><textarea name="args" cols="100" rows="5"></textarea><br/>
<input type="submit" name="call" value="Call" />
name=asdasd&street=asdasds&postal=12312&city=asdasd&vatid=&phone=&website=&email=&notes=&vatbound=0&custaddr=&payment_period=14&street2=asdsad
<?php include "style/footer.php"; ?>
