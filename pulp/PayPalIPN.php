<?php /* 

two tables 

TRANSACTIONS 



DEVIATIONS_LOG
id,
date_time
IP
id_transactions
more_data


... 

*/

class PayPalIPN
{

	var $email;

	function PayPalIPN($email)
	{
		$this->email = $email;
	}	

	function make_paypal_button_form($d, $od)
	{
		//sandbox.
		return "
				<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
				<input type='hidden' name='cmd' value='_xclick'>
				<input type='hidden' name='business' value='{$this->email}'>
				<input type='hidden' name='item_name' value='{$d['item_name']}'>
				<input type='hidden' name='item_number' value='{$d['item_number']}'>
				<input type='hidden' name='amount' value='{$d['amount']}'>
				<input type='hidden' name='no_shipping' value='0'>
				<input type='hidden' name='no_note' value='1'>
				<input type='hidden' name='currency_code' value='USD'>
				<input type='hidden' name='lc' value='US'>
				<input type='hidden' name='bn' value='PP-BuyNowBF'>
				<input type='hidden' name='return' value='{$od['return']}'> 
				<input type='hidden' name='cancel_return' value='{$od['cancel_return']}'>
				<input type='hidden' name='custom' value='{$d['custom']}'>   
				<input type='hidden' name='rm' value='2'><!--Auto return must be off if rm=2     -->
				<input type='image' src='https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif' border='0' name='submit' alt='Make payments with PayPal - it's fast, free and secure!'>
				<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'>
				</form> ";
	}

	function act_on_notification($post)
	{
/*		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		// post back to PayPal system to validate
		$http_post = Http::makePostString(array_merge($post, array('cmd' => '_notify-validate')));
		//sandbox.
		//// OLD WAY --- $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		// NEW WAY ->
		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
		// assign posted variables to local variables
		/*$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];* /

		if (!$fp) {
			// HTTP ERROR
		} else {
			fputs ($fp, $header . $http_post);
			
*/

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];

if (!$fp) {
// HTTP ERROR
} else {
fputs ($fp, $header . $req);


			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) {
					logPaypalIPN("VERIFIED");
					$this->cb_notification_valid($post);
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
				}
				else if (strcmp ($res, "INVALID") == 0) {
					logPaypalIPN("INVALID");
					$this->cb_notification_invalid($post);
					// log for manual investigation
				}
				else {
					logPaypalIPN("EXCEPTION - PAYPAL RETURNED: $res");
				}
			}
			fclose ($fp);
		}
	}
	
	function cb_notification_valid($post) { echo 'valid'; print_r($post); }
	
	function cb_notification_invalid($post) { echo 'invalid'; print_r($post); }
}




class PayPalIPNPlus extends PayPalIPN
{

	var $od_return = 'http://www.audiobank.fm/payment_made.php';
	var $od_cancel_return = 'http://www.audiobank.fm/payment_canceled.php';
	var $db;
	var $tbl_transact;
	var $tbl_except;

	function PayPalIPNPlus($email, $db, $tbl_prefix='')
	{
		$this->PayPalIPN($email);
		$this->db = $db;
		$this->tbl_transact = $tbl_prefix . 'paypal_transact';
		$this->tbl_except = $tbl_prefix . 'paypal_except';
	}
	
	function show_button_and_store($d, $item_name)
	{
		$d['custom'] = md5($d['name'] . $d['surname'] . $d['country'] . $d['id_sales_h']);
	
		$this->db->insertRow(
			array(
				'into' => $this->tbl_transact,
				'pairs' => array(
					'email' => $d['email'],
					'custom' => $d['custom'],
					'price' => $d['price_sum'],
					'payed' => false,
					'id_sales_h' => intval($d['id_sales_h']),
					'served_cnt' => 0,
					'success_page_seen' => false,
		),));
		
		$pd = array(
			'item_name' => $item_name,
			'item_number' => $d['id_sales_h'],
			'amount' => $d['price_sum'],
			'custom' => $d['custom'],
		);
		$pod = array(
			'return' => $this->od_return.'?sc='.$d['custom'],
			'cancel_return' => $this->od_cancel_return.'?sc='.$d['custom'],
		);
		
		echo $this->make_paypal_button_form($pd, $pod);
	}

	function log_deviation($id_trans, $data, $more_data='')
	{
		$this->db->insertRow(
			array(
				'into' => $this->tbl_except,
				'pairs' => array(
					'date_time' => "#noQuote#NOW()",
					'user_ip' => $_SERVER['REMOTE_ADDR'],
					'id_transact' => $id_trans,
					'data' => $data,
					'more_data' => $more_data,
		),));
	}
	
	function cb_notification_valid($d) 
	{ 
		//echo 'valid'; print_r($post); 
		$trans = $this->get_transaction_by_custom($d['custom']);
		
		if ($trans)
		{
			if (!$trans['payed']) //should we check date_time also?
			{
				//CHECK all data of transaction gotten from paypal
				//TODO how to inteligently save the Pending status (for the buyer) ?? 
				//	store it , get reason , explain user , send links by email when Completed or explanation when Denied
				if (	//$d['payment_status'] == 'Completed' &&
						$d['business'] == $this->email &&
						$d['mc_gross'] == $trans['price'] &&  //check they payed what they should have
						$d['mc_currency'] == "USD"
				){
						//save the order as payed (in transactions and in sales_h (but not delivered.. so when user's page shows it looks if it is 
						//payed and not delivered and shows him the links) )
						$this->mark_transaction_payed($trans['id']);
						
						//require_once '../_cart_f.php';
						$cart = new Cart_f($this->db);
						$cart->email_afterPaymentMade($trans['id_sales_h']);
						echo 'payed sent email and ready';
						
				}
				else
				{
					echo 'data comparison failed'; print_r($d); 
					$this->log_deviation($trans['id'], 'already payed', serialize($d));
				}
			}
			else
			{
				echo 'already payed'; print_r($d); 
				$this->log_deviation($trans['id'], 'already payed');
			}
		}
		else
		{
			echo 'trans does not exist'; print_r($d); 
			$this->log_deviation(0, "trans does not exist", $d['custom']);
		}
	}
	
	function cb_notification_invalid($d) 
	{ 
		echo 'paypal returned INVALID !'; print_r($d); 
		$this->log_deviation('0', "paypal not valid", serialize($d));
	}

	function get_transaction_by_custom($custom)
	{
		return $this->db->selectRow(array(
				'from' => $this->tbl_transact,
				'where' => "custom = '$custom'",
		));
	}

	function get_sales_id_by_custom($custom)
	{
		return $this->db->selectField(array(
				'what' => 'id_sales_h',
				'from' => $this->tbl_transact,
				'where' => "custom = '$custom'",
		));
	}
	
	function mark_transaction_payed($id)
	{
		return $this->db->updateRows(array(
				'what' => $this->tbl_transact,
				'pairs' =>  array('payed' => true, 'date_time' => '#noQuote#NOW()'),
				'where' => "id = $id",
		));		
	}

	function has_payment_been_made($custom)
	{
		$t = $this->get_transaction_by_custom($custom);
		if (!$t) return null;
		return array($t['payed'], 'ok');;
	}
	
	function show_payment_waiting_reload_code()
	{
		return '<meta http-equiv="refresh" content="5">';
	}
	
}

?>