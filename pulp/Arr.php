<?php

require_once 'Http.php';

class Arr {
	
	static function setDefaults($a, $defs) 
	{
		foreach($defs as $k => $d){
			if (!isset($a[$k]))
			{
				$a[$k] = $d;
			}
		}
		return $a;
	}
	
	static function toIndexed($a, $ks) 
	{
		$r = array();
		foreach($ks as $k)
		{
			$r[] = $a[$k];
		}
		return $r;
	}
	
	static function reducek($array, $callback, $initial=null)
	//reduce function with array key  and value
	{
		$acc = $initial;
		foreach($array as $k => $v)
			$acc = call_user_func_array($callback, array($acc, $k, $v));
		return $acc;
	}
}