<?php

if(!defined("PG_CLSCL"))
    soa_error("art.php page accessed without permission");

if(count($params) > 1){
    $aid = $params[2];
}
else{
    header("location:".SOA_ROOT);
    die();
}

$qList = $dbc->prepare('SELECT * FROM '.DB_PRE.'_art WHERE uid=? AND id=?');

// fetch article
if($userrow['type'] == 1){ // client -> list all
    $qList->execute(array($userrow['id'], $aid));
    if($qList->rowCount() < 1){ // article does not exits / wrong user
        header("location:".SOA_ROOT);
        die();
    }
    
    $arow = $qList->fetchAll()[0];
}
else{ // subclient -> be selective
    // find out their groups
    $scid = $userrow['id'];
    $q = $dbc->prepare('SELECT gid FROM '.DB_PRE.'_grp_cl WHERE uid=?');
    $q->execute(array($scid));
    $r = $q->fetchAll();
    $scgrps = array();
    foreach ($r as $value)
        array_push($scgrps, $value[0]);
    $qList->execute(array($userrow['owner'], $aid));
    if($qList->rowCount() < 1){ // article does not exits / wrong user
        header("location:".SOA_ROOT);
        die();
    }
    $arow = $qList->fetchAll()[0];
    
    // now further verify subclient has permissions

    // is it public?
    if($arow['pub'] != 1){
        // find asscociated conditions
        $qCond = $dbc->prepare('SELECT * FROM '.DB_PRE.'_acon WHERE aid=?');
        $qCond->execute(array($arow['id']));
        $r2 = $qCond->fetchAll();
        $rm = true;
        foreach ($r2 as $value2) {
            if($value2['id'] != $scid && !in_array($value2['id'], $scgrps)){ // not applicable
                    continue;
            }
            if($value2['type'] == 2 && $value2['id'] == $scid){ // client blocked explicitly
                $rm = true;
                break; // imediate priority
            }
            if($value2['type'] == 1 && $value2['id'] == $scid){ // client allowed explicitly
                $rm = false;
            }
            if($value2['type'] == 0 && in_array($value2['id'], $scgrps)){ // client's group allowed
                $rm = false;
            }
        }
        if($rm){
            header("location:".SOA_ROOT);
            die();
        }
    }
}

// now that article is accessable get tag list
$qTagList = $dbc->prepare('SELECT tid FROM '.DB_PRE.'_tagcon WHERE aid=?');
$qTagLookup = $dbc->prepare('SELECT * FROM '.DB_PRE.'_tags WHERE id=?');

$qTagList->execute(array($aid));
$r = $qTagList->fetchAll();

$tagslist = array();
foreach($r as $value){
    $qTagLookup->execute(array($value[0]));
    if($qTagLookup->rowCount() < 1)
        $name = "";
    else
        $name = $qTagLookup->fetchAll()[0]['text'];
    $name = '<a href="'.SOA_ROOT.params(array('1', $value[0])).'">'.$name.'</a>';
    array_push($tagslist, $name);
}


writeheader(getSiteDBParam("titlebar", SOA_CLID), "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME_HOME, SOA_ROOT));
array_push($a, new menuItem(SOAL_ABOUT, SOA_ROOT.params(array("about"))));
array_push($a, new menuItem(SOAL_CONTACT, SOA_ROOT.params(array("contact"))));
if($userrow['type'] == 1)
    array_push($a, new menuItem(SOAL_EDITOR, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_LOGOUT, SOA_ROOT."/logout.php"));
client_header(SOA_HEAD1, SOA_HEAD2, $a);

echo
'           <div class="content_article">'.NL.
'               <div class="article_heading">'.$arow['name'].'</div>'.NL.
'               '.$arow['text'].NL.
'               <div class="article_meta"><table>'.NL.
'                   <tr>'.NL.
'                       <td><span class="field">Tags: </span></td>'.NL.
'                       <td>'.implode(' | ', $tagslist).'</td>'.NL.
'                   </tr>'.NL.
'               </table></div>'.NL.
'           </div>'.NL;

client_footer();
writefooter();


?>
