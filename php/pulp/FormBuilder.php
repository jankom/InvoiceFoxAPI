<?php

require_once "Str.php";

class FormBuilder
{
	var $form;
	var $scheme;
	var $myId;
	var $settings;
	private $editSubmitText;
	private $addSubmitText;

	//just give the form object in, so you can set it up 
	function FormBuilder($fullFormObj, $scheme, $myId, $tablePrefix='', $settings=array())
	//outside anyhow you wish 
	//we need db only if there are any selects so we need to query values
	{
		$this->form = $fullFormObj;
		$this->scheme = $scheme;
		$this->myId = $myId;
		$this->tablePrefix = $tablePrefix;
		$this->settings = $settings;
	}
	
	public function setAddSubmitText($text) {
		
		$this->addSubmitText = $text;
		
	}
	
	public function setEditSubmitText($text) {
		
		$this->editSubmitText = $text;
		
	}
	
	public function htmlspecialchars_array($array, $quote_style)
	{
		$a = array();
		foreach( $array as $key => $item)
		{
			if (is_string($item))
			{
				$a[$key] = htmlspecialchars($item, $quote_style);
			}
			else
			{
				$a[$key] = $item;
			}
		}
		return $a;
	}
	
	function show($action='', $method='post')
	//if editing set changeId otherwise adding
	{
		$out = '';
		if (isset($this->settings['formParams'])) 
		{
			$this->form->formParams = $this->settings['formParams'];
		}
		$out .= '<a name="form"></a>';
		$out .= $this->form->startForm($action, '', $method);
		
		$changeId = isset($_GET[$this->myId.'_change']) ? $_GET[$this->myId.'_change'] : '';
		
		//outputamo hidden fielde
		$out .= '<div>';
		foreach($this->scheme as $e)
		{
			$value = isset($e['forceValue']) ? $e['forceValue'] : $e['Default'];
		
			if (isset($e['forceType']))
			{
				if ($e['forceType'] == 'hidden')
				{
					$out .= $this->form->hidden($e['Field'], $value);
				}
			}
			else if ($e['Key'] == 'PRI')
			//we have a primary key
			{
				if ($e['Extra'] == 'auto_increment')
				//if auto increment we don't show it
				{
					$out .= $this->form->hidden($e['Field'], $changeId);
				}
			}
		}
		$out .= '</div>';
		
		$out .= $this->form->startFieldset();
		
		if ($changeId)
		{
			$p = array('from' =>$this->myId,
					   'where'=>"id = $changeId");

			$this->form->applyDefaults($this->htmlspecialchars_array(stripslashes_deep(DBs::inst()->selectRow($p)), ENT_QUOTES));
			$submit = "action_{$this->myId}_change"; //TODO - make it settable
			if (isset($this->editSubmitText)) {
				$submitStr = $this->editSubmitText;
			} else {
				$submitStr  = 'Update';
			}

		}
		else
		{
			$submit = "action_{$this->myId}_add";
			if (isset($this->addSubmitText)) {
				$submitStr = $this->addSubmitText;
			} else {
				$submitStr  = 'Add';		
			}
		}
		
		//few rules ... 
		//id is primary key
		//id_some_table_name is foreign key
		
		foreach($this->scheme as $e)
		{
			$label = isset($e['label']) ? $e['label'] : $e['Field'];
			
			$value = $e['Extra'] == 'md5' ? '***' : ($changeId? '': ( isset($e['forceValue']) ? $e['forceValue'] : $e['Default'] ));
			//echo $value;
			if (isset($e['forceType']))
			{
				if ($e['forceType'] != 'hidden' && $e['forceType'] != 'none')
				{
					$out .= $this->form->input($e['forceType'], $e['Field'], stripslashes($value), '', 
												array('text' => $label, 
													  'note' => $e['Extra']=='md5'?'Leave empty if you don`t want to chage password!':'rdasdasd'
										));
				}
			}
			else if ($e['Key'] == 'PRI')
			//we have a primary key
			{
				if ($e['Extra'] == 'auto_increment')
				//if auto increment we don't show it
				{
					// outputtamo gor višje $out .= $this->form->hidden($e['Field'], $changeId);
				}
				else
				{
					$out .= $this->form->input('text', $e['Field'], '', '', array('text' => $label)); //todo make that labels can be set up
				}
			}
			else if (Str::startsWith($e['Field'], 'id_'))
			//foreign key in some other table
			//so we show select field with id's as values and 
			//what is described in 'Labels' as labels
			//in that table key must be id
			{
				$labelRow = isset($e['SelectLabelRow']) ? ','.$e['SelectLabelRow'] : '';
				$where = isset($e['Filter']) ? $e['Filter'] : '';
				$p = array('from' =>$this->tablePrefix.substr($e['Field'], 3),
						   'what' =>"id $labelRow",
						   'where'=>$where,
						   'order'=>$e['SelectLabelRow']);
				$options = DBs::inst()->selectRows($p, DB_FETCHMODE_ORDERED, isset($e['SelectLabelRow']));
				if (!isset($e['addEmptyOption'])) $e['addEmptyOption'] = false;
				
				if (true) // todo make optional ---- make order by label optional also
				{
				
					if ($e['addEmptyOption'])
					{
						$options[0] = "";
					}
					foreach ($options as $key => $option)
					{
						if ($key != 0) $options[$key] = "$option [$key]";
					}
				}
				$out .= $this->form->input('select', $e['Field'], '', '', array('text' => $label), array('options' => $options, 'useKeys' => isset($e['SelectLabelRow'])));
			}
			else 
			//we have an ordinary field
			{
				if (Str::startsWith($e['Type'], 'text'))
				{
					$out .= $this->form->input('textarea', $e['Field'], stripslashes($value), 'cols="70" rows="11"', array('text' => $label));
				}
				else
				{
					$out .= $this->form->input('text', $e['Field'], stripslashes($value), 'size="50"', 
														array('text' => $label, 
													  'note' => $e['Extra']=='md5'?'Make empty if you don`t want to chage the password!':''
										));
				}
			}
		}
		$out .= $this->form->endFieldset();

		//SUBMIT AND CANCEL IS OUTSIDE HERE
		$out .= '<div>';
		$out .= $this->form->input('submit', $submit, $submitStr, '', array('text'=>'&#160;'));
		if (!isset($this->settings['cancelLink'])) $this->settings['cancelLink'] = false;
		if ($this->settings['cancelLink']) $link = $this->settings['cancelLink'];
		else $link = $_SERVER['PHP_SELF'] . (isset($_GET['id']) ? '?id='.$_GET['id'] : '');
		$out .= "<p><a href='{$link}'>Cancel</a></p>";
		$out .= '</div>';
		
		$out .= $this->form->endForm();
		return $out;			
	}	
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}

?>
