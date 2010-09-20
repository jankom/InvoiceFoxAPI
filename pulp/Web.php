<?php

class Web
{

	function GETd($map)  //act could also be log and pass alter - only dies now
	{
		return Web::dictTest($_GET, $map, 'GET');
	}
	
	function POSTd($map)  //act could also be log and pass alter - only dies now
	{
		return Web::dictTest($_POST, $map, 'POST');
	}


	function dictTest($dict, $map, $name)  //act could also be log and pass alter - only dies now
	{
	
		$ret = array();
		
		foreach ($map as $key => $dirsStr)
		{
			$dirs = explode('|', $dirsStr);
			if (isset($dict[$key]))
			{
				$val = $dict[$key];
				foreach($dirs as $d)
				{
					$val = Web::testDirective($d, $val, $key);
				}
				$ret[] = $val;
			}
			else
			{
				if ($dirs[0] == '?')
				{
					$type = $dirs[1];
					$directive = Web::getDirective($dirs, 'default');
					$ret[] = Web::getDirectiveValue($directive, $type);
				}
				else
				{
					Web::error("$name $key is missing.");
				}
			}
		}
		return $ret;
	}
	
	function getDirective($directives, $name)
	{
		foreach($directives as $dir)
		{
			if (strpos($dir, '='))
			{
				$dirArr = explode('=', $dir);
				if ($dirArr[0] == $name)
				{
					return $dir;
				}
			}
		}
		return '';
	}

	function getDirectiveValue($directive, $type)
	{
		$dirArr = explode('=', $directive);
		if ($type == 'int')
		{
			if ($dirArr[1] == '') return 0;
			return intval($dirArr[1]);
		}
		else if ($type == 'float')
		{
			if ($dirArr[1] == '') return 0;
			return floatval($dirArr[1]);		
		}
		else if ($type == 'string')
		{
			return strval($dirArr[1]);
		}
	}

	function testDirective($directive, $value, $key)
	{
		$dir = explode('=', $directive);
		if (count($dir) == 2)
		{
			$dirName = $dir[0];
			$dirVal = $dir[1];
		}
		else
		{
			$dirName = $dir[0];
			$dirVal = "";
		}
		
		switch($dirName)
		{
			case 'int':
				if (is_numeric($value))
				{
					return intval($value);
				}
				Web::error("$key $value is not int.");
			case 'float':
				if (is_numeric($value))
				{
					return floatval($value);
				}
				Web::error("$key $value is not float.");
			case 'string':
				if (is_string($value))
				{
					return $value;
				}
				Web::error("$key ´$value´ is not string.");
			case 'larger':
				if ($value > $dirVal)
				{
					return $value;
				}
				Web::error("$key ´$value´ is not larger than $dirVal.");
			case 'smaller':
				if ($value < $dirVal)
				{
					return $value;
				}
				Web::error("$key ´$value´ is not smaller than $dirVal.");
			case 'positive':
				if ($value > 0)
				{
					return $value;
				}
				Web::error("$key ´$value´ is not positive.");
			case 'notzero':
				if ($value != 0)
				{
					return $value;
				}
				Web::error("$key ´$value´ is not notzero.");
			case 'default':
				return $value;
			default:
				Web::error("Directive $directive not supported (pulp/Web.php).");
		}
	}

	function areSet($dict, $keys)
	{
		foreach($keys as $key)
		{
			if (!isset($dict[$key])) return false;
		}
		return true;
	}

	function areNotSet($dict, $keys)
	{
		foreach($keys as $key)
		{
			if (isset($dict[$key])) return false;
		}
		return true;
	}

	//this was the old way... not much benefits just default values :/
	function GET($key, $default='') 
	{
		return isset($_GET[$key]) ? 
			$_GET[$key] : 
			$default;
	}

	function GETi($key, $default='0') 
	{
		return Web::GET($key, $default);
	}

	function POST($key, $default='') 
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	function POSTi($key, $default='0') 
	{
		return Web::POST($key, $default);
	}
	
	// util
	
	function error($text)
	{
		//TODO: povezat z ostalim v sistem za logiranje erorjev
		die("<span style='background-color: #ffaaaa;'>Error: $text</div>");
	}
}

?>