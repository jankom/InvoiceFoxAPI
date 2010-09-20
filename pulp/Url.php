<?php

class Url {
	
	public static function build($base, $variables=null, $hash=null, $defaults = true) {
		
		$base = !$base ? basename($_SERVER['PHP_SELF']) : $base;
		$url = $base . '?';
		
		if (is_array ($variables)) 
		{
			foreach ($variables as $variable => $value)  {
				if ($defaults) {
					$url .= '&'.$variable.'='.$value;
				} else {
					$url .= $variable . '=' . $value . '&';
				}
			}
			if (!$defaults) {
				$url = substr ($url, 0, strlen ($url) - 1);
			}
		}
		if (is_string ($hash)) {
			$url .= '#'.$hash;
		}
		return htmlspecialchars ($url);
	}

}