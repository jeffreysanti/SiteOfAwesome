<?php
if(defined("SOA_CONFIG_LOADED") || file_exists("config.php"))
{
    if(!defined("SOA_ROOT"))
	define("SOA_ROOT", "");
    header ("location:".SOA_ROOT);
    soa_error("Installer.php ran while config loaded or exists!");
}

// initialize variables for form
$instdir = "/";
$dbname = "";
$dbhost = "127.0.0.1";
$dbuser = "";
$dbprefix = "soa";
$dbpass = "";
$auser = "admin";
$apass = "";

$wnd = 0; // page 1

$error = ""; // error string
if(isset($_POST['submit'])) // manage form submission
{
    $instdir = strip_tags($_POST['instdir']);
    $dbname = strip_tags($_POST['dbname']);
    $dbhost = strip_tags($_POST['dbhost']);
    $dbuser = strip_tags($_POST['dbuser']);
    $dbprefix = mysql_real_escape_string(strip_tags($_POST['dbprefix']));
    $dbpass = strip_tags($_POST['dbpass']);
    $auser = strip_tags($_POST['auser']);
    $apass = strip_tags($_POST['apass']);
    
    // test installation directory
    $path = "http://".$_SERVER['SERVER_ADDR'].$instdir.'/test/dirtest.txt';
    $fp = @fopen($path, "r"); // supress warnings
    if($fp)
    {
	if(fgets($fp) != "OK")
	{
	    $error = $error . " <br />".NL."Installation Directory Invalid";
	}
	fclose($fp);
    }
    else
    {
	$error = $error . " <br />".NL."Installation Directory Invalid";
    }
    
    // test database connection
    if($dbname == "" || $dbhost == "" || $dbuser == "")
    {
	$error = $error . " <br />".NL."Database Connection Failed: Empty Parameter Given";
    }
    try {
	$dbConnection = new PDO("mysql:dbname=$dbname;host=$dbhost;charset=utf8", $dbuser, $dbpass);
	$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e)
    {
	$error = $error . " <br />".NL."Database Connection Failed: ".$e->getMessage();
    }
    
    // test admin u-name / pwrd
    if($auser == "" || $apass == "")
    {
	$error = $error . " <br />".NL."Admin username or password cannot be blank";
    }
    
    if($error == "") // success -> initalize database
    {
	$wnd = 1;
	require("core/dbdefn.php");
	$ret = CreateDBTablesMySql($dbConnection, $dbprefix);
	if($ret != "")
	{
	    $error = $error . " <br />" . NL . $ret;
	}
	else // write the file for configuration & add admin user
	{
	    $apass = md5($apass);
	    try{
		$preparedStatement = $dbConnection->prepare('INSERT INTO '.$dbprefix.'_users (username, password, type)' .
		    ' VALUES (?, ?, ?)');
		$preparedStatement->execute(array($auser, $apass, 0));
	    }catch(PDOException $e){
		$error = $error . " <br />" . NL . $e->getMessage();
	    }
	    if($error == "")
	    {
		$fl = fopen("config.php", "w");
		fwrite($fl, "<?php".NL.NL);
		fwrite($fl, "// Auto-Generated Configuration: ".date("D, d M Y H:i:s").NL);
		fwrite($fl, "// See config_sample.php for more info.".NL.NL.NL);
		
		fwrite($fl, 'define("DB_HOST", "'.$dbhost.'");'.NL);
		fwrite($fl, 'define("DB_NAME", "'.$dbname.'");'.NL);
		fwrite($fl, 'define("DB_USER", "'.$dbuser.'");'.NL);
		fwrite($fl, 'define("DB_PASS", "'.$dbpass.'");'.NL.NL);
		
		fwrite($fl, 'define("SOA_ROOT", "'.$instdir.'");'.NL);
		fwrite($fl, 'define("SOA_REWRITE", true);'.NL);
		
		fwrite($fl, "?>".NL);
		fclose($fl);
	    }
	}
    }
}

if($wnd == 1)
{
    echo    '<!DOCTYPE html>'.NL.
	    '<html>'.NL.
	    '<head>'.NL.
	    '	<title>SiteOfAwesome Configs Missing</title>'.NL.
	    '	<link rel="stylesheet" type="text/css" href="css/core.css" />'.NL.
	    '</head>'.NL.
	    '<body>'.NL;
    if($error != "")
    {
	echo'	<p>Errors have occured processing your data. Your input seemed valid, but failed for some reason'.NL.
	    '. Please try again later. Following are the reported errors:</p>';
	echo'<span class="error">'.NL;
	echo $error."</span>".NL;
    }
    else
    {
	echo'	<p>Installation was successful. You may now login as administrator to use SiteOfAwesome.</p>'.NL;
	echo'	<center><a href="'.$instdir.'">Return To Home</a></center>'.NL;
    }
    
    echo    '</body>'.NL.
	    '</html>'.NL;
}

if($wnd == 0)
{
    echo    '<!DOCTYPE html>'.NL.
	    '<html>'.NL.
	    '<head>'.NL.
	    '	<title>SiteOfAwesome Configs Missing</title>'.NL.
	    '	<link rel="stylesheet" type="text/css" href="css/core.css" />'.NL.
	    '</head>'.NL.
	    '<body>'.NL.
	    '	<h1 align="center">Configuration Missing</h1>'.NL.
	    '	<p>SiteOfAwesome detected that you there is no "config.php" in the root of it\'s installation.'.NL.
	    '	This may be because of an accidental deletion, or you may be installing it for the first'.NL.
	    '	time. If you are installing it you may proceed. Otherwise, please add a config.php file to the'.NL.
	    '	root of the installation using config_sample.php as a reference.<p>'.NL.
	    '	<h3>First-Time Installation:</h3>'.NL;
    if($error != "")
    {
	echo'<span class="error"><b>Errors Have Occured:</b>'.NL;
	echo $error."</span>".NL;
    }
    echo
	    '	<table><form name="inst" method="post" action="'.$_SERVER['REQUEST_URI'].'"'.NL.
	    '	<tr><td>Installation Root: </td>'.NL.
	    '	    <td><input name="instdir" type="text" value="'.$instdir.'" /></td></tr>'.NL.
	    '	<tr><td>MySQL Database: </td>'.NL.
	    '	    <td><input name="dbname" type="text" value="'.$dbname.'" /></td></tr>'.NL.
	    '	<tr><td>Database Host: </td>'.NL.
	    '	    <td><input name="dbhost" type="text" value="'.$dbhost.'" /></td></tr>'.NL.
	    '	<tr><td>MySQL User: </td>'.NL.
	    '	    <td><input name="dbuser" type="text" value="'.$dbuser.'" /></td></tr>'.NL.
	    '	<tr><td>MySQL Password: </td>'.NL.
	    '	    <td><input name="dbpass" type="text" value="'.$dbpass.'" /></td></tr>'.NL.
	    '	<tr><td>MySQL Table Prefix: </td>'.NL.
	    '	    <td><input name="dbprefix" type="text" value="'.$dbprefix.'" />_...</td></tr>'.NL.
	    '	<tr><td>Admin User: </td>'.NL.
	    '	    <td><input name="auser" type="text" value="'.$auser.'" /></td></tr>'.NL.
	    '	<tr><td>Admin Password: </td>'.NL.
	    '	    <td><input name="apass" type="text" value="'.$apass.'"</td></tr>'.NL.
	    '	<tr><td><input type="submit" name="submit" value="Install" /></td>'.NL.
	    '	    <td></td></tr>'.NL.
	    '	</form></table>';

    echo    '</body>'.NL.
	    '</html>'.NL;
}
    
    //$dbConnection = new PDO('mysql:dbname=dbtest;host=127.0.0.1;charset=utf8', 'user', 'pass');

//$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
