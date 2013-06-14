<?php

/*
 * Client Article Manager
 */

if(!defined("PG_CL"))
    soa_error("editor/art.php page accessed without permission");

// check for other pg
if(count($params) > 2){
    require("art_edt.php");
    // TODO: Verify article & Link page
    die();
}

if(isset($_POST['submit']))
{
    if(isset($_POST['aname']) && strlen($_POST['aname']) > 0)
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
    }
}

writeheader(SOAL_EDITORTITLE, "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_ARTICLEEDITOR, SOA_ROOT.params(array("editor", "art"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'art')).'">'.NL.
'               <span class="content_h1">'.SOAL_ARTICLEEDITOR.'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_ARTICLE_LIST.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_ARTICLE_NAME.'</th>'.NL.
'                       <th>'.SOAL_TAGS.'</th>'.NL.
'                   </tr>'.NL;


// List all articles
$r = $aid = $dbc->query('SELECT * FROM '.DB_PRE.'_art WHERE uid='.$userrow['id'].' ORDER BY name DESC')->fetchAll();
$qTagConLookup = $dbc->prepare('SELECT tid FROM '.DB_PRE.'_tagcon WHERE aid=?');
$qTagLookup = $dbc->prepare('SELECT text FROM '.DB_PRE.'_tags WHERE id=?');
foreach($r as $value){
    // get tags
    $tags = "";
    $qTagConLookup->execute(array($value['id']));
    $tcon = $qTagConLookup->fetchAll();
    $i = 0;
    foreach ($tcon as $value2) {
        $qTagLookup->execute(array($value2['tid']));
        if($qTagLookup->rowCount() < 1)
            continue;
        if($i != 0)
            $tags = $tags . ", ";
        $tags = $tags . $qTagLookup->fetchAll()[0][0];
        $i ++;
    }
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></td>'.NL.
'                       <td>'.$tags.'</td>'.NL.
'                   <tr>'.NL;
}

echo
'               </table></div><br />'.NL.
'               <span class="content_h2">'.SOAL_ADD_ARTICLE.'</span><br/><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_ARTICLE_NAME.': </div></td>'.NL.
'                       <td><input type="text" name="aname" /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
writefooter();

?>
