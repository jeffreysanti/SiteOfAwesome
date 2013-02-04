<?php
/*
 * Manages all pages on the webapp, gathering all information and redirecting as nessesary.
 * All parameters stored in $params global var
 */
ob_start();
require_once("global.php");

$params = array();
if(defined("SOA_REWRITE"))
{
    $uri = str_replace(SOA_ROOT, "", strtolower($_SERVER['REQUEST_URI']));
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

echo "bye";
//soa_error("Random Error");
var_dump($params);

ob_end_flush();
?>
