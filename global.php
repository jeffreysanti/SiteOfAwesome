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
    
    //load site info from db
    // is theme modifiable ?
    if(getSiteDBParam("tchoice", "-1", "1"))
        define("SOA_THEMECHOICE", 1); // the admin allows subthemes
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
    return $s;
}

// loads the proper theme into view
function LoadSiteSettings($num)
{
    if($num != -1 && SOA_THEMECHOICE == 0){
        LoadSiteSettings(-1); // load default theme
        return;
    }
    
    $t = getSiteDBParam("theme", $num);
    if($t != null && file_exists("css/".$t."/main.css"))
    {
        define("SOA_THEME", $t);
    }
    else{
        define("SOA_THEME", "theme_main");
    }
}

function writeheader($title = "SiteOfAwesome", $cssdoc="main.css"){
    echo 
'<!DOCTYPE html>'.NL.
'<html>'.NL.
'   <head>'.NL.
'       <title>'.$title.'</title>'.NL.
'       <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.NL.
'       <link rel="stylesheet" type="text/css" href="'.SOA_ROOT.'/css/'.SOA_THEME.'/'.$cssdoc.'" />'.NL.
'   </head>'.NL.
'   <body>'.NL.
'   <table id="container">'.NL.
'   <tr><td id="tbl_top" valign="top">'.NL;
}


function writefooter($script=""){
        echo 
'   </td></tr>'.NL.
'   <tr><td id="tbl_bottom" valign="bottom">'.NL.
'       <div id="footer">'.NL.
'           SiteOfAwesome &COPY; 2013 Jeffrey Santi'.NL.
'       </div>'.NL.
'   </td></tr>'.NL.
'   </table>'.NL.
'   </body>'.NL;
        if($script != "")
        {
            echo
'   <script>'.NL.$script.NL.
'   </script>';
        }
        echo
'</html>'.NL;
}

// returns a specified site param; if non-existancant creates if default value not null
function getSiteDBParam($param, $key=null, $defval=null){
    $qstr = "SELECT * FROM ".DB_PRE."_siteparam WHERE paramname = ?";
    if(!is_null($key))
        $qstr = $qstr . " AND keyval = ?";
    
    global $dbc;
    try{
        $q = $dbc->prepare($qstr);
        if(is_null($key))    $q->execute(array($param));
        else                 $q->execute(array($param, $key));
        if($q->rowCount() < 1) // insert new entry
        {
            if(is_null($defval))
                return null;
            if(is_null($key))
                $key = -1;
            $qstr = "INSERT INTO ".DB_PRE."_siteparam (paramname, keyval, val)";
            $qstr = $qstr . " VALUES (?, ?, ?)";
            $q = $dbc->prepare($qstr);
            $q->execute(array($param, $key, $defval));
            return $defval;
        }
        return $q->fetch(PDO::FETCH_ASSOC)["val"];
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
}

function updateSiteDBParam($param, $val, $key=null){
    $qstr = "UPDATE ".DB_PRE."_siteparam SET val = ? WHERE paramname = ?";
    if(!is_null($key))
        $qstr = $qstr . " AND keyval = ?";
    global $dbc;
    try{
        $q = $dbc->prepare($qstr);
        if(is_null($key))   $q->execute(array($val, $param));
        else                $q->execute(array($val, $param, $key));
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
}

// thanks to "yarms at mail dot ru" on: http://php.net/manual/en/ref.zip.php
function unzip($file)
{
    // TODO: Test on dev environemnt with zip enabled :(
    if(!defined("ZIP_ENABLED") || ZIP_ENABLED == false)
        return false;
    
    $zip = zip_open($file);
    if(!$zip){
        soa_error("Cannot open zip: ".$file, false, false, true);
        return false;
    }

    while($zip_entry = zip_read($zip)) {
       $zdir = dirname(zip_entry_name($zip_entry));
       $zname = zip_entry_name($zip_entry);

       if(!zip_entry_open($zip,$zip_entry,"r")){
           soa_error("File extraction failed: ".$zname . " from: ".$file, false, false, true);
           return false;
       }
       if(!is_dir($zdir))
           mkdirr($zdir,0777);

       $zip_fs = zip_entry_filesize($zip_entry);
       if(empty($zip_fs))
           continue;

       $zz = zip_entry_read($zip_entry, $zip_fs);

       $z = fopen($zname,"w");
       fwrite($z, $zz);
       fclose($z);
       zip_entry_close($zip_entry);
    }
    zip_close($zip);
    return true;
}

function mkdirr($pn,$mode=null) {
    if(is_dir($pn) || empty($pn))
        return true;
    $pn = str_replace(array('/', ''), DIRECTORY_SEPARATOR, $pn);

    if(is_file($pn)) {
        soa_error('mkdirr() File exists');
    }

    $next_pathname = substr($pn, 0, strrpos($pn, DIRECTORY_SEPARATOR));
    if(mkdirr($next_pathname, $mode)) {
        if(!file_exists($pn)) 
            return mkdir($pn,$mode);
    }
    return false;
}


?>