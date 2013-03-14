<?php
/*
 * Manages all pages on the webapp, gathering all information and redirecting as nessesary.
 * All parameters stored in $params global var
 */
ob_start();
require_once("global.php");

$params = array();
$thispg = $_SERVER['REQUEST_URI'];
if(defined("SOA_REWRITE"))
{
    $uri = str_replace(strtolower(SOA_ROOT), "", strtolower($_SERVER['REQUEST_URI']));
    $params = explode("/", $uri);
}
else
{
    $uriBase = strtolower($_SERVER['REQUEST_URI']);
    $uri = substr($uriBase, strpos($uriBase, "?")+1);
    $params = explode("&", $uri);
    foreach ($params as $k => $v) {
	$params[$k] = substr($v, strpos($v, "=")+1);
    }
}
foreach ($params as $k => $v) {
    if($v == "")
	unset($params[$k]);
}
checkForDataRedirect($params); // fullfill any data requests

// begin page stuff :)

session_start();
if(!isset($_SESSION['soa_uid'])) // they're not logged-in yet
{
    LoadSiteSettings(-1);
    require("login.php");
    die();
}

// check for core functions
if(count($params) > 0 && $params[1] == "logout.php") // logout instruction
{
    require("logout.php");
}

global $dbc;

// now find out who they are :)
$r = $dbc->query("SELECT * FROM ".DB_PRE."_users WHERE id = '".$_SESSION['soa_uid']."'")->fetchAll();
if(count($r) < 1) // they are not really logged in ???
    require("logout.php");
$userrow = $r[0]; // isolate 1st row (hopefully there were not multiple matches)

// sort by type
switch($userrow['type']){
    case 0:{            // Administrator
        LoadSiteSettings(-1);
        require("admin/page.php");
        break;
    }
    default:{
        soa_error("User with unknown type: ".$userrow['type'].', id: '.$userrow['id']);
    }
}


ob_end_flush();
?>
