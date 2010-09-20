<?php

/*

PHPtonic::in

::Html:: FUNCTION LIBRARY IN A CLASS

*/

class Html{

	function li($type='ul', $data=array('line 1'), $ulParams=''){
	/** static: checks for standard conditions **/	
		$out = '';
		if (is_array($data))
		{
			foreach($data as $line)	$out = "<li>$line</li>\n";
		}
		else
		{
			$out = "<li>$data</li>";
		}
		return "<$type $ulParams>$out</$type>";
	}
	
	function table($data=array(array('data1')), $tableProps=NULL, $cellProps=NULL, $header=NULL){
	/** static: Outputs a table  **/	
		$out = "<table $tableProps>\n";
		if (! is_null($header)) {
			$out .= '<tr>';
			foreach ($header as $title) $out .= "<th>$title</th>";
			$out .= "</tr>\n";
		}
		foreach($data as $line)	{
			$out .= '<tr>';
			foreach($line as $cell) $out .= "<td $cellProps>$cell</td>";
			$out .= "</tr>\n";
		}
		return $out.'</table>';
	}

	
	function tableDict($data=array('title' => 'value'), $tableProps=NULL, $titleProps=NULL, $valueProps=NULL, $header=NULL){
	/** static: Outputs a two coll table from dict**/	
		$out = "<table $tableProps>\n";
		if (! is_null($header)) {
			$out .= '<tr>';
			foreach ($header as $title) $out .= "<th>$title</th>";
			$out .= "</tr>\n";
		}
		foreach($data as $title => $value)	{
			$out .= "<tr><td $titleProps>$title</td><td $valueProps>$value</td></tr>\n";
		}
		return $out.'</table>';
	}
	
	function h($text, $num=1){
		/** Output header, if null nothing**/
		if (!is_null($text)) return "<h$num}>$text</h{$num}>";
		else return '';
	}

	function tag($tag, $text){
		/** Output some tag **/
		if (!is_null($text)) return "<{$tag}>$text</{$tag}>";
		else return '';
	}
	
	function redirection($url){
		///Outputs html doc that will redirect to some other url
		return "<html><body bgcolor='grey' onload='document.location=\"$url\"'>...</body></html>";
	}
	
	function wrapToHtml(
		$data, 
		$wrap, 
		$defaultWrap=array(
			'encoding'=>'windows-1250',
			'title' => '',
			'css' => array(),
			'js' => array(),
			'h1' => Null,
			'p' => Null)
		){
		//% Wraps html document around some data that becomes content of body
		//% can link/include any number of css or js files
		//% can also have some doc title and intro, h1 and p param
		if (is_null($wrap)) return $data;
		$wrap = array_merge($defaultWrap, $wrap); 
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset='.$wrap['encoding'].'"/>
  <title>'.$wrap['title'].'</title>';
  		foreach($wrap['css'] as $css)
			$out .= '  <link rel="stylesheet" type="text/css" href="'.$css.'"/>'."\n";
  		foreach($wrap['css'] as $css)
			$out .= '  <script language="JavaScript" src="'.$css.'"/>'."\n";
		$out .= ' </head>
 <body>';
		$out .= Html::h($wrap['h1']);
		$out .= Html::tag('p', $wrap['p']);
		$out .= $data;
		$out .= ' </body>
<html>';		
		return $out;
	}

	function backButton($text="Nazaj"){
		return "<input type='reset' onclick='javascript:history.go(-1); return false;' value='$text'>\n";
	}
	
	function backLink(){
		return 'TODO<input type="reset" onclick="javascript:history.go(-1); return false;" value="Nazaj">';
	}
	
	function closedDiv($id, $html, $name, $closeName, $title, $wrapDivParams='')
	{
		$out = "
						<div $wrapDivParams>
						<a href='#' title='open $title' onclick=\"javascript: document.getElementById('$id').innerHTML = '$html'; return false; \">$name</a>
						<a href='#' title='close $title' onclick=\"javascript: document.getElementById('$id').innerHTML = ''; return false; \">$closeName</a>
						<div id='$id'></div>\n
						</div>";
		return $out;
	}
	
	function getArrayFromLi($text)
	{
		$t = $text;
		$result = array();
		$pos1start = strpos($t, '<li');
		$pos1 = strpos($t, '>', $pos1start);
		while ($pos1start !== false)
		{
			$pos2 = strpos($t, '</li>', $pos1);
			$result[] = substr($t, $pos1 + 1, $pos2 - $pos1 - 1);
			$pos1start = strpos($t, '<li', $pos2);
			$pos1 = strpos($t, '>', $pos1start);
		}
		return $result;
	}
	
	function getArrayFromLiWithParams($text)
	{
		$t = $text;
		$result = array();
		$pos1start = strpos($t, '<li');
		$pos1 = strpos($t, '>', $pos1start);
		while ($pos1start !== false)
		{
			$params = substr($t, $pos1start + 3, $pos1 - $pos1start - 3);
			$pos2 = strpos($t, '</li>', $pos1);
			$content = substr($t, $pos1 + 1, $pos2 - $pos1 - 1);
			$result[] = array($content, $params);
			$pos1start = strpos($t, '<li', $pos2);
			$pos1 = strpos($t, '>', $pos1start);
		}
		return $result;
	}
	function getArrayFromHrSeparated($text)
	{
		$t = $text;
		$result = array();
		$pos1 = 0;
		$pos2 = 0;
		while (1)
		{
			$pos2 = strpos($t, '<hr', $pos1);
			if ($pos2 === false) break;
			$r = substr($t, $pos1, $pos2 - $pos1);
			if (trim($r)) $result[] = $r;
			$pos1 = strpos($t, '/>', $pos2) + 2;
		}
		//final doesn't allways finish with hr, so from last to the end is another
		$r = substr($t, $pos1);
		if ($r) $result[] = $r;
		return $result;
	}

	function getArrayOfHs($t, $hNum, $excludeClass)
	{
		$result = array();
		$include = true;
		$pos1start = strpos($t, "<h$hNum");
		$pos1 = strpos($t, '>', $pos1start);
		$include = !(Html::getParam(substr($t, $pos1start, $pos1 - $pos1start), 'class') == 'nobreak');
		
		while ($pos1start !== false)
		{
			$pos2 = strpos($t, "</h$hNum>", $pos1);
			if ($include)
			$result[] = substr($t, $pos1 + 1, $pos2 - $pos1 - 1);
			$pos1start = strpos($t, "<h$hNum", $pos2);
			$pos1 = strpos($t, '>', $pos1start);
			$include = !(Html::getParam(substr($t, $pos1start, $pos1 - $pos1start), 'class') == 'nobreak');
		}
		return $result;
	}

	function getHeaderPos($t, $hNum, $excludeClass, $offset=0)
	{
		$include = true;
		$pos1Start = strpos($t, "<h$hNum", $offset);
		if ($pos1Start !== false)
		{
			$pos1 = strpos($t, '>', $pos1Start);
			$include = !(Html::getParam(substr($t, $pos1Start, $pos1 - $pos1Start), 'class') == $excludeClass);
		}
		return array($pos1Start, $include);
	}

	function getParam($t, $name)
	{
		$res = '';
		$start = strpos($t, $name.'=');
		if ($start !== false)
		{
			$start += strlen($name)+1;
			$strSign = substr($t, $start, 1);
			if ($strSign == "'" or $strSign == '"')
			{
				$start += 1; //now pointing to firs char of value
				$res = substr($t, $start, strpos($t, $strSign, $start) - $start);
			}
		}
		return $res;
	}
	
	static public function getSubpageOutOfPage($text, $subpageId) {

		$posStart = 0;
		$idx = 0;
	
		//first header is skiped as it is part of first page
		list($pos2, $ok) = Html::getHeaderPos($text, 3, 'no-break', $posStart);
		if ($pos2 === false || !$ok)
		//if it wasn't found then return all
		{
			//echo 'returned all';
			return $text;
		}
		//set the found location as the one that will be offset for next search
		$pos1 = $pos2 + 1;
		
		for ($idx=0; $idx < $subpageId + 3; $idx ++)
		{
			list($pos2, $ok) = Html::getHeaderPos($text, 3, 'no-break', $pos1);
			if ($idx == $subpageId)
			{
				if ($pos2 !== false && $ok)
				{
					return substr($text, $posStart, $pos2 - $posStart);
				}
				else 
				{
					return substr($text, $posStart);
				}
			}
			$pos1 = $pos2 + 1;
			$posStart = $pos2;
		}
	}
	
}

/*
*TESTCASE
*/

$test = 0;

if ( $test ){
	Out::li('ul', array('line 1', 'line 2', 'line 3'));
	Out::li('ol', array('line 1', 'line 2', 'line 3'));
	
	Out::table(array(array('Janko', 'Metelko'), array('Jana', 'Mohar')), 'border="1"', NULL, array('Name', 'Surname'));
}

?>