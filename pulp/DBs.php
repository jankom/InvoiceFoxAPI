<?php 

	define('DBS_SHOW_SELECTS', false);
	define('DBS_SHOW_EXPLAIN', false);
	define('DBS_GLOBAL_TIMING', false);
	
	require_once 'DB.php';
	require_once 'Trace.php';
	require_once APP_ROOT.'bin/pulp/Format.php';
	
	class DBs {
	
		static private $instance;
		private $dbc;
		public $debugMode = false;
		
		public static function inst($dsn = null, $options = array ())
		{
			if (!self::$instance)
			{
				self::$instance = new DBs();
				self::$instance->connectToDB($dsn, $options);
			}
			return self::$instance;
		}
		
		private function connectToDB($dsn, $options=array())
		{
			$this->dbc = DB::connect($dsn, $options);
			if (DB::isError($this->dbc))
			{ 
				die($this->dbc->getMessage());
			}
			return $this->dbc;
		}
	
		public function setUtf8()
		{
			$this->executeSQL("SET WAIT_TIMEOUT = 300;");
			$this->executeSQL("SET NAMES 'utf8';");
		}
	
	 	public function selectRows($p, /*$from, $what='*', $where='', $group='', $order='', $having='', $limit = '',*/ $fetchMode=DB_FETCHMODE_ASSOC, $assocById=false) {

	 		//i Gets items from one group.
	 		//$sql = "SELECT {$p['what']}";
	 		$p['what'] = (isset($p['what']) && !empty($p['what'])) ? $p['what'] : '*';
	 		$sql = 'SELECT ' . $p['what'];
			if (isset($p['from'])  && !empty($p['from'])) { $sql .= " FROM {$p['from']}";	}
			if (isset($p['where']) && !empty($p['where'])) { $sql .= " WHERE {$p['where']}";	}
			if (isset($p['group']) && !empty($p['group'])) {$sql .= " GROUP BY {$p['group']}";	}
			if (isset($p['order']) && !empty($p['order'])) {$sql .= " ORDER BY {$p['order']}";	}
			if (isset($p['having']) && !empty($p['having'])) {$sql .= " HAVING {$p['having']}";	}
			if (isset($p['limit']) && !empty($p['limit'])) {$sql .= " LIMIT {$p['limit']}";	}
			$sql .= ';';
			
			if (DBS_SHOW_SELECTS || $this->debugMode)
			{
				echo '<div style="background-color: lightgreen; font-size: 12px;">';
				echo $sql;
				echo '</div>';
			}
			if (DBS_SHOW_EXPLAIN || DBS_GLOBAL_TIMING) $ts = microtime();
			if ($assocById)
			{
				$data = $this->dbc->getAssoc($sql, false, array(), $fetchMode);
			}
			else
			{
				$data = $this->dbc->getAll($sql, array(), $fetchMode);
			}
			if (DBS_SHOW_EXPLAIN or DBS_GLOBAL_TIMING) $ts = microtime() - $ts;
	  	$this->handleIfError($data);
			if (DBS_SHOW_EXPLAIN )
			{
				echo '<div style="background-color: lightblue; font-size: 12px;">';
				print_r($this->dbc->getAll('EXPLAIN '.$sql, array(), DB_FETCHMODE_ASSOC));
				echo "<i>time: $ts s</i>";
				echo '</div>';
			}
			if (DBS_GLOBAL_TIMING) $GLOBALS['timing']['sql-selects'] += $ts;
	  		return $data;
	 	}
	
	 	public function selectRow($p, /*$from, $what='*', $where='', $group='', $order='', $having='', */$fetchMode=DB_FETCHMODE_ASSOC, $assocById=false) 
		{
			//print_r($p['where']);
			$p['limit'] = 1;
			$data = $this->selectRows($p, $fetchMode, $assocById);
			//print_r($data);
			if (count($data) > 0) return $data[0];
			else return Null;
	 	}
	
	 	public function selectField($p) 
		{
			$p['limit'] = 1;
			$data = $this->selectRows($p, DB_FETCHMODE_ORDERED);
			//print_r($data);
			if (isset($data[0][0])) return $data[0][0];
			else return Null;
	 	}
		
		public function insertRow($p, $returnLastId=false) 
		{
			//i Adds one item.
			$strValues = $this->dict2pairs($p['pairs']);
			$sql = "INSERT INTO {$p['into']} SET $strValues;";
			$result = $this->dbc->query($sql);
			$this->handleIfError($result);
			if ($returnLastId) return $this->selectLastInsertId();
			return true;
		}
		
		public function updateRows($p) {
			//i 
			
			//opomba: janko - what bi moral bit poimenovan kako druga�e what imamo 
			// drugje za imena stolpcev
			$strValues = $this->dict2pairs($p['pairs']);
			$sql = "UPDATE {$p['what']} SET $strValues WHERE {$p['where']};";
	  		$result = $this->dbc->query($sql);
			$this->handleIfError($result);
	  		return $this->dbc->affectedRows();
		}
			
		public function deleteRows($p) {
			//i Delets one item.
			$sql = "DELETE FROM {$p['from']} WHERE {$p['where']};";
			$result = $this->dbc->query($sql);
			$this->handleIfError($result);
			return $this->dbc->affectedRows();
		}
		
		public function executeSQL($sql) {
			//i Adds one item.
			if (DBS_SHOW_SELECTS || $this->debugMode)
			{
				echo '<div style="background-color: lightgreen; font-size: 12px;">';
				echo $sql;
				echo '</div>';
			}
			if (DBS_SHOW_EXPLAIN )
			{
				echo '<div style="background-color: lightblue; font-size: 12px;">';
				print_r($this->dbc->getAll('EXPLAIN '.$sql, array(), DB_FETCHMODE_ASSOC));
				echo '</div>';
			}
	  		$result = $this->dbc->query($sql);
	  		$this->handleIfError($result);
	  		return true;
		}
	
	 	public function querySQL($sql) {
			if (DBS_SHOW_SELECTS || $this->debugMode)
			{
				echo '<div style="background-color: lightgreen; font-size: 12px;">';
				echo $sql;
				echo '</div>';
			}
			if (DBS_SHOW_EXPLAIN )
			{
				echo '<div style="background-color: lightblue; font-size: 12px;">';
				print_r($this->dbc->getAll('EXPLAIN '.$sql, array(), DB_FETCHMODE_ASSOC));
				echo '</div>';
			}
	  		$data = $this->dbc->getAll($sql, array(), DB_FETCHMODE_ASSOC);
	  		$this->handleIfError($data);
	  		return $data;
	 	}
	
		public function selectLastInsertId() {
	  		return $this->selectField(array('what' => "LAST_INSERT_ID()"));
	 	}
		
		public function countRows($p){
			//i count how many rows table has
			$p['what'] = 'count(*)';
			return $this->selectField($p);
		}
		
		public function getMaxRow($p){
			$p['what'] = "MAX({$p['rowName']})";
			$p['rowName'] = null;
			return $this->selectField($p);
		}
	
		public function dict2pairs($array){
			$result = array();
			foreach($array as $key => $value){
				$result[] = "$key = " . $this->quote($value);
			}
			return implode(', ', $result);
		}
	
		private function handleIfError($data)
		{
			if (DB::isError($data))
			{ 
				//$log = $GLOBALS['log']['object'];
				//if ($log->getShowMessage() == 0) //minimal message
				//{
				//	die($data->getMessage());
				//}
				//else
				//{
					$data->backtrace = '';
					echo '<div style="z-index: 9999; position: absolute; background: #fff; width:100%;">' . Trace::print_r($data, $data->getMessage(), '#eeffee') . '</div>';
					trigger_error ('DB SLQ Error.', E_USER_ERROR);
				/*}
				if ($log->getStoreMessage() == -1)
				{}
				else if ($log->getStoreMessage() == 0)
				{
					$log->store('error', 'db', $data->getMessage());
				}
				else
				{
					$log->store('error', 'db', print_r($data ,true));
				}*/
				die();
			}
		}
		
		public function getDbc () {
			
			return $this -> dbc;
			
		}
		
		public function quote($data) {
			
			if (is_array($data)) {
				foreach ($data as &$childData) {
					$this->quote($childData);
				}
			} else if (is_string($data)) {
				if (substr($data, 0, 9) == '#noQuote#') {
					$data = substr($data, 9); 
				} else{
					$data = $this->getDbc()->quoteSmart($data);
				}
			} else if (is_null($data)) {
				$data = 'Null';
			} else if (is_bool($data)) {
				$data = intval ($data);
			}
			return $data;
			
		}
		 
	}