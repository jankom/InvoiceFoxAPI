<?php
/*i
*/

require_once "DBs.php";

class DBSpy{

	function getDescr($table)
	{
		return DBs::inst()->querySQL("DESCRIBE $table;");
	}

	function getShortDescr($table)
	{
		$d = DBSpy::getDescr($table);
		foreach($d as $k => $l)
		{
			unset($d[$k]['Null']);
			unset($d[$k][3]);
			unset($d[$k][4]);
		}
		return $d;
	}
	
	function getFieldIdx($descr, $field)
	{
		foreach($descr as $k => $e)
		{
			if($e['Field'] == $field)
			{
				return $k;
			}
		}
		return false;
	}
}

?>