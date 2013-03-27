<?php
/*
 * Client appearance editor
 */

if(!defined("PG_CL"))
    soa_error("editor/look.php page accessed without permission");

if(isset($_POST['submit']))
{
    if(isset($_POST['title'])){
        updateSiteDBParam("head1", $_POST['title'], $userrow['id']);
    }
    if(isset($_POST['subtitle'])){
        updateSiteDBParam("head2", $_POST['subtitle'], $userrow['id']);
    }
    if(isset($_POST['titlebar'])){
        updateSiteDBParam("titlebar", $_POST['titlebar'], $userrow['id']);
    }
    if(isset($_POST['theme'])){
        updateSiteDBParam("theme", $_POST['theme'], $userrow['id']);
    }
}

// retrieve information to populate data with
$title = getSiteDBParam("head1", $userrow['id'], $userrow['username']);
$subtitle = getSiteDBParam("head2", $userrow['id'], SOAL_IS_AWESOME);
$titlebar = getSiteDBParam("titlebar", $userrow['id'], SOAL_SOA);
$theme = getSiteDBParam("theme", $userrow['id'], -1);


writeheader(SOAL_EDITORTITLE, "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_LOOKEDITOR, SOA_ROOT.params(array("editor", "look"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'look')).'">'.NL.
'               <span class="content_h1">'.SOAL_LOOKEDITOR.'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_HEADING.'</span><br/><br />'.NL.
'               <table>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_MAIN_TITLE.': </div></td>'.NL.
'                   <td><input type="text" name="title" value="'.$title.'" /></td>'.NL.
'               </tr>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_SUB_TITLE.': </div></td>'.NL.
'                   <td><input type="text" name="subtitle" value="'.$subtitle.'" /></td>'.NL.
'               </tr>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_TITLEBAR.': </div></td>'.NL.
'                   <td><input type="text" name="titlebar" value="'.$titlebar.'" /></td>'.NL.
'               </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_THEME.'</span><br/><br />'.NL.
'               <select name="theme">'.NL.
'                   <option value="-1"'.($theme==-1?" selected":"").'> -- '.SOAL_DEF_THEME.' -- </option>'.NL;

// list installed themes
chdir("css");
$themelist = glob("*");
foreach ($themelist as $key => $value) {
    if(!is_dir($value)){
        unset($themelist[$key]);
        continue;
    }
    $sel = "";
    if($theme == $value)
        $sel = " selected";
    echo 
'                               <option value="'.$value.'"'.$sel.'>'.$value.'</option>'.NL;
}

echo
'               </select><br /><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
writefooter();

?>
