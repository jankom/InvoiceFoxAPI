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

if (isset($_POST['call'])) {
	
	$api = new InvfoxAPI('vr64g6891lgatnf83dmxticsc10ashr2f4s9ye7w');
	
	$resp = $api->assurePartner("name=asdasd&street=asdasds&postal=12312&city=asdasd&vatid=&phone=&website=&email=&notes=&vatbound=0&custaddr=&payment_period=14&street2=asdsad");
	
	echo "<br/><div class='type'>response</div><pre class='resp'>"; print_r($resp->res); echo "</pre>";

}
?>
<form method="post">
<input type="submit" name="call" value="Call" />
</body>
</html>
name=asdasd&street=asdasds&postal=12312&city=asdasd&vatid=&phone=&website=&email=&notes=&vatbound=0&custaddr=&payment_period=14&street2=asdsad
