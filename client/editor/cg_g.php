<?php

/*
 * Group Editor
 */

if(!defined("PG_CL"))
    soa_error("editor/cg_g.php page accessed without permission");
if(count($params) > 3){
    $gid = $params[4]; // group id
}
else{
    header("location:".SOA_ROOT.params(array("editor", "cg")));
    die();
}

// retrieve information to populate data with
try{
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_groups WHERE id = ? AND owner = ?');
    $q->execute(array($gid, $userrow['id']));
    if($q->rowCount() < 1){
        header("location:".SOA_ROOT.params(array("editor", "cg"))); // permission missing
        die();
    }
    $grow = $q->fetchAll()[0];
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

$msg = "";
if(isset($_POST['submit']))
{
    if(isset($_POST['gname']) && $_POST['gname'] != $grow['name']){
        try{
            $q = $dbc->prepare('UPDATE '.DB_PRE.'_groups SET name = ? WHERE id=?');
            $q->execute(array($_POST['gname'], $gid));
            $grow['name'] = $_POST['gname']; // so data is shown up to date
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }

    // remove client from group
    if(isset($_POST['ugc'])){
        foreach ($_POST['ugc'] as $value) {
            try{
                $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_grp_cl WHERE uid = ? AND gid = ? LIMIT 1');
                $q->execute(array($value, $gid));
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        $msg = $msg . SOAL_MSG_CLIENTS_UGROUP . "<br />".NL;
    }
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
array_push($a, new menuItem($grow['name']." [".SOAL_GROUP.']', SOA_ROOT.params(array("editor", "cg", "g", $gid))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

if($msg != ""){
    echo
'           <span class="notice">'.$msg.'</span>';
}

echo
'           <form method="post" action="'.SOA_ROOT.params(array('editor', 'cg', 'g', $gid)).'">'.NL.
'           <span class="content_h1">'.$grow['name'].' '.SOAL_GROUP.' '.SOAL_CONFIG.'</span><br/><br />'.NL.
'           <span class="content_h2">'.SOAL_GENERAL.'</span><br/><br />'.NL.
'           <table>'.NL.
'           <tr>'.NL.
'               <td><div class="content_field">'.SOAL_GROUP_NAME.': </div></td>'.NL.
'               <td><input type="text" name="gname" value="'.$grow['name'].'" /></td>'.NL.
'           </tr>'.NL.
'           </table><br />'.
'           <span class="content_h2">'.SOAL_CLIENT_MEMBERS.'</span><br/><br />'.NL.
'           <div class="innerBorder"><table>'.NL.
'               <tr>'.NL.
'                   <th>'.SOAL_CLIENT.'</th>'.NL.
'                   <th>'.SOAL_UNGROUP.'</th>'.NL.
'               </tr>'.NL;

// list all clients of group
try{
    $r = $dbc->query('SELECT '.DB_PRE.'_users.id, '.DB_PRE.'_users.username, '.DB_PRE.'_grp_cl.uid FROM '.
            DB_PRE.'_users RIGHT JOIN '.DB_PRE.'_grp_cl ON '.DB_PRE.'_users.id = '.DB_PRE.'_grp_cl.uid WHERE '.
            DB_PRE.'_users.owner = '.$userrow['id'].' AND '.DB_PRE.'_grp_cl.gid = '.$gid)->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    echo
'               <tr>'.NL.
'                   <td><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['uid'])).'">'.$value['name'].' ['.$value['username'].']</a></td>'.NL.
'                   <td align="center"><input type="checkbox" value="'.$value['uid'].'" name="ugc[]" /></td>'.NL.
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
'                   </tr>'.NL;

// TODO: List all resources group has access to
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
