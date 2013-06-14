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
    // basic parameters
    if(isset($_POST['aname'])){
        $arow['name'] = $_POST['aname'];
    }
    if(isset($_POST['pbl'])){
        $arow['pub'] = 1;
    }else{
        $arow['pub'] = -1;
    }
    if(isset($_POST['fulltext'])){
        $arow['text'] = $_POST['fulltext'];
    }
    try
    {
        $q = $dbc->prepare('UPDATE '.DB_PRE.'_art SET name=?, pub=?, text=? WHERE id=? LIMIT 1');
        $q->execute(array($arow['name'], $arow['pub'], $arow['text'], $artid));
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    
    // tags
    if(isset($_POST['tags'])){
        // prepare queries
        try{
            $qTagFind = $dbc->prepare('SELECT id FROM '.DB_PRE.'_tags WHERE uid=? AND text=?');
            $qTagIns = $dbc->prepare('INSERT INTO '.DB_PRE.'_tags (uid,text) VALUES (?,?)');
            
            $qTCIns = $dbc->prepare('INSERT INTO '.DB_PRE.'_tagcon (tid,aid) VALUES (?,?)');
            
            // now remove all current tags
            $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_tagcon WHERE aid=?');
            $q->execute(array($artid));
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
        
        $_POST['tags'] = str_replace("\n", ",", str_replace("\r", ",", $_POST['tags'])); // change new lines to ','
        $tgs = explode(",", $_POST['tags']);
        foreach ($tgs as $value) {
            $value = trim($value);
            if($value == "")
                continue;
            $tid = -1;
            try {
                $qTagFind->execute(array($userrow['id'],$value));
                
                if($qTagFind->rowCount() > 0){
                    $row = $qTagFind->fetchAll()[0];
                    $tid = $row[0];
                }else{ 
                    $qTagIns->execute(array($userrow['id'],$value));
                    
                    $qTagFind->execute(array($userrow['id'],$value));
                    $row = $qTagFind->fetchAll()[0];
                    $tid = $row[0];
                }
                $qTCIns->execute(array($tid, $artid)); // inserts tag connection
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
    }
    // group permission
    $acc_groups = array();
    if(!isset($_POST['grp'])) $_POST['grp'] = array();
    try{
        $qAddCon = $dbc->prepare('INSERT INTO '.DB_PRE.'_acon (aid, type, id) VALUES (?,?,?)');
        $qVerifyOwn = $dbc->prepare('SELECT * FROM '.DB_PRE.'_groups WHERE id=? AND owner=?');

        // now remove all current group connections
        $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_acon WHERE aid=?');
        $q->execute(array($artid));
        
        // add connections
        foreach ($_POST['grp'] as $value) {
            $qVerifyOwn->execute(array($value, $userrow['id']));
            if($qVerifyOwn->rowCount() < 1) // make sure group is owned by user
                continue;
            $qVerifyOwn->closeCursor();
            $qAddCon->execute(array($artid, 0, $value));
            array_push($acc_groups, $value);
        }
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    
    // now the difficult subclient permissions :(
    if(!isset($_POST['blk'])) $_POST['blk'] = array();
    if(!isset($_POST['alw'])) $_POST['alw'] = array();
    $acc_clients = array();
    $acc_blclients = array();
    try{
        // remove all old connections
        $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_acon WHERE aid=? AND type=?');
        $q->execute(array($artid, 1)); // client allow
        $q->execute(array($artid, 2)); // client block
        
        $qListGroups = $dbc->prepare('SELECT '.DB_PRE.'_groups.name, '.DB_PRE.'_grp_cl.gid FROM '.DB_PRE.'_grp_cl JOIN '.
                DB_PRE.'_groups ON '.DB_PRE.'_grp_cl.gid = '.DB_PRE.'_groups.id WHERE '.DB_PRE.'_grp_cl.uid=?');
        $qAddACon = $dbc->prepare('INSERT INTO '.DB_PRE.'_acon (aid, type, id) VALUES (?,?,?)');


        // get list of subclients
        $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE owner=?');
        $q->execute(array($userrow['id']));
        $crow = $q->fetchAll();
        var_dump($_POST['alw']);
        foreach ($crow as $value) {
            $cl_groups = array();

            // get group list
            $qListGroups->execute(array($value['id']));
            $tmp = $qListGroups->fetchAll();
            foreach ($tmp as $value2)
                array_push ($cl_groups, $value2['gid']);
            
            if(count(array_intersect($acc_groups, $cl_groups)) == 0 && in_array($value['id'], $_POST['alw'])) // allow it
            {
                $qAddACon->execute(array($artid, 1, $value['id']));
            }
            elseif(count(array_intersect($acc_groups, $cl_groups)) > 0 && in_array($value['id'], $_POST['blk'])) // block it
            {
                $qAddACon->execute(array($artid, 2, $value['id']));
            }
        }    
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
}

writeheader(SOAL_EDITORTITLE, "main.css", '        <script src="'.SOA_ROOT.'/ckeditor/ckeditor.js"></script>'.NL);
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_ARTICLEEDITOR.ARROW, SOA_ROOT.params(array("editor", "art"))));
array_push($a, new menuItem($arow['name'], SOA_ROOT.params(array("editor", "art",$arow['id']))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

//tags
$tags = "";
try{
    $q = $dbc->prepare('SELECT '.DB_PRE.'_tags.text FROM '.DB_PRE.'_tags JOIN '.DB_PRE.'_tagcon ON '.
            DB_PRE.'_tags.id = '.DB_PRE.'_tagcon.tid WHERE '.DB_PRE.'_tagcon.aid=?');
    $q->execute(array($artid));
    $trow = $q->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach ($trow as $key => $value) {
    if($key != 0)
        $tags = $tags . ", ";
    $tags = $tags . $value[0];
}

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
'               <textarea name="fulltext">'.$arow['text'].'</textarea><br />'.NL.
'               <span class="content_h2">'.SOAL_PERMISSIONS.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_GROUP.'</th>'.NL.
'                       <th>'.SOAL_ACCESS.'</th>'.NL.
'                   </tr>'.NL;

// List all group access rights
$acc_groups = array();
try{
$q = $dbc->prepare('SELECT '.DB_PRE.'_groups.name, '.DB_PRE.'_groups.id, '.DB_PRE.'_acon.id FROM '.DB_PRE.'_groups '.
        'LEFT JOIN '.DB_PRE.'_acon ON '.DB_PRE.'_groups.id='.DB_PRE.'_acon.id AND '.DB_PRE.'_acon.aid=? AND '.
        DB_PRE.'_acon.type=? WHERE '.DB_PRE.'_groups.owner=? ');
$q->execute(array($artid, 0, $userrow['id']));
$grow = $q->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

foreach($grow as $value){
    $chked = $value[1] == $value[2] ? ' checked="1"' : "";
    if($value[1] == $value[2])
        array_push ($acc_groups, $value[1]);
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','cg','g',$value[1])).'">'.$value[0].'</a></td>'.NL.
'                       <td><input type="checkbox" name="grp[]"'.$chked.' value="'.$value[1].'" /></td>'.NL.
'                   <tr>'.NL;
}
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
 
// List subclients&access rights
$acc_clients = array();
$acc_blclients = array();
try{
    $qListGroups = $dbc->prepare('SELECT '.DB_PRE.'_groups.name, '.DB_PRE.'_grp_cl.gid FROM '.DB_PRE.'_grp_cl JOIN '.
            DB_PRE.'_groups ON '.DB_PRE.'_grp_cl.gid = '.DB_PRE.'_groups.id WHERE '.DB_PRE.'_grp_cl.uid=?');
    $qListACon = $dbc->prepare('SELECT id FROM '.DB_PRE.'_acon WHERE aid=? AND type=?');
    
    // get all allowed subclient ids
    $qListACon->execute(array($artid, 1));
    $tmp = $qListACon->fetchAll();
    foreach ($tmp as $value)
        array_push ($acc_clients, $value[0]);
    
    // get all blocked subclients
    $qListACon->execute(array($artid, 2));
    $tmp = $qListACon->fetchAll();
    foreach ($tmp as $value)
        array_push ($acc_blclients, $value[0]);

    // get list of subclients
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE owner=?');
    $q->execute(array($userrow['id']));
    $crow = $q->fetchAll();
    foreach ($crow as $value) {
        $cl_groups = array();
        $cl_groups_names = array();
        
        // get group list
        $qListGroups->execute(array($value['id']));
        $tmp = $qListGroups->fetchAll();
        foreach ($tmp as $value2){
            array_push ($cl_groups, $value2['gid']);
            array_push ($cl_groups_names, $value2['name']);
        }
        
        if(in_array($value['id'], $acc_blclients)) // client explicity blocked
        {
            echo 
'                   <tr class="blocked">'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['name'].' ['.$value['username'].']</a></th>'.NL.
'                       <th>'.implode(", ", $cl_groups_names).'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" checked="1" /></th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="alw[]" type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL;

        }
        elseif(in_array($value['id'], $acc_clients)) // client explicitly allowed
        {
            echo 
'                   <tr class="added">'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['name'].' ['.$value['username'].']</a></th>'.NL.
'                       <th>'.implode(", ", $cl_groups_names).'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" disabled="disabled" /></th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="alw[]" type="checkbox" checked="1" /></th>'.NL.
'                   </tr>'.NL;
        }
        elseif(count(array_intersect($acc_groups, $cl_groups)) > 0) // group has access
        {
            echo 
'                   <tr>'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['name'].' ['.$value['username'].']</a></th>'.NL.
'                       <th>'.implode(", ", $cl_groups_names).'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" /></th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="alw[]" type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL;
        }
        else
        {
            echo 
'                   <tr class="na">'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['name'].' ['.$value['username'].']</a></th>'.NL.
'                       <th>'.implode(", ", $cl_groups_names).'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" disabled="disabled" /></th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="alw[]" type="checkbox" /></th>'.NL.
'                   </tr>'.NL;
        }
        
    }
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

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
