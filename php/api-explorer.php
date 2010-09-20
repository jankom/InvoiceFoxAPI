<html>
<body>
<style>
body {
	font-size: 12px;
	font-family: Trebuchet MS, Arial, Tahoma;
	padding: 0px;
	margin: 0px;
}
div.type {
	float: right;
	color: gray;
	font-weight: bold;
	padding-right: 10px;
}
h1 {
	margin: 0px;
	padding 20px;
	background-color: #444444;
	color: white;
}
pre.req {
	padding: 20px;
	background-color: #f1f1f1;
}
pre.resp {
	padding: 20px;
	background-color: #f1f1f1;
}
form {
	padding: 20px;
	background-color: #f1f1ff;
}
</style>
<h1>InvoiceFox API explorer</h1>
<?php
require_once "pulp/Format.php";
require_once "strpcapi.php";
require_once "invfoxapi.php";


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
</body>
</html>
name=asdasd&street=asdasds&postal=12312&city=asdasd&vatid=&phone=&website=&email=&notes=&vatbound=0&custaddr=&payment_period=14&street2=asdsad
