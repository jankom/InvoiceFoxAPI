<?php
class Urls {

 	function removeSubDomain($url) 
	{
 	  $pos = strrpos($url, '.');
		$pos2 = strrpos($url, '.', -($pos+1));
		if ($pos2 === false)
		{
			return $url;
		}
		else
		{
		  return substr($url, $pos2+1);
		}
	}
}	 

echo Urls::removeSubDomain('www.itmmetelko.com');

?>