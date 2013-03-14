<?php

/*
 * Appearance page for admin interface
 */
if(!defined("PG_ADMIN"))
    soa_error("look.php page accessed without permission");

if(SOA_REWRITE)
    define ("SOA_THEMES_DIR", "themes/");
else
    define("SOA_THEMES_DIR", "../themes/");

if(!chdir(SOA_THEMES_DIR))
    soa_error ("chdir failure: ".SOA_THEMES_DIR. " from ".getcwd());

// find out what page to show
if(count($params) > 1){
    $pg = $params[2];
}
else{
    $pg = "";
}

$msg = "";

switch($pg){
    case "del": {
        if(count($params) > 2)
        {
            $fl = $params[3];
            // verify no change in directory (security)
            if(basename($fl) != $fl) {
                soa_error("File Name Invalid[Directory Set]: ".$fl);
            }
            $fl = basename($fl);
            if(!file_exists($fl)) {
                soa_error("File Name Invalid[Not Found]: ".$fl);
            }
            if(!unlink($fl))
                header("location: ".SOA_ROOT.params(array("look", "delf")));
            else
                header("location: ".SOA_ROOT.params(array("look", "dels")));
            die();
        }
        break;
    }
    case "delf": {
        $msg = "File Deletion Failed.";
        break;
    }
    case "dels": {
        $msg = "File Deleted Successfully";
        break;
    }
    case "allow": {
        if(count($params) > 2)
        {
            $fl = $params[3];
            // verify no change in directory (security)
            if(basename($fl) != $fl) {
                soa_error("File Name Invalid[Directory Set]: ".$fl);
            }
            $fl = basename($fl);
            if(!file_exists($fl)) {
                soa_error("File Name Invalid[Not Found]: ".$fl);
            }
            chdir("../");
            if(!unzip("themes/".$fl)){
                    chdir("themes");
                    $msg = "Theme Installation failed!";
                    break;
            }
            chdir("themes");
            unlink($fl);
            if($msg == ""){
                header("location: ".SOA_ROOT.params(array("look", "allows")));
                die();
            }
        }
        break;
    }
    case "allows": {
        $msg = "Theme Installation Successful.";
    }
        
    default: {
        
    }
}

if(isset($_POST['submit'])){ // setting has been changed
    // process data
    if(isset($_POST['allowCustom']))
        $acust = 1;
    else
        $acust = 0;
    
    $thm = $_POST['theme'];

    // update database
    updateSiteDBParam('tchoice', $acust, "-1");
    updateSiteDBParam('theme', $thm, "-1");
    header("location: ".$thispg); // refresh it
    die();
}

// get database info
$acust = getSiteDBParam("tchoice", -1, "1");
getSiteDBParam("theme", -1, "theme_main"); // initialize it if all else

// draw page
writeheader("Appearance - SiteOfAwesome Administration", "admin.css");

// menu entires
$a = array();
array_push($a, new AdminNavEntry("Main Page", ""));
array_push($a, new AdminNavEntry("Accounts", SOA_ROOT.params(array("accounts"))));
array_push($a, new AdminNavEntry("Appearance", SOA_ROOT.params(array("look")), true));

admin_writeheader("Appearance - SiteOfAwesome Administration", $a);

// main content::
echo
'           <form method="post" action="'.$thispg.'">'.NL.
'               <div class="sdivsion">Theme Information</div><br />'.NL.
'               <p>To Install and Use Themes:</p>'.NL.
'               <ol>'.NL.
'                   <li>Place the zip file of the theme into the <em>./themes</em> folder.</li>'.NL.
'                   <li>Verify you wish to install the theme below. The zip file will extract'.NL.
'                       and will be moved to the <em>./themes/installed</em> folder.</li>'.NL.
'                   <li>Select the theme to use below.</li>'.NL.
'               </ol><br />'.NL;
if(!defined("ZIP_ENABLED") || ZIP_ENABLED == false)
{
    echo
'               <p><span class="error">Please note that ZIP is not enabled (see config.php and your server configuration).'.NL.
'                   This means that extraction will not work, so you must manually extract themes to the'.NL.
'                   root of the installation ('.SOA_ROOT.').</span></p><br />'.NL;
}
echo
'               <div class="sdivsion">Theme Installation</div><br />'.NL;

if($msg != ""){
    echo
'               <p><span class="error">'.$msg.'</span></p>'.NL;
}
// now list themes to be installed
$themelist = glob("*.zip");
if(count($themelist) > 0)
{
    echo
'               <p>The following themes have been detected in the <em>./themes</em> folder, and'.NL.
'                   are ready to be installed with approval.</p>'.NL.
'               <div class="innerBorder"><table width="75%">'.NL.
'                   <tr><th width="75%">File</th><th width="25%">Action</th></tr>'.NL;
// TODO: Populate avalible themes table
    foreach ($themelist as $value) {
        echo 
'                   <tr>'.NL.
'                       <td>'.$value.'</td>'.NL.
'                       <td align="center">'.NL.
'                           <a href="'.SOA_ROOT.params(array("look", "allow", $value)).'"><img src="'.SOA_ROOT.'/img/'.SOA_THEME.'/allow.png" alt="Install" width="25" height="25" border="0" /></a>'.NL.
'                           &nbsp;&nbsp;&nbsp;'.NL.
'                           <a href="'.SOA_ROOT.params(array("look", "del", $value)).'"><img src="'.SOA_ROOT.'/img/'.SOA_THEME.'/del.png" alt="Delete" width="25" height="25" border="0" /></a>'.NL.
'                       </td>'.NL.
'                   </tr>'.NL;
    }
    echo
'               </table></div><br />'.NL;
}
else
{
    echo
'               <p>No themes were found that are ready to be installed. To install a theme place the .zip'.NL.
'                   archive in the themes folder.</p><br />'.NL;
}
echo
'               <div class="sdivsion">Theme Selection</div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td>Select the theme you wish to use: </td>'.NL.
'                       <td>'.NL.
'                           <select name="theme">'.NL;

// todo: populate theme list

chdir("../css");
$themelist = glob("*");
foreach ($themelist as $key => $value) {
    if(!is_dir($value)){
        unset($themelist[$key]);
        continue;
    }
    $sel = "";
    if(SOA_THEME == $value)
        $sel = " selected";
    echo 
'                               <option value="'.$value.'"'.$sel.'>'.$value.'</option>'.NL;
}

$tmp = $acust ? " checked" : "";

echo
'                           </select>'.NL.
'                       </td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td>Allow users to select installed themes: </td>'.NL.
'                       <td><input type="checkbox" name="allowCustom"'.$tmp.' /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <input id="save" type="submit" name="submit" value="Save Changes" />'.NL.        
'           </form>'.NL;

admin_writefooter();
writefooter();

?>
