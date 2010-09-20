<?php

require_once "Str.php";
require_once "Arr.php";

class QueryBuilder
{
	function smartBuild($p)
	//if editing set changeId otherwise adding
	{		
		$p = Arr::setDefaults($p, array('tablePrefix'=>'', 'filters'=>array(), 'order'=>''));
		list($table, $scheme, $tablePrefix, $filters, $order) = 
				Arr::toIndexed($p, array('table', 'scheme', 'tablePrefix', 'filters', 'order'));
		
		
		$what = "$table.*";
		$from = $table;
		$where = '';
	
		foreach($scheme as $e)
		{
			if (Str::startsWith($e['Field'], 'id_'))
			//foreign key in some other table
			//so we show select field with id's as values and 
			//what is described in 'Labels' as labels
			//in that table key must be id
			{
				if (isset($e['SelectLabelRow']))
				{
					$otherTableName = substr($e['Field'], 3);
					$otherTable = $tablePrefix.$otherTableName;
					$what .= ", {$otherTable}.{$e['SelectLabelRow']} AS {$otherTableName}_{$e['SelectLabelRow']}";
					$from .= " LEFT JOIN $otherTable ON $table.{$e['Field']} = $otherTable.id ";
				}
			}
		}
		
		$first = true;
		foreach($filters as $key => $val)
		{
			if (!$first) $where .= " AND ";
			$where .= " $key = '$val' ";
			$first = false;
		}
		
		return array('what'=>$what, 'from'=>$from, 'where'=>$where, 'order'=>$order);
	}	
	
	function getFilters($params)
	//gets only filters from array and changes filter_id_key => 2 to id_key => 2
	{	
		$ret = array();
		foreach ($params as $key => $val)
		{
			if (Str::startsWith($key, 'filter_'))
			{
				$name = substr($key, 7);
				$ret[$name] = $val;
			}
		}
		return $ret;
	}

	function reaplyFilters($params)
	//gets only filters from array and changes filter_id_key => 2 to id_key => 2
	{	
		$ret = '';
		foreach ($params as $key => $val)
		{
			if (Str::startsWith($key, 'filter_'))
			{
				$ret .= "&amp;$key=$val";
			}
		}
		return $ret;
	}
	
}
?>
