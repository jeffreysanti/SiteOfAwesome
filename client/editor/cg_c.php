<?php

/*
 * Group Editor
 */

if(!defined("PG_CL"))
    soa_error("editor/cg_c.php page accessed without permission");
if(count($params) > 3){
    $cid = $params[4]; // group id
}
else{
    header("location:".SOA_ROOT.params(array("editor", "cg")));
    die();
}

// retrieve information to populate data with
try{
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE id = ? AND owner = ?');
    $q->execute(array($cid, $userrow['id']));
    if($q->rowCount() < 1){
        header("location:".SOA_ROOT.params(array("editor", "cg"))); // permission missing
        die();
    }
    $crow = $q->fetchAll()[0];
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

$msg = "";
if(isset($_POST['submit']))
{
    if(isset($_POST['cname']) && $_POST['cname'] != $crow['name']){
        try{
            $q = $dbc->prepare('UPDATE '.DB_PRE.'_users SET name = ? WHERE id=?');
            $q->execute(array($_POST['cname'], $cid));
            $crow['name'] = $_POST['cname']; // so data is shown up to date
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }
    if(isset($_POST['cpass']) && strlen($_POST['cpass']) > 0){
        if(strlen($_POST['cpass']) < 5){
            $msg = $msg . SOAL_MSG_ERRPSW . "<br />" . NL;
        }
        else{
            $pass = md5($_POST['cpass']);
            try{
                $q = $dbc->prepare('UPDATE '.DB_PRE.'_users SET password = ? WHERE id=?');
                $q->execute(array($pass, $cid));
                $crow['password'] = $pass; // so data is shown up to date
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
            $msg = $msg . SOAL_MSG_CLIPSWCHD . "<br />" . NL;
        }
    }

    // now update client membership
    // first get list of groups member of
    try{
        $r = $dbc->query('SELECT '.DB_PRE.'_groups.id, '.DB_PRE.'_grp_cl.gid, '.DB_PRE.'_grp_cl.uid FROM '.
            DB_PRE.'_groups LEFT JOIN '.DB_PRE.'_grp_cl ON '.DB_PRE.'_groups.id = '.DB_PRE.'_grp_cl.gid AND '.DB_PRE.'_grp_cl.uid='.$cid.' WHERE '.
            DB_PRE.'_groups.owner = '.$userrow['id'])->fetchAll();
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    
    if(!isset($_POST['grps'])) $_POST['grps'] = array();
    foreach ($r as $value) {
        $i = $value['id'];
        $ingroup = !is_null($value['gid']) && $value['uid'] == $cid;
        if($ingroup && !in_array($i, $_POST['grps'])) // remove it
        {
            try{
                $dbc->query('DELETE FROM '.DB_PRE.'_grp_cl WHERE gid='.$i.' AND uid='.$cid.' LIMIT 1');
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        elseif (!$ingroup && in_array($i, $_POST['grps'])){ // add it
            try{
                $dbc->query('INSERT INTO '.DB_PRE.'_grp_cl (gid, uid) VALUES ('.$i.', '.$cid.')');
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
    }
    
    $acc_groups = array(); // TODO: Clean this up
    try{
        $r = $dbc->query('SELECT '.DB_PRE.'_groups.id, '.DB_PRE.'_groups.name, '.DB_PRE.'_grp_cl.gid, '.DB_PRE.'_grp_cl.uid FROM '.
                DB_PRE.'_groups LEFT JOIN '.DB_PRE.'_grp_cl ON '.DB_PRE.'_groups.id='.DB_PRE.'_grp_cl.gid AND '.DB_PRE.'_grp_cl.uid='.$cid.' WHERE '.
                DB_PRE.'_groups.owner = '.$userrow['id'])->fetchAll();
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    foreach($r as $value){
        $c = (is_null($value['gid'])) || ($value['uid'] != $cid) ? "" : " checked";
        if($c == " checked"){
            array_push($acc_groups, $value['id']);
        }
    }

    // access changes
    $acc_art = array();
    $acc_blart = array();
    if(!isset($_POST['blk'])) $_POST['blk'] = array();
    try{
        $qListACon = $dbc->prepare('SELECT aid FROM '.DB_PRE.'_acon WHERE id=? AND type=?');
        $qListAConInvserse = $dbc->prepare('SELECT id FROM '.DB_PRE.'_acon WHERE aid=? AND type=?');
        $qDelAcon = $dbc->prepare('DELETE FROM '.DB_PRE.'_acon WHERE aid=? AND type=? AND id=?');
        $qAddAcon = $dbc->prepare('INSERT INTO '.DB_PRE.'_acon (aid, type, id) VALUES (?,?,?)');
        
        // get all allowed articles
        $qListACon->execute(array($cid, 1));
        $tmp = $qListACon->fetchAll();
        foreach ($tmp as $value)
            array_push ($acc_art, $value[0]);

        // get all blocked articles
        $qListACon->execute(array($cid, 2));
        $tmp = $qListACon->fetchAll();
        foreach ($tmp as $value)
            array_push ($acc_blart, $value[0]); 

        // get list of articles
        $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_art WHERE uid=?');
        $q->execute(array($userrow['id']));
        $arow = $q->fetchAll();
        foreach ($arow as $value) {
            $a_groups = array();

            // get allowed group list
            $qListAConInvserse->execute(array($value['id'], 0));
            $tmp = $qListAConInvserse->fetchAll();
            foreach ($tmp as $value2){
                array_push ($a_groups, $value2[0]);
            }

            if(in_array($value['id'], $acc_blart)) // client explicity blocked
            {
                if(!in_array($value['id'], $_POST['blk'])){ // unblocked
                    $qDelAcon->execute(array($value['id'], 2, $cid));
                }
            }
            elseif(in_array($value['id'], $acc_art)) // client explicity added
            {
                if(in_array($value['id'], $_POST['blk'])){ // un-added
                    $qDelAcon->execute(array($value['id'], 1, $cid));
                }
            }
            elseif(count(array_intersect($acc_groups, $a_groups)) > 0) // client inherently there
            {
                if(in_array($value['id'], $_POST['blk'])){ // block it
                    $qAddAcon->execute(array($value['id'], 2, $cid));
                }
            }
        }
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
}

writeheader(SOAL_EDITORTITLE, "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_CGEDITOR.ARROW, SOA_ROOT.params(array("editor", "cg"))));
array_push($a, new menuItem($crow['name'].' ('.$crow['username'].") [".SOAL_CLIENT.']', SOA_ROOT.params(array("editor", "cg", "c", $cid))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

if($msg != ""){
    echo
'           <span class="notice">'.$msg.'</span>';
}

echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'cg', 'c', $cid)).'">'.NL.
'           <span class="content_h1">'.$crow['name'].' ('.$crow['username'].') '.SOAL_CLIENT.' '.SOAL_CONFIG.'</span><br/><br />'.NL.
'           <span class="content_h2">'.SOAL_GENERAL.'</span><br/><br />'.NL.
'           <table>'.NL.
'           <tr>'.NL.
'               <td><div class="content_field">'.SOAL_CLIENT_NAME.': </div></td>'.NL.
'               <td><input type="text" name="cname" value="'.$crow['name'].'" /></td>'.NL.
'           </tr>'.NL.
'           <tr>'.NL.
'               <td><div class="content_field">'.SOAL_LOGIN.': </div></td>'.NL.
'               <td><input type="text" readonly disabled value="'.$crow['username'].'" /></td>'.NL.
'           </tr>'.NL.
'           <tr>'.NL.
'               <td><div class="content_field">'.SOAL_PASSWORD.': </div></td>'.NL.
'               <td><input type="text" name="cpass" value="" /></td>'.NL.
'           </tr>'.NL.
'           </table><br />'.
'           <span class="content_h2">'.SOAL_GROUP_LIST.'</span><br/><br />'.NL.
'           <div class="innerBorder"><table>'.NL.
'               <tr>'.NL.
'                   <th>'.SOAL_GROUP.'</th>'.NL.
'                   <th>'.SOAL_MEMBER.'</th>'.NL.
'               </tr>'.NL;

// list all all groups & whether client member of
$acc_groups = array();
try{
    $r = $dbc->query('SELECT '.DB_PRE.'_groups.id, '.DB_PRE.'_groups.name, '.DB_PRE.'_grp_cl.gid, '.DB_PRE.'_grp_cl.uid FROM '.
            DB_PRE.'_groups LEFT JOIN '.DB_PRE.'_grp_cl ON '.DB_PRE.'_groups.id='.DB_PRE.'_grp_cl.gid AND '.DB_PRE.'_grp_cl.uid='.$cid.' WHERE '.
            DB_PRE.'_groups.owner = '.$userrow['id'])->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    $c = (is_null($value['gid'])) || ($value['uid'] != $cid) ? "" : " checked";
    if($c == " checked"){
        array_push($acc_groups, $value['id']);
    }
    echo
'               <tr>'.NL.
'                   <td><a href="'.SOA_ROOT.params(array('editor','cg','g',$value['id'])).'">'.$value['name'].'</a></td>'.NL.
'                   <td align="center"><input type="checkbox"'.$c.' value="'.$value['id'].'" name="grps[]" /></td>'.NL.
'               <tr>'.NL;
    
}

echo
'               </table></div><br />'.NL.
'               <span class="content_h2">'.SOAL_RESACCESS.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_RES_NAME.'</th>'.NL.
'                       <th>'.SOAL_TAGS.'</th>'.NL.
'                       <th>'.SOAL_BLOCK.'</th>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <th><a href="#">'.SOAL_INHERETED.'</a></th>'.NL.
'                       <th>'.SOAL_INHER_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="added">'.NL.
'                       <th><a href="#">'.SOAL_ADDED.'</a></th>'.NL.
'                       <th>'.SOAL_ADDED_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="blocked">'.NL.
'                       <th><a href="#">'.SOAL_BLOCKED.'</a></th>'.NL.
'                       <th>'.SOAL_BLOCKED_MSG.'</th>'.NL.
'                       <th align="center"><input type="checkbox" checked disabled="disabled" /></th>'.NL.
'                   </tr>'.NL.
'                   <tr class="divide"><th></th><th></th><th></th></tr>'.NL;
 
// List all resources subclient has access to
$acc_art = array();
$acc_blart = array();
try{
    $qListACon = $dbc->prepare('SELECT aid FROM '.DB_PRE.'_acon WHERE id=? AND type=?');
    $qListAConInvserse = $dbc->prepare('SELECT id FROM '.DB_PRE.'_acon WHERE aid=? AND type=?');
    $qTagConLookup = $dbc->prepare('SELECT tid FROM '.DB_PRE.'_tagcon WHERE aid=?');
    $qTagLookup = $dbc->prepare('SELECT text FROM '.DB_PRE.'_tags WHERE id=?');
    
    // get all allowed articles
    $qListACon->execute(array($cid, 1));
    $tmp = $qListACon->fetchAll();
    foreach ($tmp as $value)
        array_push ($acc_art, $value[0]);
    
    // get all blocked articles
    $qListACon->execute(array($cid, 2));
    $tmp = $qListACon->fetchAll();
    foreach ($tmp as $value)
        array_push ($acc_blart, $value[0]); 
    
    // get list of articles
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_art WHERE uid=?');
    $q->execute(array($userrow['id']));
    $arow = $q->fetchAll();
    foreach ($arow as $value) {
        $a_groups = array();
        
        // get allowed group list
        $qListAConInvserse->execute(array($value['id'], 0));
        $tmp = $qListAConInvserse->fetchAll();
        foreach ($tmp as $value2){
            array_push ($a_groups, $value2[0]);
        }
        
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
        
        if(in_array($value['id'], $acc_blart)) // client explicity blocked
        {
            echo 
'                   <tr class="blocked">'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></th>'.NL.
'                       <th>'.$tags.'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" checked="1" /></th>'.NL.
'                   </tr>'.NL;
        }
        elseif(in_array($value['id'], $acc_art)) // client explicity added
        {
            echo 
'                   <tr class="added">'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></th>'.NL.
'                       <th>'.$tags.'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" /></th>'.NL.
'                   </tr>'.NL;
        }
        elseif(count(array_intersect($acc_groups, $a_groups)) > 0) // client explicity added
        {
            echo 
'                   <tr>'.NL.
'                       <th><a href="'.SOA_ROOT.params(array('editor','art',$value['id'])).'">'.$value['name'].'</a></th>'.NL.
'                       <th>'.$tags.'</th>'.NL.
'                       <th align="center"><input value="'.$value['id'].'" name="blk[]" type="checkbox" /></th>'.NL.
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
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor','cg')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
writefooter();
?>
