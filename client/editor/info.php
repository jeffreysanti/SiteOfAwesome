<?php

/*
 * Baisc contact/profile information
 */

if(!defined("PG_CL"))
    soa_error("editor/info.php page accessed without permission");


if(isset($_POST['submit']))
{
    // add "about" info
    if(isset($_POST['iname'])&&isset($_POST['ieditor'])&&strlen($_POST['iname'])>0&&strlen($_POST['ieditor'])>0){
        $pub = isset($_POST['ipublic']) ? 1 : -1;
        try{
            $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_client_info (uid, ft, field, info, public) VALUES (?,?,?,?,?)');
            $q->execute(array($userrow['id'], -1, $_POST['iname'], $_POST['ieditor'], $pub));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }
    // add contact info
    if(isset($_POST['cname'])&&isset($_POST['ceditor'])&&strlen($_POST['cname'])>0&&strlen($_POST['ceditor'])>0){
        $type = $_POST['ctype'];
        $str = $_POST['cname'];
        $pub = isset($_POST['cpublic']) ? 1 : -1;
        try{
            $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_client_info (uid, ft, field, info, public) VALUES (?,?,?,?,?)');
            $q->execute(array($userrow['id'], $type, $str, $_POST['ceditor'], $pub));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }
    // profile image
    updateSiteDBParam("profimge", isset($_POST['noimg']), $userrow['id']);
    if(isset($_POST['img']))
        updateSiteDBParam("profimg", $_POST['img'], $userrow['id']);
    
    // remove any "infos" and "contacts" -> merge them to remove abstraction
    if(!isset($_POST['rmi'])) $_POST['rmi'] = array();
    if(!isset($_POST['rmc'])) $_POST['rmc'] = array();
    $a = array_merge($_POST['rmi'], $_POST['rmc']);
    foreach ($a as $value) {
        try{
            $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_client_info WHERE uid=? AND id=? LIMIT 1');
            $q->execute(array($userrow['id'], $value));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }
}

$link = '        <script src="'.SOA_ROOT.'/ckeditor/ckeditor.js"></script>'.NL.
        '        <script>'.NL.
        '           function openKCFinder_singleFile() {'.NL.
        '               window.KCFinder = {};'.NL.
        '               window.KCFinder.callBack = function(url) {'.NL.
        '                   // Actions with url parameter here'.NL.
        '                   window.KCFinder = null;'.NL.
        '                   var str=url.replace("'.SOA_ROOT.'/kcfinder/upload/'.$userrow['id']."/".'","");'.NL.
        '                   document.getElementById("img_box").value=str;'.NL.
        '               };'.NL.
        '               window.open(\''.SOA_ROOT.'/kcfinder/browse.php\', \'kcfinder_single\');'.NL.
        '           }'.NL.
        '       </script>'.NL;
writeheader(SOAL_EDITORTITLE, "main.css", $link);
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_YOUCONTACTEDITOR, SOA_ROOT.params(array("editor", "info"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);


// load settings
$imgenabled = getSiteDBParam("profimge", $userrow['id'], -1);
$imgstr = getSiteDBParam("profimg", $userrow['id'], "");

$imgchk = $imgenabled == 1 ? ' checked="1"' : "";

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

try{
     $r = $dbc->query('SELECT * FROM '.DB_PRE.'_client_info WHERE uid="'.$userrow['id'].'" AND ft=-1')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach ($r as $value) {
    $pub = $value['public'] == 1 ? "Yes" : "No";
    echo
'                   <tr>'.NL.
'                       <td>'.$value['field'].'</td>'.NL.
'                       <td>'.$value['info'].'</td>'.NL.
'                       <td align="center">'.$pub.'</td>'.NL.
'                       <td align="center"><input type="checkbox" value="'.$value['id'].'" name="rmi[]" /></td>'.NL.
'                   </tr>'.NL;
}


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
try{
     $r = $dbc->query('SELECT * FROM '.DB_PRE.'_client_info WHERE uid="'.$userrow['id'].'" AND ft>-1')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach ($r as $value) {
    $pub = $value['public'] == 1 ? "Yes" : "No";
    $type = $ctypes[$value['ft']];
    echo
'                   <tr>'.NL.
'                       <td>'.$type.'</td>'.NL.
'                       <td>'.$value['field'].'</td>'.NL.
'                       <td>'.$value['info'].'</td>'.NL.
'                       <td align="center">'.$pub.'</td>'.NL.
'                       <td align="center"><input type="checkbox" value="'.$value['id'].'" name="rmc[]" /></td>'.NL.
'                   </tr>'.NL;
}

echo
'               </table></div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_CONTACT_SECTION.': </div></td>'.NL.
'                       <td><select name="ctype">'.NL;
foreach ($ctypes as $key => $value) {
    echo
'                               <option value="'.$key.'">'.$value.'</option>'.NL;
}
echo
'                       </select></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_FIELD.': </div></td>'.NL.
'                       <td><input type="text" name="cname" /></td>'.NL.
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
'                           <td><input type="checkbox" name="noimg"'.$imgchk.' /></td>'.NL.
'                       </tr>'.NL.
'                       <tr>'.NL.
'                           <td><div class="content_field">'.SOAL_IMAGE.': </div></td>'.NL.
'                           <td><input type="text" name="img" id="img_box" value="'.$imgstr.'" />'.
'                               <a href="#" onclick="openKCFinder_singleFile()">['.SOAL_FILEBROWSER.']</a></td>'.NL.
'                       </tr>'.NL.
'                   </table>'.NL.
'                   </td>'.NL.
'                   <td valign="top">'.NL;
if(SOA_REWRITE)
    define ("SOA_ULOAD_DIR", "kcfinder/");
else
    define("SOA_THEMES_DIR", "../../kcfinder/");
if(!chdir(SOA_ULOAD_DIR))
    soa_error ("chdir failure: ".SOA_ULOAD_DIR. " from ".getcwd());

if($imgenabled == 1 && file_exists(("upload/".$userrow['id']."/".$imgstr))){
    echo
'                       <img src="'.SOA_ROOT.'/kcfinder/upload/'.$userrow['id'].'/'.$imgstr.'" alt="'.SOAL_YOUR_IMAGE.'" width="200" />'.NL;
}elseif ($imgenabled == 1) {
    echo
'                       <span class="notice">Error: Image Not Found</span>'.NL;
}
echo
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
