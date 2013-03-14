<?php

/*
 * Lists all client & admin accounts
 */
if(!defined("PG_ADMIN"))
    soa_error("accounts/list.php page accessed without permission");

// draw page
writeheader("List Accounts - SiteOfAwesome Administration", "admin.css");

// menu entires
$a = array();
array_push($a, new AdminNavEntry("Main Page", ""));
array_push($a, new AdminNavEntry("Accounts", SOA_ROOT.params(array("accounts"))));
array_push($a, new AdminNavEntry("Add Account", SOA_ROOT.params(array("accounts", "add")), false, true));
array_push($a, new AdminNavEntry("List Accounts", SOA_ROOT.params(array("accounts", "list")), true, true));
array_push($a, new AdminNavEntry("Appearance", SOA_ROOT.params(array("look"))));

admin_writeheader("List Accounts - SiteOfAwesome Administration", $a);

// main content::
echo 
'           <div class="sdivsion">Account List</div><br />'.NL;

if(count($params) > 2){
    if($params[3] == "postdel")
    {
        echo "Account successfully deleted.<br /><br />";
    }
}

// fetch user accounts (only admin & client)
try{
    $r = $dbc->query('SELECT * FROM '.DB_PRE.'_users WHERE type = 0 OR type = 1 ORDER BY username')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
if(count($r) > 1)
{
    echo
'           <div class="innerBorder"><table width="40%">'.NL.
'               <tr>'.NL.
'                   <th width="60%">Username <span class="admin">[Admin]</span></th>'.NL.
'                   <th width="30%">Action</th>'.NL.
'               </tr>'.NL;
    
    foreach ($r as $value) {
        if($value['id'] == $_SESSION['soa_uid'])
            continue;
        if($value["type"] == 0) // admin
        {
            $value["username"] = '<span class="admin">['.$value['username'].']</span>';
        }
        echo 
'               <tr>'.NL.
'                   <td>'.$value['username'].'</td>'.NL.
'                   <td align="center">'.NL.
'                       <a href="'.SOA_ROOT.params(array("accounts", "sudo", $value['id'])).'">'.NL.
'                           <img src="'.SOA_ROOT.'/img/'.SOA_THEME.'/login.png" alt="Login As" height="25" border="0" /></a>'.NL.
'                       <a href="'.SOA_ROOT.params(array("accounts", "del", $value['id'])).'">'.NL.
'                           <img src="'.SOA_ROOT.'/img/'.SOA_THEME.'/del.png" alt="Delete" height="25" border="0" /></a>'.NL.
'                   </td>'.NL.
'               </tr>'.NL;
    }

    echo
'           </table></div><br />'.NL;
}
else
{
    echo 
'           <p>No accounts besides yourself exist at this momment.</p>';
}

admin_writefooter();
writefooter();

?>
