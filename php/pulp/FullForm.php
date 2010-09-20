<?php

class FullForm
{
	var $formParams;
	var $fieldsetParams;
	var $dlParams;
	var $dtParams;
	var $ddParams;
	var $validationUlParams;
	var $helpAParams;
	var $breakIfNote;
	var $inLine;
	var $validationNotes;
	var $defaults;
	private $fullFormName;

	function FullForm()
	{
		$this->formDivParams='';
		$this->rowDivParams='';
		$this->textDivParams='';
		$this->fieldDivParams='';
		$this->helpAParams='class="help" tabindex="-1"';
		$this->validationDivParams = '';
		$this->breakIfNote=true;
		$this->inLine=true;
		$this->validationNotes = array();
		$this->defaults = array();
		$this->fullFormName='fullformname';
	}
	
	/*
	todo, globalno rešiti JS include
	function initJs()
	{
		return "<script type='text/javascript' src='bin/pulp/js/fullform.js'></script>";
	}
	*/
	
	function startForm($action, $otherCode='', $method='post', $id='fullformname')
	{
		$this->fullFormName=$id;
		$out = "
			<form action='$action' method='$method' id='$id' {$this->formParams}> 
				$otherCode 
		";
		//name=fullformname is temporary here because of how "stupid" mce filemanager/imagemanager works
		//it doesn't work with multiple forms on one page for example but names shouldn't be element identifiers anyway!
		//id's should be so we will change mce
		return $out;
	}

	function endForm()
	{
		return  "</form>";
	}

	function applyValidationNotes($validationNotes)
	{
		$this->validationNotes = $validationNotes;
	}

	function applyDefaults($defaults)
	{
		$this->defaults = $defaults;
	}

	function input(
		$type,  //type of input fiel special cases: justrow
		$name,  //type of input fiel
		$value='', //value
		$params='',  //any params of elements as string
		$userTexts=array(), //text - text in front of field that user sees
												//note - smaller note to name that user sees
												//help - html that displays after clicking  [?]
		$otherSettings=array(), //we put various special case stuff here (like options for select)
		$validationTags=array() //array with tags that javascript checks in real time - TODO later
	)
	{
	
		
		if (!isset($userTexts['text'])) $userTexts['text']='';
		if (!isset($userTexts['note'])) $userTexts['note']='';
		if (!isset($userTexts['help'])) $userTexts['help']='';
		
		if (!$value)
		//if no value use the defaults if any
		{
			$value = isset($this->defaults[$name]) ? $this->defaults[$name] : '';
		}
		
		$out = '';
		//$out .= "
		//	<dt {$this->rowDivParams}>
		//";
		//$out .= Trace::print_r($this->validationNotes);
		//validation notes

		if ($type == 'justrow')
		{
			$out .= $name; //in case of type justrow second parameter is html contents of that row div
										//so you can put anything inthere
		}
		else
		{
			 if ($type == 'submit') {
			
				$out .= "<dt>&nbsp;</dt><dd><input type='$type' id='id_$name' name='$name' value='$value' class='$type' $params />";
				if (isset($_GET['oncancel'])) {
					$out .= "<input type='button' value='Cancel' onclick='document.location=\"" . htmlspecialchars($_GET['oncancel']) . "\"' /></dd>";
				} 
				return $out;
			}
			$out .= "
					<dt {$this->dtParams}>
						<label for='id_$name'>".ucfirst(str_replace('_', ' ', $userTexts['text']))."</label>";
					
			//help
			if ($userTexts['help'])
			{		
				$out .= "<a href='#' onclick='javascript:FullForm_doHelp(this, \"{$userTexts['help']}\"); return false;' {$this->helpAParams}>?</a>";
			}
			
			$out .= "</dt>
					<dd {$this->ddParams}>
					";

			if (isset($this->validationNotes[$name]))
			{
				$out .= Html::li('ul', $this->validationNotes[$name], $this->validationUlParams);
			}
					
			if ($type == 'text' || $type == 'password')
			{
				$out .= "<input type='$type' id='id_$name' name='$name' value='$value' class='$type' $params />";
			
			}  else if ($type == 'checkbox')
			{
				$sel = '';
				if ($value) $sel = 'checked="checked"';
				$out .= "<input type='$type' id='id_$name' name='$name' value='1' $sel class='$type' $params />";
			}
			else if ($type == 'textarea')
			{
				if (isset($GLOBALS['output_mime_xml']) and $GLOBALS['output_mime_xml']) $value = '<![CDATA['.$value.']]>';
				$out .= "<$type id='id_$name' name='$name' $params>$value</textarea>";
			}
			else if ($type == 'richtextarea')
			{
				$value = str_replace("\n", ' ', $value);
				$value = str_replace("\r", ' ', $value);
				$out .= "
					<textarea id='id_$name' name='$name' class='editarea' $params>".htmlspecialchars($value)."</textarea>
					";
			} 
			else if ($type == 'filelink')
			{
				//sm
				//$linkStr = $value ? "|<a href='$value' target=\"_new\">prenesi</a>" : '';
				//$out .= "<!--input type='text' id='id_$name' name='$name' value='$value' $params />
				//			<a href='#' onclick='FullForm_OpenFileBrowser(\"bin/edit/editor/filemanager/browser/default/browser.html?Type=File&amp;Connector=connectors/php/connector.php\", \"id_$name\", \"file\"); return false;'>zbirka datotek</a>
				//			<span id='id_{$name}_linkSpan'>$linkStr</span-->
							
				//			<input type='text' name='$name' value='$value' $params>
				//			<a href='javascript:mcImageManager.open(\"example1\",\"url\");'>[Browse]</a>
							
				//			";
				
				$linkStr = $value ? "|<a href='$value' target=\"_new\">prenesi</a>" : '';
				$out .= "
					<input type='text' id='id_$name' name='$name' value='$value' class='$type' $params />
					<span><a href='javascript:mcFileManager.open(\"{$this->fullFormName}\",\"id_$name\");'>Prebrskaj...</a></span>
					";
				
			} 
			else if ($type == 'imagelink')
			{
				//$imgStr = $value ? "<br /><img src='$value' width='80' />" : '';
				//$out .= "<input type='text' id='id_$name' name='$name' value='$value' $params />
				//			<a href='#' onclick='FullForm_OpenFileBrowser(\"bin/edit/editor/filemanager/browser/default/browser.html?Type=Image&amp;Connector=connectors/php/connector.php\", \"id_$name\", \"image\"); return false;'>zbirka slik</a>
				//			<span id='id_{$name}_imageSpan'>$imgStr</span>";			
								$linkStr = $value ? "|<a href='$value' target=\"_new\">prenesi</a>" : '';
				$out .= "
										<input type='text' name='$name' value='$value' class='$type' id='id_$name' $params />
										<span><a href='javascript:mcImageManager.open(\"{$this->fullFormName}\",\"$name\");'>Prebrskaj...</a></span>
								";

			} 
			else if ($type == 'date')
			{
				$valueSlo = Format::toSloDate($value);
				$out .= "<input type='text' name='$name' id='id_$name' value='$valueSlo' class='$type' readonly='readonly' /><button type='reset' id='id_btn_$name' class='button'>Izberi datum</button>
								<script type='text/javascript'>
										Calendar.setup({
												inputField     :    'id_$name',      // id of the input field
												ifFormat       :    '%d.%m.%Y',       // format of the input field
												showsTime      :    false,            // will display a time selector
												button         :    'id_btn_$name',   // trigger for the calendar (button ID)
												singleClick    :    true,           // double-click mode
												step           :    1                // show all years in drop-down boxes (instead of every other year as default)
										});
								</script>";
			}
			else if ($type == 'select')
			{
				$out .= "<$type id='id_$name' name='$name' class='$type' $params>\n";
				if (isset($otherSettings['options']))
				{
					foreach($otherSettings['options'] as $key => $option)
					{
						//print_r($option);
						$out .= "<option";
						$useKeys = false;
						
						if (isset($otherSettings['useKeys']))
						{
							if ($otherSettings['useKeys'])
							{
								$useKeys = true;
								$out .= " value='$key'"; 
							}
						}
						//mark the right selected value
						$selected = false;
						if ($useKeys)
						{
							if ($key == $value)
							{
								$selected = true;
							}
						}
						else 
						{
							if ($option == $value)
							{
								$selected = true;
							}						
						}
						if ($selected)
						{
							$out .= " selected='selected'";
						}
						if(is_array($option)) $option = $option[0];
						$out .= ">$option</option>\n";
					}
				}
				$out .= "</$type>";
			}
		}
		if (isset($otherSettings['afterHtml']))
		{
			$out .= $otherSettings['afterHtml'];
		}
		if ($userTexts['note']) $out .= "<div><small>{$userTexts['note']}</small></div>";
		
		$out .= "</dd>";
		return $out;
	}
	
	function startFieldset($legend='')
	{
		$out = "<fieldset $this->fieldsetParams>";
		
		if ($legend)
		{
			$out .= "<legend>$legend</legend>";
		}
		$out .= "<dl>";
		return $out;
	}

	function endFieldset()
	{
		$out = "</dl></fieldset>";
		return $out;
	}

	function hidden($name, $value)
	{
		return "<input type='hidden' id='id_$name' name='$name' value='$value' />";
	}
	
}
?>