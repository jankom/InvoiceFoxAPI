<?php
/*i
*/

class XmlFrack
{

	static function getOpenTagEnd($data, $tag, $start)
	{
		$pos = strpos($data, '<'.$tag, $start);
		return $pos ? strpos($data, '>', $pos) + 1 : false;
	}
	
	static function getCloseTagStart($data, $tag, $start){
		return strpos($data, '</'.$tag, $start);
	}
	
	static function getTagParams($data, $tag, $start){
		$pos = strpos($data, '<'.$tag, $start) + 1 + strlen($tag);
		return $pos !== false ? substr($data, $pos, strpos($data, '>', $pos) - $pos) : '';
	}

	static function getParam($param, $params)
	{
		$pos = strpos($params, $param."=");
		if ($pos !== false)
		{
			$posQ = strpos($params, "=", $pos) + 1;
			$q = substr($params, $posQ, 1);
			$posQe = strpos($params, $q, $posQ + 1);
			return substr($params, $posQ + 1, $posQe - $posQ - 1);
		}
		else
		{
			return "";
		}
	}

}

?>