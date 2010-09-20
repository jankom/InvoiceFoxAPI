<?php

	require_once APP_ROOT.'bin/pulp/DBs.php';
	require_once APP_ROOT.'bin/pulp/Validator.php';
	
	//Backend is universal database - logic backend and trys to hold all that it needs
	//Extend it to your class for use
	
	abstract class Stone {
		
		private $msgs;
		protected $validationNotes;
		protected $db;
		protected $validator;
	
		public function __construct() {
			
			$this->db = DBS::inst();
			$this->validator = new Validator();
			$this->msgs = array();
			$this->validationNotes = array();
			
		}
	
		public function _get($p) {
			
			return $this->db->selectRows($p);
			
		}
	
		public function _addMsg($msg, $class='') {
			
			$this->msgs[] = array($msg, $class);
			
		}
	
		public function _log($type, $msg) {
			
			//type: error, event, warning
			
			$pairs = array ('date_time'=>'#noQuote#NOW()',
							'type'	   =>$type,
							'msg'	   =>$msg);
							
			$p = array ('into' =>'eu_log',
						'pairs'=>$pairs);
						
			$this->db->insertRow($p);
			
		}
	
		public function _show_msgs($class='') {
			
			$out = '';
			if (count($this->msgs))
			{
				$out = "<ul class='msgs'>\n";
				foreach($this->msgs as $msg)
				{
					if ($msg[1] == $class || $class == '')
					{
						$out .= "<li class='$msg[1]'>$msg[0]</li>\n";
					}
				}
				$out .= "</ul>\n";
			}
			return $out;
			
		}
		
		protected function restricted($roles) {
			
			//Auth_f::inst()->dieUnlessRoles($roles);
			
		}
	
	}


?>