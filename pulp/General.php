<?php

/*

GENERAL FUNCTIONS USED HERE AND THERE

*/

class General
{

	function null_to_self( &$target ) {
	/** This is always needed for taggets. If is null, set it to self. Target is passed by reference. **/
		if (is_null($target)) { $target = $_SERVER['PHP_SELF']; }	//set target file if needed
	}

	function null_to( &$target , $alt_value) {
	/** This is always needed for taggets. If is null, set it to self. Target is passed by reference. **/
		if (is_null($target)) { $target = $alt_value; }	//set target file if needed
		return $target;
	}

	function srand_once($seed = ''){
	/** Srand can be called only once. So this func simpy fixes it and makes a good seed. **/
		static $wascalled = FALSE;
		if (!$wascalled){
			$seed = $seed === '' ? (double) microtime() * 1000000 : $seed;
			srand($seed);
			$wascalled = TRUE;
		}
	} 

	function generate_vid($num=40) {
	/** Generate some validation ID **/
		$vid = '';
		srand_once();
		for ($i=0; $i<$num; $i++)
			$vid .= rand(0,9);
		return $vid;
	}

	function bool2int( $value ) {
	/** True to 1 and False to 0, used at xmlrpc methods. if int, like -1 return int **/
		if (is_int($value)) return $value;
		if ($value) return 1;
		else return 0;
	}
}

?>