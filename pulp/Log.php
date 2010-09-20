<?php
/*
Make this table for this class to work

CREATE TABLE `_log` (
  `id` int(11) NOT NULL auto_increment,
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` varchar(100) NOT NULL default '',
  `subtype` varchar(100) NOT NULL default '',
  `msg` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM; 
*/

class Log
{
	var $db;
	var $showMessage; //0 - yes small, 1-yes big
	var $storeMessage; //0 - yes small, 1-yes big
	
	function Log($db, $showMessage_=1, $storeMessage_=1)
	{
		$this->db = $db;
		$this->showMessage = $showMessage_;
		$this->storeMessage = $storeMessage_;
	}
	
	function store($type, $subType, $msg)
	{
		return DBs::insertRow($this->db, $GLOBALS['t']['_log'], array(Null, '#noQuote#NOW()', $type, $subType, $msg));
	}

	function getShowMessage()
	{
		return $this->showMessage;
	}

	function getStoreMessage()
	{
		return $this->storeMessage;
	}
}

?>