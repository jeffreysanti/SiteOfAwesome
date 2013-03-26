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

    // remove client from group
    /*if(isset($_POST['ugc'])){
        foreach ($_POST['ugc'] as $value) {
            try{
                $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_grp_cl WHERE uid = ? AND gid = ? LIMIT 1');
                $q->execute(array($value, $gid));
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        $msg = $msg . SOAL_MSG_CLIENTS_UGROUP . "<br />".NL;
    }*/
    // remove clients
    /*if(isset($_POST['rmc'])){
        foreach ($_POST['rmc'] as $value) {
             try{
                $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_users WHERE id = ? AND owner = ? LIMIT 1');
                $q->execute(array($value, $userrow['id']));
                
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        $msg = $msg . SOAL_MSG_CLIENT_REMOVED . "<br />".NL;
    }*/
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
try{
    $r = $dbc->query('SELECT '.DB_PRE.'_groups.id, '.DB_PRE.'_groups.name, '.DB_PRE.'_grp_cl.gid, '.DB_PRE.'_grp_cl.uid FROM '.
            DB_PRE.'_groups LEFT JOIN '.DB_PRE.'_grp_cl ON '.DB_PRE.'_groups.id='.DB_PRE.'_grp_cl.gid AND '.DB_PRE.'_grp_cl.uid='.$cid.' WHERE '.
            DB_PRE.'_groups.owner = '.$userrow['id'])->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    $c = (is_null($value['gid'])) || ($value['uid'] != $cid) ? "" : " checked";
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
 
// TODO: List all resources client has access to
/*try{
    $r = $dbc->query('SELECT * FROM '.DB_PRE.'_users WHERE owner='.$userrow['id'].' ORDER BY username')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    // get group list of subclient
    //$r2 = $dbc->query('SELECT * FROM '.DB_PRE.'_users WHERE owner='.$userrow['id'].' ORDER BY username')->fetchAll();
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['username'].'</a></td>'.NL.
'                       <td>...</td>'.NL.
'                       <td align="center"><input type="checkbox" value="'.$value['id'].'" name="rmc[]" /></td>'.NL.
'                   <tr>'.NL;
    
}*/

echo
'               </table></div><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor','cg')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form>';

client_footer();
writefooter();
?>
