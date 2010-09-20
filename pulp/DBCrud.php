<?php
/*i
*/

require_once "DBs.php";
require_once "Stone.php";

class DBCrud extends Stone{

	var $myId;
	var $descr;

	function DBCrud($myId, $descr)
	{
		parent::__construct();
		$this->myId = $myId;
		$this->descr = $descr;
	}
	
	protected function add() {
		
		$d = $_POST;
		$inserArr = array();
		
		foreach($this->descr as $e)
		{
			$value = $d[$e['Field']];
			if ($e['Key'] == 'PRI')
				//we have a primary key
			{
				if ($e['Extra'] == 'auto_increment')
				{
					$value = Null;
				}
				if ($e['Extra'] == 'md5')
				{
					$value = md5($value);
				}
			}
			if ($value == '' && isset($e['Default'])) {
				//echo 'rok';
				$value = $e['Default'];
			}
			$insertArr[$e['Field']] = $value;
		}
			
		$p = array('into' =>$this->myId,
				   'pairs'=>$insertArr);
		$this->db->insertRow($p);
		
		$p = array('from'=>$this->myId,
				   'what'=>'MAX(id) as maxId');
		$line = $this->db->selectRow($p);
		$id = $line['maxId'];
					
		//$this->_addMsg('Zapis je bil dodan.', 'ok'); //TODO: make it so this strings can be customized ... make them in array that can be preset in code
		$this->_addMsg('The record was added.', 'ok'); //TODO: make it so this strings can be customized ... make them in array that can be preset in code
		return $id;
		
	}
	
	protected function change() {
		
		$d = $_POST;
		$id = $d['id'];			
		unset($d['id']);
		unset($d["action_{$this->myId}_change"]);
		$updateArr = array();

		foreach($this->descr as $e)
		{
			if (isset($d[$e['Field']])) {
				$value = $d[$e['Field']];
				echo "->".$e['Extra'];
				if ($e['Extra'] == 'md5')
				{
					if (strlen($value) > 0) {
						$value = md5($value);
					}
					
				}

				if (!($e['Extra'] == 'md5' && strlen($value) == 0)) {
					$updateArr[$e['Field']] = $value;
				}
			}
		}
		$p = array('what' =>$this->myId,
				   'pairs'=>$updateArr,
				   'where'=>"id = $id");
		$this->db->updateRows($p);
		$this->_addMsg('The record was changed.', 'ok');
        return $id;
        
	}
	
	protected function delete() {
		
		$d = $_POST;
		
		$id = $_GET["action_{$this->myId}_delete"];
		$p = array('from' =>$this->myId,
				   'where'=>"id = $id");
		$this->db->deleteRows($p);
		//$this->_addMsg('Zapis je bil izbrisan', 'ok');
		$this->_addMsg('The record was deleted', 'ok');
		
	}

	function doBasicActions($settings=array())
	{	
		
		if (isset($_POST["action_{$this->myId}_add"]))
		{ 		
			$this->add();
		}
		else if (isset($_POST["action_{$this->myId}_change"]))
		//change
		{
			$this->change();
		}
		else if (isset($_GET["action_{$this->myId}_delete"]))
		//delete
		{
			$this->delete();
		}
	}
	
}

?>
