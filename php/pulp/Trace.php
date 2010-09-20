<?php

require_once "Html.php";

class Trace
{

	function print_r($data, $title='', $color='#ffeeee'	)
	{
		return Html::closedDiv('pulpErrDiv1', Format::html_print_r($data, true), '&#160;+&#160;', 
			'&#160;-&#160;', $title, "style='background-color: $color; font-size: 10px; width: 30px;'");
	}
}

?>