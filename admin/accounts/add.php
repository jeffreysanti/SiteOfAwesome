<?php

/*
 * Adds a user account
 */

if(!defined("PG_ADMIN"))
    soa_error("accounts/add.php page accessed without permission");


$uname = '';
$pass = '';
$admin = false;
$error = '';

if(isset($_POST['submit']))
{
    // process information
    $uname = $_POST['uname'];
    $pass = md5($_POST['pass']);
    $admin = isset($_POST['admin']);
    $error = '';
    if(md5($_POST['conf']) != $pass)
        $error = $error . "Passwords do not match<br />".NL;
    
    // verify username does not already exist
    try{
        $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE username = ?');
        $q->execute(array($uname));
        if($q->rowCount() > 0)
            $error = $error . "Username already exists<br />".NL;
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    
    // add account if able to
    if($error == "")
    {
        try{
            $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_users (username, password, type)'.
                ' VALUES (?, ?, ?)');
            $q->execute(array($uname, $pass, $admin ? 0 : 1));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
        header("location: ".SOA_ROOT.params(array("accounts")));
        die();
    }
}


// draw page
writeheader("Add Account - SiteOfAwesome Administration", "admin.css");

// menu entires
$a = array();
array_push($a, new AdminNavEntry("Main Page", ""));
array_push($a, new AdminNavEntry("Accounts", SOA_ROOT.params(array("accounts"))));
array_push($a, new AdminNavEntry("Add Account", SOA_ROOT.params(array("accounts", "add")), true, true));
array_push($a, new AdminNavEntry("List Accounts", SOA_ROOT.params(array("accounts", "list")), false, true));
array_push($a, new AdminNavEntry("Appearance", SOA_ROOT.params(array("look"))));
array_push($a, new AdminNavEntry("&nbsp;", "-")); // seperator
array_push($a, new AdminNavEntry("Logout", SOA_ROOT."/logout.php"));

admin_writeheader("Add Account - SiteOfAwesome Administration", $a);

// main content::
$achecked = $admin ? " checked" : "";
echo
'           <form method="post" action="'.$thispg.'">'.NL.
'               <div class="sdivsion">Account Information</div><br />'.NL.
'                   <table border="0">'.NL;
if($error != "")
{
    echo 
'                       <tr><td><span class="error">Error(s):</span></td><td><span class="error">'.$error.'</span></td></tr>';
}
echo
'                       <tr><td>User Name:</td><td><input type="text" name="uname" value="'.$uname.'" /></td></tr>'.NL.
'                       <tr><td>Password:</td><td><input type="text" name="pass" /></td></tr>'.NL.
'                       <tr><td>Confirm:</td><td><input type="text" name="conf" /></td></tr>'.NL.
'                       <tr><td>Admin:</td><td><input type="checkbox" name="admin" '.$achecked.' /></td></tr>'.NL.
'                   </table><br />'.NL.
'               <input id="save" type="submit" name="submit" value="Add Account" />'.NL.
'           </form>'.NL;

admin_writefooter();
writefooter();


?>
