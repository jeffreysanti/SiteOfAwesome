<?php
/*
 * Sets up supreme constants for system
 */

// CONFIG INDEPENDENT DEFENITIONS
define("NL", "\r\n");

function ReportNotFound($params)
{
    soa_error("Requsted object not found:".implode("/", $params), false);
}

function checkForDataRedirect(array $params, $noCare=false) // checks if a page is requesting a datafile
{
    // we need to make sure this isn't a data file requested
    // check if it is a CSS/Image File
    // then find the file on disk
    // set content-type header so browser recongizes it :)
    
    // This function is a bit confuseing
    // it is only called when rewrite is enabled to request a datafile
    // for instance a linked image or css document will be under root/--type--/file
    // without rewrite it will be directly linked; with rewrite it will be caught
    // by this function
    // assumes params structure like so: "--type--" ( / "--dir--" )*x / "--file--"
    // type is always element 1 ($value)
    // from element 2->infinity: is the directory path to it
    // fl is set to this path by imploading the array {2..} with "/" delimeters
    if(count($params) < 2)
	return;
    $params = array_values($params); // reindex
    $types = array("css", "test", "img", "ckeditor");
    foreach ($params as $key => $value) {
	if(in_array($value, $types))
	{
	    $fl = implode("/", array_slice($params, $key+1));
	    if(file_exists($value . "/" . $fl))
	    {
		if($value == "css")
		    header ("content-type:text/css");
		if($value == "img")
		    header ("content-type:image/png");
		include($value . "/" . $fl);
	    }
	    else
	    {
		if($value == "css")
		{
		    header ("content-type:text/css");
		    include("css/core.css"); // output default css
		    soa_error("CSS NOT FOUND: ".implode("/", $params), false, false, true); //non-fatal log
		}
		elseif($value == "test")
		{
		    include("core/notfound.php"); // show message to user
		    ReportNotFound($params); // log it
		}
		else
		{
		    ReportNotFound($params); // log it
		}

	    }
	    die(); // if something could have existed, it will not continue
	}
	if(!$noCare) // noCare will override the first parameter requirement
	    return;
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

global $dbc;
$siteparams = array();

// LOADING OF CONFIG
if(file_exists("config.php"))
{
    require_once("config.php");
    define("SOA_CONFIG_LOADED", true); // prevents certain scripts from running (installer.php)
    
    // include db connection
    require_once("core/dbdefn.php");
    $dbc = EstablishDataBaseConnection();
    
    //load global site data
    $r = $dbc->query("SELECT * FROM `".DB_PRE."_siteparam` WHERE keyval = '-1'");
    if($r != FALSE)
    {
        $siteparams = $r->fetchAll();
    }
    
    //load site info from db
    // is theme modifiable
    @define("SOA_THEMECHOICE", 0);
    foreach ($siteparams as $value) {
        if($value['paramname'] == "tchoice" && $value["keyval"] == -1 && $value["val"] == 1)
        {
            @define("SOA_THEMECHOICE", 1); // the admin allows subthemes
        }
    }
    define("SOA_THEME", "theme_main"); // default if else fails :(
    var_dump($siteparams);
    LoadSiteSettings(-1, $siteparams);
}
else
{
    $uri = strtolower($_SERVER['REQUEST_URI']);
    $params = explode("/", $uri);
    checkForDataRedirect($params, true); // check with noCare (no first param requirement)
    include("installer.php"); // show installation webpage
    soa_error("Missing Config", false);
}

function params(array $a) // constructs paramters on url
{
    $s = "";
    if(!defined("SOA_REWRITE"))
        $s = "?";
    $i = 0;
    foreach ($a as $value) {
        if(defined("SOA_REWRITE"))
            $s = $s . "/" . $value;
        else{
            $s = $s . "p" . $i . "=" . $value . "&";
        }
        $i++;
    }
}

function LoadSiteSettings($num, $a=FALSE)
{
    if($num != -1 && SOA_THEMECHOICE == 0)
        return; // nothing can be changed
    
    if($a == FALSE)
    {
        $r = $dbc->query("SELECT * FROM `".DB_PRE."_siteparam` WHERE keyval = '".$num."'");
        if($r != FALSE)
        {
            $a = $r->fetchAll();
        }
    }
    foreach ($a as $value) {
        if($value["keyval"] == $num)
        {
            if($value['keyval'] == 'theme' && ($num == -1 || SOA_THEMECHOICE == 1))  // theme setting
                @define("SOA_THEME", $value['val']);
        }
    }
    
}


?>
