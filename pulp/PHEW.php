<?php 

define('NORMAL', 1);
define('INSTR', 2);


class PHEW
{
	
	var $state = NORMAL;
	var $inStr = '';
	var $strQuot = '';
	var $inFunc = '';
	var $inClass = '';
	var $depth = 0;
	var $inStrCode = false;
	
	var $funVars = array();
	var $inArray = 0;
	var $funFuns = array();
	
	var $cur = 0;
	
	function compile($c)
	{
		$len = strlen($c);
		$w = '';
		$count = 0;
		while ( $this->cur + 5 <= $len && $count < 10000)
		{
			$w .= $this->getNextTag($c, $this->cur);
			$count ++;
		}
		//$w .= "\t}";
		
		$funs = $this->funFuns; $this->funFuns = array();
		$c2 = '';
		foreach($funs as $k => $v)
		{
			$func = $v[0];
			list($pars, $bod) = explode('->', $v[1]);
			$c2 .= "fun lsf_{$func}_$k($pars) $bod \n";
		}
		
		$this->cur = 0;
		$len = strlen($c2);
		$w2 = "\n\n"; 
		while ( $this->cur + 5 <= $len && $count < 10000)
		{
			$w2 .= $this->getNextTag($c2, $this->cur);
			$count ++;
		}		
		$w2 .= "\t}";
		
		return $w . $w2 . "\n}";
	}
	
	function getNextTag($c, $cur)
	{
		
		$word = substr($c, $cur, $this->getEndOfNextTag($c, $cur) - $cur);
		$newcur = $cur + strlen($word);
		$r = $word;
		$word = trim($word);
		
		if ($this->inStr && !$this->inStrCode)
		{
			if ($word == $this->inStr){
				//echo 'OUTStr';
				$this->inStr = '';
			}
			elseif ($word == '{{'){
				$this->inStrCode = true;
				$r = "{$this->inStr} . (";
			}
			elseif ($word == '{'){
				$r = "{\$";
			}
			elseif ($word == '}'){
				$r = "}";
			}
		}
		elseif ($this->inFunc && ($word != 'fun'))
		{
			if ($word == '}}' && $this->inStrCode){
				$this->inStrCode = false;
				$r = ") . {$this->inStr}";
			}
			elseif ($word == '..'){
				$r =  '.=';
			}
			elseif ($word == '#'){
				$r =  $this->inClass.'::';
			}
			elseif ($word == '\\'){
				$r =  '[' . $this->getNextTag($c, $newcur) . ']';
				$newcur = $this->cur;
			}
			elseif ($word == 'var'){
				$var = trim($this->getNextTag($c, $newcur));
				$this->funVars[] = $var;
				$r =  '$'.$var;
				$newcur = $this->cur;
			}
			elseif ($word == 'fvar'){
				$pos1 = strpos($c, "=", $newcur);
				$name = trim(substr($c, $newcur, $pos1 - $newcur));
				//echo $name . '"""';
				$pos2 = strpos($c, ";", $pos1);
				$body = trim(substr($c, $pos1 + 1, $pos2 - $pos1 + 1));
				//echo $body . '"""';
				$this->funFuns[$name] = array($this->inFunc, $body);
				$r =  "\n";
				$newcur = $pos2 + 2;
			}
			elseif ($word == '@@'){
				$r =  ''.$this->getNextTag($c, $newcur);
				$r =  '$_GLOBALS['.$r.']';
				$newcur = $this->cur;
			}
			elseif ($word == '"'){
				//echo 'inStr';
				$this->inStr = $word;
			}
			elseif (substr($word, 0, 1) == ':'){
				$r = "'".substr($word, 1)."'";
			}
			elseif ($word == '['){
				$this->inArray ++;
				$r = " array(";
			}
			elseif ($word == ']'){
				$this->inArray --;
				$r = ")";
			}
			elseif ($word == '=' && $this->inArray){
				$r = " =>";
			}
			elseif (in_array($word, $this->funVars) && !$this->inStr){
				$r = " $".$word;
			}
			elseif (isset($this->funFuns[$word]) && !$this->inStr){
				
				$r = "array('" . $this->inClass . "', 'lsf_" . $this->inFunc . "_" .$word . "')";
			}
			//print_r($this->funFuns);
			//echo $word. $this->inStr.'%' ;
			
		}
		else if ($this->inClass || $this->inFunc)
		{
			if($word == 'fun') {
				if (!$this->inClass) echo("ERR: Not in class $this->inFunc.");
				$firstFun = $this->inFunc == '';
				$this->inFunc = trim($this->getNextTag($c, $newcur));
				$cur2 = strpos($c, ')', $this->cur) + 1;
				$params = $this->processParams(substr($c, $this->cur,  $cur2 - $this->cur));
				//echo "<b>$params</b>";
				$newcur = $cur2;
				$r = ($firstFun?"":"\t}\n\t") .  
					"function " . $this->inFunc . $params . "{";
			}
		}
		else 
		{
			if($word == 'class') {
				$this->inClass = trim($this->getNextTag($c, $newcur));
				$cur2 = strpos($c, "\n", $this->cur);
				$extends = substr($c, $this->cur, $cur2 - $this->cur);
				$newcur = $cur2;
				$r = "class" . $this->inClass . ' ' . $extends . " {";
			}
		}		
		/*if ($word == '{'){
			$depth ++;
			$r =  $word;
		}
		if ($word == '}'){
			$depth --;
			if (depth == 0) $this->inClass = '';
			if (depth == 1) $this->inFunc = '';
			$r =  $word;
		}*/
		$this->cur = $newcur;
		return $r;
	}
	
	function getSmaller($acc, $v)
	{
		if ($v)
		{
			if (!$acc) return $v;
			$acc = $acc < $v ? $acc : $v;
		}
		return $acc;
	}
	
	function getEndOfNextTag($c, $cur)
	{
		if (
			substr($c, $cur, 2) == "@@" ||
			substr($c, $cur, 2) == "{{" || 
			substr($c, $cur, 2) == "}}"
		)
			return $cur + 2;
			
		if (
			substr($c, $cur, 1) == "'" ||
			substr($c, $cur, 1) == '"' ||
			substr($c, $cur, 1) == "#" ||
			substr($c, $cur, 1) == "\\" ||
			substr($c, $cur, 1) == "(" ||
			substr($c, $cur, 1) == ")" ||
			substr($c, $cur, 1) == "[" ||
			substr($c, $cur, 1) == "]" ||
			substr($c, $cur, 1) == "{" ||
			substr($c, $cur, 1) == "}" ||
			substr($c, $cur, 1) == "," ||
			substr($c, $cur, 1) == "\t"
		)
			return $cur + 1;
	
		$e = array();
		$e[] = strpos($c, "{", $cur + 1);
		$e[] = strpos($c, "}", $cur + 1);
		$e[] = strpos($c, "[", $cur + 1);
		$e[] = strpos($c, "]", $cur + 1);
		$e[] = strpos($c, "@", $cur + 1);
		$e[] = strpos($c, ";", $cur + 1);
		$e[] = strpos($c, "'", $cur + 1);
		$e[] = strpos($c, '"', $cur + 1);
		$e[] = strpos($c, ',', $cur + 1);
		$e[] = strpos($c, '(', $cur + 1);
		$e[] = strpos($c, ')', $cur + 1);
		$e[] = strpos($c, '#', $cur + 1);
		$e[] = strpos($c, '\\', $cur + 1);
		$e[] = strpos($c, '.', $cur + 1);

		$e[] = strpos($c, ' ', $cur + 1);
		$e[] = strpos($c, "\n", $cur + 1);
		$e[] = strpos($c, "\r", $cur + 1);
		$e[] = strpos($c, "\t", $cur + 1);
		$r = array_reduce($e, array('PHEW', 'getSmaller'));
		
		
		return $r;
	}
	
	function processParams($p)
	{
		$p = str_replace('(', '', $p);
		$p = str_replace(')', '', $p);
		$p = str_replace(',', '', $p);
		$p = trim($p);
		if (!$p) { return '()'; $this->funVars = array(); $this->funFuns = array();}
		$ps = explode(' ', $p);
		$this->funVars = $ps;
		foreach($ps as $k => $p) $ps[$k] = '$'.$p;
		return '('.implode(', ', $ps) . ')';
	}
}

if (1)
{
	$f = new PHEW();
	echo $f->compile('
class Users extends Base

	fun getFullName(u)
		return u\:name . " " . u\:surname;

	fun show_hi()
		var usr = #getUserById(@@:auth.getUserId());
		return "Hi {{#getFullName(usr)}}!";
		
	fun getUserById(id)
		return DBs::selectRow(@@:dbc, [
			:from = @@:t\:users,
			:where = "id = {id} " ,
		]);

	fun saveUser(d)
		var df = Arr::filter_keys(d, [ :username, :password, :age, :birth_date ] );
		var vstr = [:string, :len<30, :nospace];
		var dv = Arr::validate_keys(df, [
					:username = vstr,	:password = vstr,
					:age = [:int, :<150, :>0] ,
					:birth_date = [:mysqldate]]);
		DBs::insertRow(@@:dbc, [:into = @@:t\:users, :pairs = df]);
		@@:msgman.add("User was added", :ok);

	fun lambdatest(x, y)
		fvar add2 = a, b -> return a + b ;
		return Arr::reduce([1, 2, 3, 4, 5], add2 );





');

	// http://pastebin.be/8719
	// @@ -- GLOBAL hashtable -- this is the only way to reach anything out of object scope
	// @ -- nonstatic function of self -- the only way to reach anything out of function scope
	// # -- static function of self
	// :string -- "string"
	// [] -- array()
	// fun -- function
}	
/*	
	echo $f->compile('

---- file Users.phew
	
class Users extends WebBase

	fun getFullName(u)
		return u\name . ' ' . u\surname;

	fun show_hi()
		var u = #getUserById(@@auth.getUserId());
		return "Hi {{ #getFullName(u) }}!";
		
	fun getUserById(id:Int!)
		return DBs:selectRow(@@dbc, [
			:from = @@t\users
			:where = "id = $id" ]);
		

	fun saveUser(d:Arr)
		var df = Arr:filter_keys(d, [:username, :password, :age, :birth_date]);
		var valid = [:string :len<30 :nospace];
		df = Arr:validate_keys(df, [:username = valid :password = valid :age = [:int :<150 :>0] :birth_date = [:mysqldate]]);
		DBs:insertRow(@@dbc, [
			:into = @@t\users
			:pairs = df
		]);
		@@msgman.add("User was added", :ok);

---- file MessageMan.phew
		
module MessageMan
		
	var msgs = [];
		
	fun add(t, c)
		msgs[c] = t;
		
	fun show_msgs()
		var o = <ul>;
		foreach(k = msg in @msgs){
			o .. <li class="{k}">{msg}</li>;
		}
		return o . <ul>;
		
--- globals.phew

const PATH '';

@@mgsman = new MessageMan();
@@auth = new Auth();
');
}

/ *

 - @@ is global distributed hash table... all other values are only local and nonpersistent between states
 - @@tables.users -- $_GLOBALS['tables']['users']


*/


	/*$functionMap = array(
		'Arr' => array(
			'map' => 'array_map',
			'reduce' => 'array_reduce',
			'filter' => 'array_filter',
			'iter' => '',
			'reducek' => '',
			'set_defaults' => '',
			'to_indexed' => '',
		),
		'Str' => array(
			'sub' => 'substr',
			'pos' => 'strpos',
			'replace' => 'str_replace',
			'starts_with' => '',
		),
		'Web' => array(
			'GET' => '',
			'GETi' => '',
			'POST' => '',
			'POSTi' => '',
		),
		'Sess' => array(
			'get' => '',
			'set' => '',
			'get_i' => '',
			'clear' => '',
		),
		'DBs' => array(
			'selectField' => '',
			'selectRow' => '',
			'selectRows' => '',
			'deleteRows' => '',
			'insertRow' => '',
			'updateRows' => '',
		),
	);*/