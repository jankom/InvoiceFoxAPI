<?php

	class Crumbs {
		
		static private $crumbs = array();
		static private $end;
		
		static public function add($name, $link) {
			
			self::$crumbs[] = array('name'=>ucfirst($name), 'link'=>$link);
			
		}
		
		static public function addAtEnd($name, $link) {
			
			self::$end = array('name'=>ucfirst($name), 'link'=>$link);
			
		}
		
		static public function show() {
			
			if (isset(self::$end)) {
				self::$crumbs[] = self::$end;
			}
			$return = '<div id="crumbs"><ul>';
			$return .= '<li><a href="' . URL::build('osnovna.php') . '"><span>Osnovna stran</span></a></li>';
			foreach (self::$crumbs as $crumb) {
				$return .= "<li><a href='{$crumb['link']}'><span>{$crumb['name']}</span></a></li>";
			}
			$return .= '</ul></div>';
			
			return $return;
		}
		
	}