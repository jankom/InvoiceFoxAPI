<?php

mb_internal_encoding("UTF-8");
ini_set('display_errors', 1);
error_reporting (E_ALL); 

define('RE_NORMAL', '1');
define('RE_OPTIONAL', '2');
define('RE_BLOCK', '3');
define('RE_OR', '4');

class ReadEx 
{
	
	// (Janko;He) choose red colo*ur.
	// *(Uh), how are you?
	// *(UhWo1w), how are you?
	// #(yesterday), she went home, #(yesterday).
	// #(yesterday,) she went home #(,yesterday).
	// {use=,}*(Uh;Wow), how are you? //maybe
	//ignore ,?.:!-
	//Ignore case
	//ignore multiple spaces (tabs..)
	
	function match($t, $tr)
	{
		$posT = 0;
		$posTr = 0;
		
		$sampleResult = '';
		
		//lower case both
		$t = strtolower($t);
		$tr = strtolower($tr);
		
		//clean up the ignore chars
		$t = ReadEx::generalizeString($t);
		$tr = ReadEx::generalizeString($tr); //za tega bi bilo to bol optimalno delat že ko se zapiše v bazo
		
		$secureCount = 0;
		$state = RE_NORMAL;
		$block = false;
		$optblock = false;
		$blockStartT = 0;
		$posInsideLastBlock = 0;
		$isOptionalChar = false;
		
		do {
			
			if ($secureCount > 300) return array(false, -1);
			$secureCount ++;
		
			$charT = substr($t, $posT, 1);
			$charTr = substr($tr, $posTr, 1);

			if ($state == RE_NORMAL)
			{
				if (!$block)
				{
					if ($charTr == '*')
					{
						$state = RE_OPTIONAL;
						$posTr ++;
					}
					else if ($charTr == '(')
					{
						$block = true;
						$posTr ++;
						$blockStartT = $posT;
					}
					else if ($charT == $charTr)
					{
						$posT ++;
						$posTr ++;
					}
					else if ((ord($charT) == 0 and ord($charTr) == 32))
					{
						$posTr ++;
						//return array(true, 1);
					}
					else
					//false char
					{
						//echo "'$charT'";
						//echo ord($charT);
						//echo '-'.$posT;
						//echo "'$charTr'";
						//echo ord($charTr);
						//echo '-'.$posTr;
						/*if ((ord($charTr) == 0 or ord($charT) == 32))
						{
							return array(true, 1);
						}
						else
						{/**/
							if ($posT == $blockStartT and $posInsideLastBlock > $posT) 
							//if wrong was inside of block (after few ok) and he got out and
							//the first is wrong then return wrong position of that mistake inside the block
							{
								return array(false, $posInsideLastBlock);
							}
							else
							{
								return array(false, $posT);
							}
						//}
					}
				}
				else
				//je v blocku
				{
					if ($charTr == '*')
					{
						$state = RE_OPTIONAL;
						$posTr ++;
					}
					else if ($charTr == ')')
					{
						$block = false;
						$posTr ++;
					}
					else if ($charTr == ';')
					{
						//this option passed go to the end of block )
						$posTr = strpos($tr, ')', $posTr);
					}
					else if ($charT == $charTr)
					{
						$posT ++;
						$posTr ++;
					}
					else
					//false char
					{
						$posEndBlock = strpos($tr, ')', $posTr);
						$posEndOption = strpos($tr, ';', $posTr);
						if ($posEndBlock > $posEndOption and $posEndOption !== false)
						//if another option in block
						{
							$posTr = $posEndOption + 1;
							$posT = $blockStartT;
						}
						else
						{
							return array(false, $posT);
						}
					}
					
				}
			}
			else if ($state == RE_OPTIONAL)
			{
				if (!$optblock)
				{
					if ($charTr == '(')
					{
						$optblock = true;
						$posTr ++;
						$blockStartT = $posT;
					}
					else if ($charT == $charTr)
					{
						//oba premaknemo naprej
						$posTr ++;
						$posT ++;
						$state =  RE_NORMAL;
					}
					else
					{
						$posTr ++;
						$state =  RE_NORMAL;					
					}
				}
				else 
				{
					if ($isOptionalChar)
					{
						if ($charT == $charTr)
						{
							//oba premaknemo naprej
							$posTr ++;
							$posT ++;
						}
						else
						{
							$posTr ++;
						}
						$isOptionalChar = false;
					}
					else if ($charTr == '*')
					{
						$isOptionalChar = true;
						$posTr ++;
					}
					else if ($charTr == ')')
					{
						if ($state == RE_OPTIONAL) $state == RE_NORMAL;
						$optblock = false;
						$posTr ++;
					}
					else if ($charTr == ';')
					{
						//this option passed go to the end of block )
						$posTr = strpos($tr, ')', $posTr);
					}
					else if ($charT == $charTr)
					{
						//oba premaknemo naprej
						//echo $t;
						$posTr ++;
						$posT ++;
					}
					else
					{
						//we check if there are multiple options here
						$posEndBlock = strpos($tr, ')', $posTr);
						$posEndOption = strpos($tr, ';', $posTr);
						if ($posEndBlock > $posEndOption and $posEndOption !== false)
						//if another option in block
						{
							//echo '-';
							$posTr = $posEndOption + 1;
							$posT = $blockStartT;
						}
						else
						{					
							//premakni se na konec bloka ')'
							$posTr = strpos($tr, ')', $posTr);
							//premakni tekst nazaj na tam kjer se je blok začel v tr
							$posInsideLastBlock = $posT;
							$posT = $blockStartT;
						}
					}
					//echo '-';
					
				}
			}
		}	while ($charT or $charTr);
		return array(true, 0);
	}

	function getVariant($tr)
	{
		$posTr = 0;
		
		$res = '';
		
		//$tr = strtolower($tr);
		
		$tr = ReadEx::generalizeStringForDisplay($tr);
		
		$secureCount = 0;
		$state = RE_NORMAL;
		$block = false;
		$optblock = false;
		$blockStartT = 0;
		$posInsideLastBlock = 0;
		$isOptionalChar = false;
		
		do 
		{
			
			if ($secureCount > 300) return 'ERR OFLOW';
			$secureCount ++;
		
			$charTr = substr($tr, $posTr, 1);
			//echo $charTr;
			
			if ($state == RE_NORMAL)
			{
				if (!$block)
				{
					if ($charTr == '*')
					{
						$state = RE_OPTIONAL;
						$posTr ++;
					}
					else if ($charTr == '(')
					{
						$block = true;
						$posTr ++;
					}
					else if (ord($charTr) == 32)
					{
						$posTr ++;
						$res .= $charTr;
					}
					else
					//ordinary char
					{
						$posTr ++;
						$res .= $charTr;
					}
				}
				else
				//je v blocku
				{
					if ($charTr == '*')
					{
						$state = RE_OPTIONAL;
						$posTr ++;
					}
					else if ($charTr == ')')
					{
						$block = false;
						$posTr ++;
					}
					else if ($charTr == ';')
					{
						//this option passed go to the end of block )
						$posTr = strpos($tr, ')', $posTr);
						$posEndBlock = strpos($tr, ')', $posTr);
					}
					else
					//ordinary char
					{
						$posTr ++;
						$res .= $charTr;
					}
					
				}
			}
			else if ($state == RE_OPTIONAL)
			{
				if (!$optblock)
				{
					if ($charTr == '(')
					{
						$optblock = true;
						$posEndBlock = strpos($tr, ')', $posTr);
						$posTr = $posEndBlock + 1;
						$optblock = false;
					}
					else
					{
						$posTr ++;
						$state =  RE_NORMAL;					
					}
				}
			}
		}	while ($charTr);
		
		return $res;
	}

	function generalizeString($str)
	{
		//replace all strange signs with ' ' except ','
		
		//if (strpos($str, '’')) echo $str . '11111';
		//if (strpos($str, '‘')) echo $str . '22222';
		//echo str_replace('’', "####", 'tom’s');
		
		//first we exchange explicit charactes to right ones
		//some html encoded
		$str = str_replace('&#39;', "'", $str);
		$str = str_replace('&quot;', '"', $str);
		//some common mistakes
		$str = str_replace('`', "'", $str);
		$str = str_replace('´', "'", $str);
		$str = str_replace('’', "'", $str);
		$str = str_replace('‘', "'", $str);
		
		$chars = array('.','!',':','?','-',','); //; + smo dali ven
		$str = str_replace($chars, ' ', $str);
		
		//echo $str;
		
		//replace all ',' with ' , '. we separate ',' from words
		//this way
		$str = str_replace(',', ' , ', $str);

		//remove all multiple spaces from string
		$words = explode(" ", $str);
		$words2 = array();
		foreach($words as $word)
		{
			if (trim($word) != '')
			{
				$words2[] = $word;
			}
		}
		$str = implode(' ', $words2);
		
		return strtolower($str);
	}
	
	function generalizeStringForDisplay($str)
	{
		//first we exchange explicit charactes to right ones
		//some html encoded
		$str = str_replace('&#39;', "'", $str);
		$str = str_replace('&quot;', '"', $str);
		//some common mistakes
		$str = str_replace('`', "'", $str);
		$str = str_replace('´', "'", $str);
		$str = str_replace('’', "'", $str);
		$str = str_replace('‘', "'", $str);
		
		//$chars = array('.','!',':','?','-',','); //; + smo dali ven
		//$str = str_replace($chars, ' ', $str);
		
		//echo $str;
		
		//replace all ',' with ' , '. we separate ',' from words
		//this way
		//$str = str_replace(',', ' , ', $str);

		//remove all multiple spaces from string
		$words = explode(" ", $str);
		$words2 = array();
		foreach($words as $word)
		{
			if (trim($word) != '')
			{
				$words2[] = $word;
			}
		}
		$str = implode(' ', $words2);
		
		return $str;
	}
}

?>