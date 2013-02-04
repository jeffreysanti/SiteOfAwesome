<?php
/*
 * Sets up supreme constants for system
 */

// CONFIG INDEPENDENT DEFENITIONS
define("NL", "\r\n");

function checkForDataRedirect(array $params) // checks if a page is requesting a datafile
{
    // we need to make sure this isn't a data file requested
    // check if it is a CSS/Image File
    // then find the file on disk
    // set content-type header so browser recongizes it :)
    $types = array("css", "test");
    foreach ($params as $key => $value) {
	if(in_array($value, $types))
	{
	    if(file_exists($value . "/" . $params[$key+1]))
	    {
		if($value == "css")
		    header ("content-type:text/css");
		include($value . "/" . $params[$key+1]);
	    }
	    else
	    {
		soa_error("Request failed:: type:".$value." string:".implode("/", $params), false);
	    }
	    die();
	}
    }
}

// FUNCTIONS
function soa_error($msg, $DoShow=true, $DoDie=true, $DoLog=true)
{
    if($DoLog)
    {
	$hndl = fopen(__DIR__."/log.txt", "a");
	fwrite($hndl, date("D, d M Y H:i:s") . " :: " . $msg . NL);
	fclose($hndl);
    }
    if($DoDie && $DoShow)
	die("An Error Has Occured: " . $msg);
    if($DoDie)
	die();
    if($DoShow)
	echo "An Error Has Occured: " . $msg;
}

// LOADING OF CONFIG
if(file_exists("config.php"))
{
    require_once("config.php");
    define("SOA_CONFIG_LOADED", true); // prevents certain scripts from running (installer.php)
    
    // include db connection
    require_once("core/dbdefn.php");
    $dbc = EstablishDataBaseConnection();
}
else
{
    $uri = strtolower($_SERVER['REQUEST_URI']);
    $params = explode("/", $uri);
    checkForDataRedirect($params);
    include("installer.php"); // show installation webpage
    soa_error("Missing Config", false);
}



?>
