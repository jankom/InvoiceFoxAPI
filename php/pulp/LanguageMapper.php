<?php

	class LanguageMapper Implements ArrayAccess {
		
		static private $instance;
		private $language;
		static private $array;
		
		static public function inst ($language = '') {

			if (!self::$instance) {
				self::$instance = new self($language);
			}
			if ($language!='' && self::$instance->language != $language) {
				return new self($language);
			}
			return self::$instance;
		}
		
		private function __construct($language) {
			
			$this->language = $language;
		
		}
		
		public function getLanguage() {
			
			return $this->language;
				
		}
		
		public function __get ($key) {
			
			if (!isset($this->array)) {
				$this->loadArray();
			}
			$this->offsetGet($key); 
			
		}
		
		private function loadArray () {
			
			$file = APP_ROOT.'bin/lang/lang.php';
			require_once $file;
			self::$array = $values;
			
		}
		
		function offsetExists($offset){

			return isset(self::$array[$this->language][$offset]);
			
		}
	 
		function offsetGet($offset){
		
			if (!isset(self::$array)) {
				$this->loadArray();
			}
			if ($this->offsetExists($offset)) {
				return self::$array[$this->language][$offset];
			} else {
				return $offset;
			}
		}
	 
		function offsetSet($offset, $value){
			//
		}
	 
		function offsetUnset($offset){
			//
		}
		
	}