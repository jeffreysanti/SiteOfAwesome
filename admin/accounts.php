<?php

/*
 * Accounts page for admin interface
 */
if(!defined("PG_ADMIN"))
    soa_error("accounts.php page accessed without permission");

// find out what page to show
if(count($params) > 1){
    $pg = $params[2];
}
else{
    $pg = "";
}

switch($pg){
    case "add":{
        require("accounts/add.php");
        break;
    }
    case "list":{
        require("accounts/list.php");
        break;
    }
    case "sudo":{
        require("accounts/sudo.php");
        break;
    }
    case "del":{
        require("accounts/del.php");
        break;
    }
    default: {
        if(isset($_POST['submit'])){ // setting has been changed
            // process data
            if(isset($_POST['allowSignup']))
                $asignup = 1;
            else
                $asignup = 0;
            
            // update database
            updateSiteDBParam('asignup', $asignup, "-1");
        }
        
        // get database info
        $asignup = getSiteDBParam("asignup", null, "1");
        
        // draw page
        writeheader("Accounts - SiteOfAwesome Administration", "admin.css");

        // menu entires
        $a = array();
        array_push($a, new AdminNavEntry("Main Page", ""));
        array_push($a, new AdminNavEntry("Accounts", SOA_ROOT.params(array("accounts")), true));
        array_push($a, new AdminNavEntry("Add Account", SOA_ROOT.params(array("accounts", "add")), false, true));
        array_push($a, new AdminNavEntry("List Accounts", SOA_ROOT.params(array("accounts", "list")), false, true));
        array_push($a, new AdminNavEntry("Appearance", SOA_ROOT.params(array("look"))));
        array_push($a, new AdminNavEntry("&nbsp;", "-")); // seperator
        array_push($a, new AdminNavEntry("Logout", SOA_ROOT."/logout.php"));

        admin_writeheader("Accounts - SiteOfAwesome Administration", $a);

        // main content::
        $sasignup = $asignup ? " checked" : "";
        echo
'           <form method="post" action="'.$thispg.'">'.NL.
'               <div class="sdivsion">Basic Account Settings</div><br />'.NL.
'               Allow Users To Signup: <input type="checkbox" name="allowSignup" '.$sasignup.' /><br /><br />'.NL.
'               <input id="save" type="submit" name="submit" value="Save Changes" />'.NL.
'           </form>'.NL;

        admin_writefooter();
        writefooter();
        break;
    }
}

?>
