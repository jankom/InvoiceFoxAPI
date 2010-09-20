<?php

require_once "Str.php";

//i) Serializes variables into html -- how stupid is that?

//i) strong - string , em - numbers, u - null and errors
//i) <dl><dt><b>age</b></dt><dd><i>23</i></dd></dl> map

class Htmlize
{

	static function encode($value)
	{
		if (is_string($value)) 
		{ 
			if (Str::startsWith($value, "#err:"))
				return "<u>$value</u>";
			else
				return "<b>$value</b>";
		}
		else if(is_numeric($value)) 
		{
			return "<i>$value</i>";
		}
		else if(is_null($value)) 
		{
			return '<u>null</u>';  //null and "system messages get underlined (errors)
		}
		else if(is_bool($value)) 
		{ 
			return "<i>".intval($value)."</i>"; 
		}
		else if (is_array($value) and (isset($value[0]) || count($value) == 0))
		{
			$o = '<ul>';
			foreach($value as $val)
			{
				$val2 = Htmlize::encode($val);
				$o .= "<li>$val2</li>";
			}
			return $o."</ul>";
		}
		else if (is_array($value))
		{
			$o = '<dl>';
			foreach($value as $key => $val)
			{
				$key2 = Htmlize::encode($key);
				$val2 = Htmlize::encode($val);
				$o .= "<dt>$key2</dt><dd>$val2</dd>";
			}
			return $o."</dl>";
		}
	}

	function print_n_decode($str)
	{
		echo $str . '<br/>';
		return Htmlize::decode($str);
	}

	function decode(&$str, $arr=array())
	{
		$str = trim($str);
	
		//DISCRETE VALUES
		if (Str::startsWith($str, "<i>"))
		//numbers
		{
			$val = null;
			$strval = substr($str, 3, strpos($str, '</i>') - 3);
			if (strpos($strval, '.') === false)
				$val = intval($strval);
			else
				$val = floatval($strval);
			return $val;
		}
		else if (Str::startsWith($str, "<b>"))
		//strings
		{
			$val = substr($str, 3, strpos($str, '</b>') - 3);
			return $val;
		}
		//COMPOSITE VALUE uses recursion to parse itself
		else if (Str::startsWith($str, '<dl>'))
		{
			$arr = array();
			do 
			{
				$str = substr($str, strpos($str, '<dt>'));
				$key = Htmlize::decode($str);
				$str = substr($str, strpos($str, '<dd>'));
				$val = Htmlize::decode($str);
				$arr[$key] = $val;
				$str = trim(substr($str, strpos($str, '</dd>') + 5));
			} while (Str::startsWith($str, '<dt>'));
			return $arr;
		}
		else if (Str::startsWith($str, '<ul>'))
		{
			$arr = array();
			do 
			{
				
				$posLi = strpos($str, '<li>');
				$posUlEnd = strpos($str, '</ul>');
				if ($posUlEnd < $posLi || $posLi === false) return $arr;
				
				$str = substr($str, $posLi);
				$val = Htmlize::decode($str);
				$arr[] = $val;
				$str = trim(substr($str, strpos($str, '</li>') + 5));
			} while (Str::startsWith($str, '<li>'));
			return $arr;
		}
		else if (Str::startsWith($str, '<dt>'))
		{
			$str = substr($str, 4);
			return Htmlize::decode($str);
		}
		else if (Str::startsWith($str, '<dd>'))
		{
			$str = substr($str, 4);
			return Htmlize::decode($str);
		}
		else if (Str::startsWith($str, '<li>'))
		{
			$str = substr($str, 4);
			return Htmlize::decode($str);
		}
	}	
}

?>