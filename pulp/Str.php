<?php
/*i
*/

class Str{

	function startsWith($string, $start){
		return substr($string, 0, strlen($start)) == $start;
	}
	
	function ucwords($str) 
	{
		$pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';
		return preg_replace_callback($pattern, 'Str__ucwords_cb',$str);
	}	
	
	public function smartLeft($content, $offset=120) 
	//author) written by rok changed by janko
	//returns left part of the string at first word from given offset or at |
	{
		$offsetOrig = $offset;
		$content = strip_tags($content);
		if (strpos($content, '|') !== false) {
			return substr($content, 0, strpos($content, '|')).'...';
		} else {
			$len = strlen($content);
			if ($offset < $len) {
				while (substr($content, $offset, 1) != ' ' && $offset > 0) {
					$offset--;
				}
				//echo $content . '+' . $offset . '--';
				if ($offset > 0) 
				{
					return substr($content, 0, $offset).'...';
				}
				else 
				{
					return substr($content, 0, $offsetOrig).'...';
				}
			} else {
				return $content;
			}
		}
	}
	
}

function Str__ucwords_cb($matches) 
{
	$leadingws = $matches[2];
	$ucfirst = strtoupper($matches[3]);
	$ucword = $ucfirst . substr(ltrim($matches[0]),1);
	return $leadingws . $ucword;
}	

//echo Str::ucwords('Žižek Čokec');
?>