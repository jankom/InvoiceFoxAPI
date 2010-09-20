<?php

	require_once APP_ROOT.'bin/pulp/Format.php';
	require_once APP_ROOT.'bin/pulp/LanguageMapper.php';
	
	class Validator {
	
		private $rules;
		private $notesTemplates;
		private $valid;
		
		public function __construct() {
			
			$this->valid = false;
			$this->rules = array();
	
		}
	
		public function addRule($id, $rules, $name='') {
			
			$this->rules[] = array($id, $rules, $name);
			
		}
	
		public function validate($data) {
			
			if (!isset($this->notesTemplates)) {
				$this->notesTemplates = LanguageMapper::inst('english');
			}
			$notes = array();
			foreach($this->rules as $ruleLine)
			{
				if (isset($data[$ruleLine[0]])) 
				{
					foreach($ruleLine[1] as $rule)
					{
						$ruleName = isset($ruleLine[2]) ? $ruleLine[2] : '';
						$note = $this->validateOne($data[$ruleLine[0]], $rule, $ruleName);
						if ($note) {
							$notes[$ruleLine[0]] = $note;
							break;
						}
					}
				} 
				else {
					trigger_error("Validation rule {$ruleLine[0]} not found in data.");
				}
			}
			if (!count($notes)) {
				$this->valid = true;
			}
			return $notes;
			
		}
		
		public function validateOne($data, $rule, $name='') {
			
			if ($name) {
				$name = "'$name'";
			}
			switch ($rule) {
				case 'is_required':
					if (!$this->is_required($data))
						return str_replace('%%', $name, $this->notesTemplates['Validation is_required']);
					break;
				case 'is_email':
					if (!$this->is_email($data))
						return str_replace('%%', $name, $this->notesTemplates['Validation is_email']);
					break;
				case 'is_number':
					if (!$this->is_number($data))
						return str_replace('%%', $name, $this->notesTemplates['Validation is_number']);
					break;
				case 'is_oneWord':
					if (!$this->is_oneWord($data))
						return str_replace('%%', $name, $this->notesTemplates['Validation is_oneWord']);
					break;
				case 'is_pureText':
					if (!$this->is_pureText($data))
						return str_replace('%%', $name, $this->notesTemplates['Validation is_pureText']);
					break;
				default:
					trigger_error ("Rule '$rule' for validation is not supported.", E_USER_ERROR); 
			}		
			return Null;
		}
		
		public function isValid() {
			
			return $this->valid;
			
		}
		
		public function is_empty($text) {
			
			if ( $text == '' OR is_null($text)) {
				return true;
			} else {
				return false;
			}
		}
	
		public function is_required($text) {
			
			return !$this->is_empty($text);
			
		}
		
		public function is_email($email) {
			
			if (eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$", $email)) {
				return true;
			}else {
				return false;
			}
		}
	
		public function is_pureText($str) {
			
			if (eregi("^[_0-9a-z]+$", $str)) {
				return true;
			}else {
				return false;
			}
		}
		
		public function is_number( $text ) {
			
			if (is_numeric($text)) {
				return true;
			}else{
				return false;
			}
		}
	
		public function is_oneWord($text) {
			
			if (eregi("( |\n|\t|\r)+", trim($text))) {
				return false;
			}else{
				return true;
			}
		}
	
		public function is_dateNull($date) {
			
			if (is_null($date) or $date[0] == '0' or $date == '') {
				return true;
			}else{
				return false;
			}
		}
			
		public function security_check($text , $mode='path') {
			
			switch($mode) {
				case 'path':
					return ! eregi('.*[\\|\.|\..|\/].*', $text);
				default:
					trigger_error ("Mode must be 'path' ....", E_USER_ERROR);
			}
		}
		
		public function mustBeInt ($value) {

			if (!is_numeric($value)) {
				trigger_error ('Bad parameter type.', E_USER_ERROR);
			}
			
		}
		
		public function mustBeString ($value) {
			
			if (!is_string($value)) {
				trigger_error ('Bad parameter type.', E_USER_ERROR);
			}
			
		}
	}