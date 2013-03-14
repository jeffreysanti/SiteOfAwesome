<?php

/*
 * Comfirms that an admin wants to delete another user
 */
if(!defined("PG_ADMIN"))
    soa_error("accounts/del.php page accessed without permission");

// get account info
if(count($params) > 2 || $params[3] == $_SESSION['soa_uid']){ // don't allow self-account deletion
    $did = $params[3];
}
else{
    header("location: ".SOA_ROOT.params(array("accounts", "list"))); // invalid request
    die();
}
try{
    $q = $dbc->prepare("SELECT * FROM ".DB_PRE."_users WHERE id = ?");
    $q->execute(array($did));
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
if($q->rowCount() < 1){
    header("location: ".SOA_ROOT.params(array("accounts", "list"))); // user invalid id
    die();
}
$r = $q->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit']))
{
    if($_POST['conf'] == "yes") // okay -- delete it :(
    {
        // TODO: Delete any subaccounts etc associated with clients
        try{
            $q = $dbc->prepare("DELETE FROM ".DB_PRE."_users WHERE id = ? LIMIT 1");
            $q->execute(array($did));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
        header("location: ".SOA_ROOT.params(array("accounts", "list", "postdel")));
        die();
    }
    header("location: ".SOA_ROOT.params(array("accounts", "list"))); // nope
}

// draw page
writeheader("Delete Account - SiteOfAwesome Administration", "admin.css");

// menu entires
$a = array();
array_push($a, new AdminNavEntry("Delete", "", true));

admin_writeheader("Delete Account - SiteOfAwesome Administration", $a);

// main content::

echo 
'           <div class="sdivsion">Delte Account?</div><br />'.NL.
'           <form method="post" action="'.$thispg.'">'.NL.
'               <p>Are you sure you wish to delete the account under the username: '.NL.
'                   <b>'.$r['username'].'</b>?</p><br />'.NL.
'               <input type="radio" name="conf" value="no" checked /> No <br />'.NL.
'               <input type="radio" name="conf" value="yes" /> Yes <br /><br />'.NL.
'               <input id="save" type="submit" name="submit" value="Apply Changes" />'.NL.
'           </form>'.NL;

admin_writefooter();
writefooter();
?>
