<?php
/*	template utility_class
	
 	:dependencies:
 	NO
 
*/

class Template {

	var $content;
	var $start;
	var $end;

	function Template($content, $start='{', $end='}'){
	/** Constructor: Loads tempalte as string. Use load or load_random to load it from file. **/
		$this->start = $start;
		$this->end = $end;
		$this->content = $content;
	}

	function load($template_file) {
	/** Statically called: Get chosen template. Return content. **/		
		return implode('', file($template_file));
	}


	function load_random($template_folder, $template_start) {
	/** Statically called: Get randomly chosen template with some start001 from some folder. Return content. **/
		
		$templates = array();
		
		//collect all files with that begining
		$d = dir($template_folder); 
		while (false !== ($entry = $d->read())) { 
			if ( eregi('^'.$template_start, $entry)) $templates[] = $entry;		//'.*\.tpl$'
		} 
		$d->close();
		
		if (!isset($templates[0])) die('No templates found. Please notify webmaster.');
		//randomy chose one read it and return it
		srand_once();
		$index = rand(0, count($templates)-1);
		return implode('', file($template_folder.$templates[$index]));
	}
	
	function fill($variables) {
	/** fill $template wit array of variabless, or just one variable. **/
		
		if ( ! is_array($variables)) $variables = array($variables);
		
		foreach ( $variables as $variable => $value) {
			$this->content = str_replace($this->start.$variable.$this->end, $value, $this->content); 	
		}
		return $this->content;
	}
	
	function store($filename) {
	/**  **/
		$fp = fopen($filename, 'w');
		$result = fwrite($fp, $this->content);
		fclose($fp);
		return $result;
	}
	
	function templatize($template, $data){
	//static function to do it all
		$doc = new Template($template);	
		$doc->fill($data);
		return $doc->content;
	}
}
//HELPER FUNCTIONS

/*function srand_once($seed = ''){
//i Srand can be called only once. So this func simpy fixes it and makes a good seed.
	static $wascalled = FALSE;
	if (!$wascalled){
		$seed = $seed === '' ? (double) microtime() * 1000000 : $seed;
		srand($seed);
		$wascalled = TRUE;
	}
}*/
