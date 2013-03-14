<?php

/*
 * Allows admin to login as another user
 */

if(!defined("PG_ADMIN"))
    soa_error("accounts/sudo.php page accessed without permission");

// get sudo info
if(count($params) > 2){
    $sid = $params[3];
}
else{
    header("location: ".SOA_ROOT.params(array("accounts", "list"))); // no user selected to sudo as
    die();
}
try{
    $q = $dbc->prepare("SELECT * FROM ".DB_PRE."_users WHERE id = ?");
    $q->execute(array($sid));
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
if($q->rowCount() < 1){
    header("location: ".SOA_ROOT.params(array("accounts", "list"))); // user invalid id
    die();
}
$r = $q->fetch(PDO::FETCH_ASSOC);

session_destroy();
session_start();
$_SESSION['soa_uid'] = $r['id'];

// draw page
writeheader("Sudo - SiteOfAwesome Administration", "admin.css");

// menu entires
$a = array();
array_push($a, new AdminNavEntry("Sudo", "", true));

admin_writeheader("Sudo - SiteOfAwesome Administration", $a);

// main content::

echo 
'           <div class="sdivsion">Redirecting...</div><br />'.NL.
'           <p>You have logged in as <b>'.$r['username'].'</b>, and will remain logged in as such until'.NL.
'               you log out. You will then need to log in again with your actual account information.</p><br />'.NL.
'           <p>You will automatically be redirected in ten seconds. Do so now by clicking below:</p>'.NL;
echo
'           <br /><br />'.NL.
'           <p align="center"><a href="'.SOA_ROOT.'">Redirect Now</a></p>'.NL;

admin_writefooter();

// redirect script
echo
'       <script type="text/javascript">'.NL.
'           setTimeout("delayer()", 10000)'.NL.
'           function delayer(){'.NL.
'               window.location = "'.SOA_ROOT.'"'.NL.
'           }'.NL.
'       </script>'.NL;

writefooter();
?>
