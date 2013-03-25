<?php

/*
 * Client/Group Editort
 */

if(!defined("PG_CL"))
    soa_error("editor/cg.php page accessed without permission");

// check for other pg
if(count($params) > 2){
    $pg = $params[3];
}
else{
    $pg = "";
}

switch($pg){
    case "g":{ // group
        require("cg_g.php");
        die();
        break;
    }
    default: {
        break;
    }
}

$msg = "";
if(isset($_POST['submit']))
{
    if(isset($_POST['publicprof'])){
        updateSiteDBParam("pubp", 1, $userrow['id']);
    }else{
        updateSiteDBParam("pubp", 0, $userrow['id']);
    }
    
    // add a group ?
    if(isset($_POST['groupname']) && $_POST['groupname'] != ""){
        $groupname = $_POST['groupname'];
        try{
            $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_groups WHERE name = ? AND owner = ?');
            $q->execute(array($groupname, $userrow['id']));
            if($q->rowCount() > 0)
                $msg = $msg . SOAL_MSG_ERRGRPNAME . "<br />".NL;
            else {
                $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_groups (name,owner) VALUES (?,?)');
                $q->execute(array($groupname, $userrow['id']));
                $msg = $msg . SOAL_MSG_GRPADED . "<br />".NL;
            }
        }catch(PDOException $e){
            soa_error("Database failure: ".$e->getMessage());
        }
    }
    // add a subclient ?
    if(isset($_POST['clientlogin']) && $_POST['clientlogin'] != ""){
        if(!isset($_POST['clientpass']) || strlen($_POST['clientpass']) < 5){
            $msg = $msg . SOAL_MSG_ERRPSW . "<br />" . NL;
        }
        else
        {
            $clname = $_POST['clientlogin'];
            $pswd = md5($_POST['clientpass']);
            try{
                $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE username = ?');
                $q->execute(array($clname));
                if($q->rowCount() > 0)
                    $msg = $msg . SOAL_MSG_ERRCLINAME . "<br />".NL;
                else {
                    $q = $dbc->prepare('INSERT INTO '.DB_PRE.'_users (username, password, type, owner) VALUES (?,?,?,?)');
                    $q->execute(array($clname, $pswd, 2, $userrow['id']));
                    $msg = $msg . SOAL_MSG_CLIADED . "<br />".NL;
                }
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
    }
    
    // remove groups
    if(isset($_POST['rmg'])){
        foreach ($_POST['rmg'] as $value) {
             try{
                $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_groups WHERE id = ? AND owner = ? LIMIT 1');
                $q->execute(array($value, $userrow['id']));
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        $msg = $msg . SOAL_MSG_GROUP_REMOVED . "<br />".NL;
    }
    // remove clients
    if(isset($_POST['rmc'])){
        foreach ($_POST['rmc'] as $value) {
             try{
                $q = $dbc->prepare('DELETE FROM '.DB_PRE.'_users WHERE id = ? AND owner = ? LIMIT 1');
                $q->execute(array($value, $userrow['id']));
                
            }catch(PDOException $e){
                soa_error("Database failure: ".$e->getMessage());
            }
        }
        $msg = $msg . SOAL_MSG_CLIENT_REMOVED . "<br />".NL;
    }
}

// retrieve information to populate data with
$pubprof = getSiteDBParam("pubp", $userrow['id'], "0");


writeheader(SOAL_EDITORTITLE, "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR.ARROW, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_CGEDITOR, SOA_ROOT.params(array("editor", "cg"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

$pbl = ($pubprof == 1 ? " checked" : "");

if($msg != ""){
    echo
'           <span class="notice">'.$msg.'</span>';
}

echo
'           <div id="content_rightside">'.NL.
'               <span class="content_h1">'.SOAL_ABOUT.'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_PUBLICVIEWING.'</span><br/><br />'.NL.
'               <p>'.SOAL_PARA_PBLVIEWING.'</p><br />'.NL.
'               <span class="content_h2">'.SOAL_GROUPS.'</span><br/><br />'.NL.
'               <p>'.SOAL_PARA_GROUPS.'</p><br />'.NL.
'           </div>'.NL.
'           <div id="content_leftside"><form method="post" action="'.SOA_ROOT.params(array('editor', 'cg')).'">'.NL.
'               <span class="content_h1">'.SOAL_CONFIG.'</span><br/><br />'.NL.
'               <span class="content_h2">'.SOAL_GENERAL.'</span><br/><br />'.NL.
'               <table>'.NL.
'               <tr>'.NL.
'                   <td><div class="content_field">'.SOAL_PUBLICPROFILE.': </div></td>'.NL.
'                   <td><input type="checkbox" name="publicprof"'.$pbl.' /></td>'.NL.
'               </tr>'.NL.
'               </table><br />'.
'               <span class="content_h2">'.SOAL_GROUP_LIST.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_GROUP.'</th>'.NL.
'                       <th>'.SOAL_REMOVE.'</th>'.NL.
'                   </tr>'.NL;

// list all groups
try{
    $r = $dbc->query('SELECT * FROM '.DB_PRE.'_groups WHERE owner = '.$userrow['id'].' ORDER BY name')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','cg','g',$value['id'])).'">'.$value['name'].'</a></td>'.NL.
'                       <td align="center"><input type="checkbox" value="'.$value['id'].'" name="rmg[]" /></td>'.NL.
'                   <tr>'.NL;
    
}

echo
'               </table></div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_GROUP_NAME.': </div></td>'.NL.
'                       <td><input type="text" name="groupname" /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_CLIENT_LIST.'</span><br/><br />'.NL.
'               <div class="innerBorder"><table>'.NL.
'                   <tr>'.NL.
'                       <th>'.SOAL_CLIENT.'</th>'.NL.
'                       <th>'.SOAL_GROUP_S.'</th>'.NL.
'                       <th>'.SOAL_REMOVE.'</th>'.NL.
'                   </tr>'.NL;

// subclient list
try{
    $r = $dbc->query('SELECT * FROM '.DB_PRE.'_users WHERE owner='.$userrow['id'].' ORDER BY username')->fetchAll();
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}
foreach($r as $value){
    echo
'                   <tr>'.NL.
'                       <td><a href="'.SOA_ROOT.params(array('editor','cg','c',$value['id'])).'">'.$value['username'].'</a></td>'.NL.
'                       <td>...</td>'.NL.
'                       <td align="center"><input type="checkbox" value="'.$value['id'].'" name="rmc[]" /></td>'.NL.
'                   <tr>'.NL;
    
}

echo
'               </table></div><br />'.NL.
'               <table>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_LOGIN.': </div></td>'.NL.
'                       <td><input type="text" name="clientlogin" /></td>'.NL.
'                   </tr>'.NL.
'                   <tr>'.NL.
'                       <td><div class="content_field">'.SOAL_PASSWORD.': </div></td>'.NL.
'                       <td><input type="text" name="clientpass" /></td>'.NL.
'                   </tr>'.NL.
'               </table><br />'.NL.
'               <span class="content_h2">'.SOAL_DONE.':</span><br/>'.NL.
'               <div id="content_submit"><input type="submit" name="submit" value="'.SOAL_UPDATE.'" /></div>'.NL.
'               <div class="content_linkbtn"><a href="'.SOA_ROOT.params(array('editor')).'">'.SOAL_CANCEL.'</a></div>'.NL.
'           </form></div>';

client_footer();
writefooter();

?>
