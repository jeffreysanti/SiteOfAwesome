<?php

/*
 * Main page for showing articles on client
 */

if(!defined("PG_CLSCL"))
    soa_error("mainpg.php page accessed without permission");


writeheader(SOA_TITLEBAR, "main.css");
$a = array();
array_push($a, new menuItem("Home", SOA_ROOT));
array_push($a, new menuItem("About", SOA_ROOT.params(array("about"))));
array_push($a, new menuItem("Contact", SOA_ROOT.params(array("contact"))));
array_push($a, new menuItem("Editor", SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem("Logout", SOA_ROOT."/logout.php"));
client_header(SOA_HEAD1, SOA_HEAD2, $a);

client_footer();
writefooter();

?>
