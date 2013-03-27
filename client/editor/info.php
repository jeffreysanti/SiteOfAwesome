<?php

/*
 * Baisc contact/profile information
 */

if(!defined("PG_CL"))
    soa_error("editor/info.php page accessed without permission");

if(isset($_POST['submit']))
{
    /*if(isset($_POST['title'])){
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
    }*/
}

// retrieve information to populate data with
/*$title = getSiteDBParam("head1", $userrow['id'], $userrow['username']);
$subtitle = getSiteDBParam("head2", $userrow['id'], SOAL_IS_AWESOME);
$titlebar = getSiteDBParam("titlebar", $userrow['id'], SOAL_SOA);
$theme = getSiteDBParam("theme", $userrow['id'], -1);*/

$link = '        <script src="'.SOA_ROOT.'/ckeditor/ckeditor.js"></script>';
writeheader(SOAL_EDITORTITLE, "main.css", $link);
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_YOUCONTACTEDITOR, SOA_ROOT.params(array("editor", "info"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

//Field 	Information 	Public 	Remove
echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'info')).'">'.NL.
'               <span class="content_h1">'.SOAL_YOUCONTACTEDITOR.'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_ABOUTYOU.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_FIELD.'</th>'.NL.
'                       <th>'.SOAL_INFO.'</th>'.NL.
'                       <th>'.SOAL_PUBLIC.'</th>'.NL.
'                       <th>'.SOAL_REMOVE.'</th>'.NL.
'                   </tr>'.NL;
// display info


echo
'               </table></div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_FIELD.': </div></td>'.NL.
'                       <td><input type="text" name="iname" /></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_INFO.': </div></td>'.NL.
'                       <td><textarea name="ieditor"></textarea></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_PUBLIC.': </div></td>'.NL.
'                       <td><input type="checkbox" name="ipublic" /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_CONTACT_INFO.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_TYPE.'</th>'.NL.
'                       <th>'.SOAL_NAME.'</th>'.NL.
'                       <th>'.SOAL_INFO.'</th>'.NL.
'                       <th>'.SOAL_PUBLIC.'</th>'.NL.
'                       <th>'.SOAL_REMOVE.'</th>'.NL.
'                   </tr>'.NL;

// contact info

echo
'               </table></div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_CONTACT_SECTION.': </div></td>'.NL.
'                       <td><select name="ctype">'.NL.
'                               <option value="1">'.SOAL_CT_PHONE.'</option>'.NL.
'                               <option value="1">'.SOAL_CT_FAX.'</option>'.NL.
'                               <option value="1">'.SOAL_CT_ADDR.'</option>'.NL.
'                               <option value="1">'.SOAL_CT_EMAIL.'</option>'.NL.
'                               <option value="1">'.SOAL_CT_WEB.'</option>'.NL.
'                               <option value="1">'.SOAL_CT_ONLINE.'</option>'.NL.
'                       </select></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_FIELD.': </div></td>'.NL.
'                       <td><input type="text" name="cfield" /></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_INFO.': </div></td>'.NL.
'                       <td><textarea name="ceditor"></textarea></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_PUBLIC.': </div></td>'.NL.
'                       <td><input type="checkbox" name="cpublic" /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_IMAGE.'</span><br/><br />'.NL.
'               <table><tr>'.NL.
'                   <td valign="top">'.NL.
'                   <table>'.NL.
'                       <tr>'.NL.
'                           <td><div class="content_field">'.SOAL_NO_IMAGE.': </div></td>'.NL.
'                           <td><input type="checkbox" name="noimg" /></td>'.NL.
'                       </tr>'.NL.
'                       <tr>'.NL.
'                           <td><div class="content_field">'.SOAL_IMAGE.': </div></td>'.NL.
'                           <td><input type="text" name="img" value="bg.png" /></td>'.NL.
'                       </tr>'.NL.
'                   </table>'.NL.
'                   </td>'.NL.
'                   <td valign="top">'.NL.
'                       <img src="/SiteOfAwesome/img/theme_main/bg.png" alt="'.SOAL_YOUR_IMAGE.'" width="200" />'.NL.
'                   </td>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
echo
"   <script>".NL.
"       CKEDITOR.replace( 'ieditor' );".NL.
"       CKEDITOR.replace( 'ceditor' );".NL.
"   </script>";
writefooter();
?>
