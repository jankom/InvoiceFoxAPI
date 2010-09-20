<?php

//
//
// WARNING: THIS IS FAR FROM FINAL STRPC IMPLEMENTATION AND SPEC
// I AM USING IT AND WILL SEE IN PRACTICE WHAT WORKS THE BEST AND THEN 
// FINALIZE THE SPEC AND CODE ... HOWEVER, AND OPPINIONS ARE WELCOME
//
//


//RPC is basically just about transfering a function name and some params over net.. 


//This is a revised version 
//of braindump I had when I tried to make something as simple as possible...

//XMLRPC is nice, but why complicate?... REST is intresting too but each resource it's own URI?... 
//how do I simply do that without REWRITE RULES... I WOULD LIKE TO CALL FUNCTIONS NOT 
//RESOURCES WITH A RPC (something that has some parameters and potentially returns something back)
//I wan't basic variables returned and sent like numbers, strings and arrays not XML and stuff
//----
//what is the simplest possible way without any complication to do RPC over HTTP?
//Use the POST OR GET params... easiest to call - easiest to serve on server... even without any libs...

//RPC is basically just about transfering a function name and some params.. and a module, class, package?
//
//params should be named not ordered.. much more (human) error prone
//there should be only basic variable types allowed... int,double/float,boolean,string,array,hash
//param values should for clarity and type be serialized... the one that comes to my mind first is PHP serialize, 
//it's simple and allready implemented in python and pearl too.. I didn't like it at first because of length values..
//I wanted something as minimal and lightweight as possible... but then I figured that having lenght is a plus, especially
//for less dynamic languages and it makes deserialization quicker to implement... so be it PHP
//
//One additional thing... to exactly know that it's a STRPC call and avoid confusion with other POST/GET params
//lets say that if we are making a STRPC call we add call=STRPC...
//
//And to mark and prevent the problem of having call,package,procedure in our procedure params we mark these 3 with _ in front.
//
//This is the translation table... thanks to http://www.hurring.com/code/python/serialize/ who also made those 
//two modules
//
//Type	Serialized	Example
//NULL	N;	N;
//Integer	i:$data;	i:123;
//Double	d:$data;	d:1.23;
//Float	d:$data;	d:1.23;
//Boolean	b:$bool_value;	b:1;
//String	s:$data_length:"$data";	s:5:"Hello"
//Array	a:$key_count:{$key;$value}	a:1:{i:1;i:2}
//	$value can be any data type
//
//_call and _procedure are only required params
//
//So finally we have a full blown example of this... this are example GET or POST params:
//_call=STRPC&_package=users&_procedure=getByIdAndGroup&id=i:239;&group=s:7:"Writers"
//
//return is a serialized array with boolean that defines succses or error and returnedData... 
//returnedData can of course be array of anything
//if success is false it returnedData is short human understandable description of the error.
//it works like this a:2:{bool:1, s:12:"returnData"}
//
//so at the end:
//- easyer to call (from js on Ajax webpages too), from forms, from url line, or any language that can make ordinary HTTP POST or GET
//- easyer to process on serverside (if you can process ordinary html forms you can process this even without any library
//- easyer to debug: no more tracing proxies to debug... just call it from your browser with url line or with form and see
//  the output *this is one of my favorites because I really hate jumping trough hops to just see the output*
//- low stran on the computer and network... no XML parsing libraries, small footprint and no overhead.. untouchy with the urls 
// (put it wherever you need it to)
//- fully works with and inside the HTTP Web... no layers above it... so you can use things like BASIC AUTH, sessions? whatever you wish
//- with only POST to send you can write your own customizable client or server library in minutes
// TODO: make it possible that it is also called on a normal html page (result embeded into it...)

//PEAR
require_once('HTTP/Request.php');
require_once("Htmlize.php");
require_once("Trace.php");

class STRPC
{
	var $map;
	var $debug;
	
	// array(array('package', 'procedure', array(array('param1','number'), array('param2','string'), array('param3','array')))
	// we rather define types... we could leave them to function but because this functions will be accsessible openly
	// and from many languages 
	// the order of the params defines the order in calling the functions (php doesn't support named params)
	// we are rather more restrictive than not (types must be defined and can't exchange) all params are requred

	function STRPC()
	{
		$this->map = array();
		$this->debug = false;
	}
	
	function isCall()
	{
		if (isset($_REQUEST['_call']))
		{
			return $_REQUEST['_call'] == 'strpc';
		}
		return false;
	}

	function getPackage()
	{
		return isset($_REQUEST['_module'])?  $_REQUEST['_module'] : '';
	}
	
	function getProcedure()
	{
		return $_REQUEST['_procedure'];
	}

	function serve($map=array())
	{
	
		//return $this->wrapStrpc(serialize(array(true, 'asdasd')));
		//if we added map as param lets set it
	
		if($this->isCall())
		{

			if (count($map))
			{
				$this->map = $map;
			}

			$package = $this->getPackage();
			$procedure = $this->getProcedure();

			echo $package . '-----' . $procedure;
			
			foreach ($map as $item)
			{
				//if we find package + procedure that is beindg called in map
				if ($item[0] == $package && $item[1] == $procedure)
				{
					//we check if we have all the params and are of right type
					//and we build the params array that is then given to the function call
					
					$paramsArray = array();
					
					foreach ($item[2] as $params)
					{
						$paramName = $params[0];
						$paramType = $params[1];
						
						if (isset($_REQUEST[$paramName]))
						{
							if ($this->checkTypeFromSerialized($_REQUEST[$paramName], $paramType))
							{
								$paramsArray[] = Htmlize::decode(stripslashes($_REQUEST[$paramName]));
								//echo $_REQUEST[$paramName];
							}
							else
							{
								return $this->wrapStrpc(Htmlize::encode(array('STRPC_Err', "STRPC: param_wrong_type '$paramName' should be '$paramType'")));
							}
						}
						else
						{
							return $this->wrapStrpc(Htmlize::encode(array('STRPC_Err', "STRPC: param_missing '$paramName'")));
						}
					}
					//print_r($paramsArray);
					//Trace::print_r($paramsArray, 'params');
					//so we checked all params and all are ok
					
					//if package is defined let's call a method of it
					if($package)
					{
						
						//call the method if object exists with that name
						global $$package;

						$ret = '';
						if (isset($$package))
						{
							if (is_object($$package))
							{
								if (is_callable(array(&$$package, $procedure)))
								{
									$ret = call_user_func_array(array(&$$package, $procedure), $paramsArray);
								}							
							}
						}
						//call the static method if class with that name exists
						else if (class_exists($package, false))
						{
							//if (method_exists($package, $procedure))
							if (is_callable($package, $procedure))
							{
								$ret = call_user_func_array(array($package, $procedure), $paramsArray);
							}
						}
					}
					//else we call a ordinary function
					else
					{
						if (function_exists($procedure))
						{
							$ret = call_user_func_array($procedure, $paramsArray);
						}
					}
					
					return $this->wrapStrpc(Htmlize::encode($ret));
				}
			}
			//if we processed whole map then it doesn't exist in map
			return $this->wrapStrpc(Htmlize::encode(array(false, "STRPC: package_procedure_undefined $package::$procedure")));
		}
	}

	function checkTypeFromSerialized($sParam, $paramType)
	{
		//echo $sParam[0];
		switch ($paramType)
		{
			case 'number': 
				return Str::startsWith($sParam, '<i>');
			case 'string': 
				return Str::startsWith($sParam, '<b>');
			case 'array': 
				return Str::startsWith($sParam, '<dl>') || Str::startsWith($sParam, '<ul>');
			default:
			  return false;
		}
	}

	function call($url, $package, $procedure, $params)
	//url:
	//params: array('param1' => 12, 'param2' => 'some text')
	{
		if ($this->debug){ echo '<div class="STRPC-debug">#STRPC-client-request-to:' . $url . '</div>'; }
		$req = &new HTTP_Request($url);
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		
		$req->addPostData('_call', 'strpc');
		$req->addPostData('_module', $package);
		$req->addPostData('_procedure', $procedure);
		if ($this->debug){ echo '<div class="STRPC-debug">#STRPC-client-request-package-procedure:' . $package .' '. $procedure . '</div>'; }
		if ($this->debug){ echo '<div class="STRPC-debug">#STRPC-client-request-params:' . Format::dictToGETParams($params, '', ' ') . '</div>'; }
		foreach($params as $name => $value)
		{
			$req->addPostData($name, Htmlize::encode($value));
		}
		
		if (!PEAR::isError($req->sendRequest())) 
		{
			$response = $req->getResponseBody();
			
			$responseRaw = str_replace('<strpc>', '&lt;strpc&gt;', $response);
			$responseRaw = str_replace('</strpc>', '&lt;/strpc&gt;', $responseRaw);
			
			if ($this->debug){ echo '<div class="STRPC-debug">#STRPC-client-got-raw:' . $responseRaw . '</div>'; }
			
			//get contents in <strpc></strpc> tags ... so we can more robustly return values (even with other text, notices, errors don't break it)
			$pos1 = strpos($response, '<strpc>');
			$pos2 = strpos($response, '</strpc>');
			$data = substr($response, $pos1 + 7, $pos2 - $pos1 - 7);
			
			$r = Htmlize::decode($data);
			if ($this->debug){ echo '<div class="STRPC-debug">#STRPC-client-got-Array:'; print_r($r); echo '</div>'; }
			return $r;
		}
		return array('STRPC_Err', "STRPC: HTTP_error $url;$package::$procedure" . $req->getResponseCode());
	}
	
	function wrapStrpc($r)
	{
		return '<strpc>'.$r.'</strpc>';
	}
	
	function isError($r)	
	{
		if (is_array($r))
		{
			if (isset($r[0]))
			{
				return ("{$r[0]}" == 'STRPC_Err');
			}
		}
		return false;
	}

	function makeError($text)	
	{
		return array('STRPC_Err', $text);
	}

}
?>