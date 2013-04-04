<?php

/*
 * Client Article Editor
 */


if(!defined("PG_CL"))
    soa_error("editor/art_edt.php page accessed without permission");

// verify permission & article existance
$artid = $params[3];
try
{
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_art WHERE uid=? AND id=?');
    $q->execute(array($userrow['id'],$artid));
    if($q->rowCount() < 1)
        die();
    $arow = $q->fetchAll()[0];
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

if(isset($_POST['submit']))
{
    /*if(isset($_POST['aname']) && strlen($_POST['aname']) > 0)
    {
        try
        {
            $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_art (uid,name,pub) VALUES (?,?,?)');
            $q->execute(array($userrow['id'],$_POST['aname'], -1));
            
            // redirect to editor
            $aid = $dbc->query('SELECT id FROM '.DB_PRE.'_art WHERE uid='.$userrow['id'].' ORDER BY id DESC LIMIT 1')->fetchAll()[0][0];
            header("location: ".SOA_ROOT.params(array("editor", "art", $aid)));
            die();
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }*/
}

writeheader(SOAL_EDITORTITLE, "main.css", '        <script src="'.SOA_ROOT.'/ckeditor/ckeditor.js"></script>'.NL);
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_ARTICLEEDITOR.ARROW, SOA_ROOT.params(array("editor", "art"))));
array_push($a, new menuItem($arow['name'], SOA_ROOT.params(array("editor", "art",$arow['id']))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);
// TODO: Tags
$tags = "";
$pbl = $arow['pub'] == 1 ? ' checked="1"' : "";
echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'art', $arow['id'])).'">'.NL.
'               <span class="content_h1">'.$arow['name'].'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_GENERAL.'</span><br/><br />'.NL.
'               <table>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_ARTICLE_NAME.': </div></td>'.NL.
'                   <td><input type="text" name="aname" value="'.$arow['name'].'" /></td>'.NL.
'               </tr>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_PUBLIC.': </div></td>'.NL.
'                   <td><input type="checkbox" name="pbl"'.$pbl.' /></td>'.NL.
'               </tr>'.NL.
'               <tr>'.NL.
'                   <td valign="top"><div class="content_field">'.SOAL_TAGS_SBC.':</div></td>'.NL.
'                   <td><textarea name="tags" rows="8" cols="50">'.$tags.'</textarea></td>'.NL.
'               </table><br />'.
'               <span class="content_h2">'.SOAL_FULL_TEXT.'</span><br/><br />'.NL.
'               <textarea name="fulltext"></textarea><br />'.NL.
'               <span class="content_h2">'.SOAL_PERMISSIONS.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_GROUP.'</th>'.NL.
'                       <th>'.SOAL_ACCESS.'</th>'.NL.
'                   </tr>'.NL;

// List all group access rights
/*$r = $aid = $dbc->query('SELECT * FROM '.DB_PRE.'_art WHERE uid='.$userrow['id'].' ORDER BY name DESC')->fetchAll();
foreach($r as $value){
    // TODO: Get tags implemented
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></td>'.NL.
'                       <td></td>'.NL.
'                   <tr>'.NL;
}*/
echo
'               </table></div><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_CLIENT.'</th>'.NL.
'                       <th>'.SOAL_GROUP.'</th>'.NL.
'                       <th>'.SOAL_BLOCK.'</th>'.NL.
'                       <th>'.SOAL_ALLOW.'</th>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <th><a href="#">'.SOAL_INHERETED.' ['.SOAL_VISIBLE.']</a></th>'.NL.
'                       <th>'.SOAL_INHER_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="na">'.NL.
'                       <th><a href="#">'.SOAL_INHERETED.' ['.SOAL_INVISIBLE.']</a></th>'.NL.
'                       <th>'.SOAL_IMPINV.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="added">'.NL.
'                       <th><a href="#">'.SOAL_ADDED.'</a></th>'.NL.
'                       <th>'.SOAL_ADDED_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" checked="1" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="blocked">'.NL.
'                       <th><a href="#">'.SOAL_BLOCKED.'</a></th>'.NL.
'                       <th>'.SOAL_BLOCKED_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" checked="1" /></th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="divide"><th></th><th></th><th></th><th></th></tr>'.NL;
 
// TODO List clients&access rights
/*$r = $aid = $dbc->query('SELECT * FROM '.DB_PRE.'_art WHERE uid='.$userrow['id'].' ORDER BY name DESC')->fetchAll();
foreach($r as $value){
    // TODO: Get tags implemented
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></td>'.NL.
'                       <td></td>'.NL.
'                   <tr>'.NL;
}*/

echo
'               </table></div><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
echo
"   <script>".NL.
"       CKEDITOR.replace( 'fulltext' );".NL.
"   </script>";
writefooter();
?>
