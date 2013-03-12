<?php
/*
 * This page displays the login screen & handles it
 */
global $dbc;
if(isset($_POST['submit'])) // login attempt was made
{
    $user = $_POST['user'];
    $pass = md5($_POST['password']);
    try{
	$q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE username = ? AND password = ?');
        $q->execute(array($user, $pass));
        if($q->rowCount() > 0)
        {
            $r = $q->fetch(PDO::FETCH_ASSOC);
            if($r != FALSE)
            {
                // establish session & redirect
                $_SESSION["soa_uid"] = $r['id'];
                header("location: ".SOA_ROOT);
                die();
            }
        }
        define("lerror", true);

    }catch(PDOException $e){
        soa_error($e->getMessage());
    }
}
echo 
'<!DOCTYPE html>'.NL.
'<html>'.NL.
'    <head>'.NL.
'	<title>SiteOfAwesome Login</title>'.NL.
'	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.NL.
'	<link rel="stylesheet" type="text/css" href="'.SOA_ROOT.'/css/'.SOA_THEME.'/login.css" />'.NL.
'   </head>'.NL.
'   <body>'.NL.
'	<div id="login_page"><form method="post" action="'.SOA_ROOT.'/login.php">'.NL;
if(defined("lerror"))
{
    echo
'           <span class="error" style="position: absolute; top: 15px; left: 50px;">Invalid Login Credentials</span>';
}
echo
'	    <span style="position: absolute; top:50px; left:50px;">Username</span>'.NL.
'	    <input name="user" type="text" style="top:50px;right:50px;" />'.NL.
'	    <span style="position: absolute; top:80px; left:50px;">Password</span>'.NL.
'	    <input name="password" type="password" style="top:80px;right:50px;" />'.NL.
'	    <input name="submit" type="submit" value="Login" style="left:50px; top:110px; width: 280px; height:26px;" />'.NL.
'       </form></div>'.NL.
'   </body>'.NL.
'</html>';
?>
