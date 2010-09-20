<?php

require_once "Template.php";

class FullTable
{
	var $myId;
	var $rowsPerPage;
	var $headers;
	var $noDataString;
	var $limitToAndSort;
	var $specialFieldTemplate;

	var $tableParams;
	var $thParams;
	var $trParams;
	var $tdParams;
	
	function FullTable($myId='dbtable001')
	{
		$this->myId = $myId;
		$this->rowsPerPage = 10;
		$this->tableParams = "width='100%'";
		$this->headers = array();
		$this->noDataString = 'No data';
		$this->specialFieldTemplate = '';
		$this->limitToAndSort = array();
	}

	function setHeaders()
	{
	
	}

	function showTable($data)
	{
		//we get data outside and provide start / count to it... so more flexible
		//$data = $DBs::selectRows($this->db, $this->tableName, $this->colsToShow, $this->whereFilter, 
	
		$out = '';
		
		if (!count($data))
		{
			$out .= $this->noDataString;
		}
		else
		{
			$limitToActive = count($this->limitToAndSort) > 0;
			$out .= "<table {$this->tableParams}><tr>";
			//echo heades
			if ($limitToActive)
			{
				$dataToLoop = $this->limitToAndSort;
			}
			else
			{
				$dataToLoop = array_keys($data[0]);
			}
			
			foreach ($dataToLoop as $key)
			{
				$header = isset($this->headers[$key]) ? $this->headers[$key] : ucfirst(str_replace('_', ' ', $key));
				$out .= "<th>$header</th>";
			}
			if ($this->specialFieldTemplate) $out .= "<th><span style='color: grey'>cmd</span></th>";
			$out .= '</tr>';
			//echo data 
			foreach ($data as $l)
			{
				$out .= "<tr>";
				//foreach ($l as $k => $f)
				if ($limitToActive)
				{
					$dataToLoop = $this->limitToAndSort;
				}
				else
				{
					$dataToLoop = array_keys($data[0]);
				}
				
				foreach($dataToLoop as $key)
				{
					$show = true;
					if ($limitToActive)
					{
						if (!in_array($key, $this->limitToAndSort))
						{
							$show = false;
						}
					}				
					if ($show) $out .= "<td>".stripslashes($l[$key])."</td>";
				}
				if ($this->specialFieldTemplate)
				{
					
					$out .= '<td class="end">'.Template::templatize($this->specialFieldTemplate, $l);
					
					$out .= $this->specialFieldTemplateMore($l).'</td>'; //
				}
				$out .= "</tr>";
			}
			$out .= "</table>";
		}
		return $out;
	}
	
	function showSubPages($count, $getParams='')
	{
		$out = '';
		if ($this->rowsPerPage > 0)
		{
			$page = isset($_GET[$this->myId.'_subPage']) ? $_GET[$this->myId.'_subPage'] - 1: 0;
			$out .= '<div class="pagenum">';
			for($i=1; $i<= ceil($count/$this->rowsPerPage); $i++)
			{
				$sel = '';
				//sm, todo, make here ACT state for active page, add class "act" to existing css def --> class="pagenum act"
				if ($page == $i-1) $sel = 'style="margin: 0 0.125em; background: #fff; border: 2px solid #0a0;"';
				$out .= "<a href='?{$this->myId}_subPage={$i}{$getParams}' $sel>$i</a> ";
			}
			$out .= '</div>';
		}
		return $out;
	}
	
	function getLimitValues()
	{
		$page = isset($_GET[$this->myId.'_subPage']) ? $_GET[$this->myId.'_subPage'] - 1: 0;
		return $this->rowsPerPage * $page.", ".$this->rowsPerPage;
	}
	
	function getSpecialFieldBasicTemplate($moreGetParams='')
	{
		return "<div>
				<a href='?{$this->myId}_change={id}$moreGetParams#form' class='edit'>edit</a>  
				<a href='?action_{$this->myId}_delete={id}$moreGetParams' onclick='return confirm(\"Do you wish to delete this record?\")' class='delete'>delete</a></div>";
	}
	
	function specialFieldTemplateMore($row)
	{
		//you can owerwrite this when extending
		return '';
	}
}
?>