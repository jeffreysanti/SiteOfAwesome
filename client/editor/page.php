<?php
/*
 * Main page for client editor
 */

if(!defined("PG_CLSCL"))
    soa_error("editor.php page accessed without permission");

// verify client privillages (not subclient)
if($userrow['type'] != 1){
    header("location: ".SOA_ROOT);
    die();
}
define("PG_CL", true);

define("ARROW", "&nbsp;&nbsp;&nbsp;&rarr;&nbsp;&nbsp;&nbsp;");

// find out what page to show
if(count($params) > 1){
    $pg = $params[2];
}
else{
    $pg = "";
}

switch($pg){
    case "cg":{
        require("cg.php");
        break;
    }
    case "look":{
        require("look.php");
        break;
    }
    case "info":{
        require("info.php");
        break;
    }
    case "art":{
        require("art.php");
        break;
    }
    default: {
        require("mainpg.php");
        break;
    }
}
?>
