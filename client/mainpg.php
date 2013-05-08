<?php

/*
 * Main page for showing articles on client
 */

if(!defined("PG_CLSCL"))
    soa_error("mainpg.php page accessed without permission");


writeheader(getSiteDBParam("titlebar", SOA_CLID), "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME_HOME, SOA_ROOT));
array_push($a, new menuItem(SOAL_ABOUT, SOA_ROOT.params(array("about"))));
array_push($a, new menuItem(SOAL_CONTACT, SOA_ROOT.params(array("contact"))));
if($userrow['type'] == 1)
    array_push($a, new menuItem(SOAL_EDITOR, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_LOGOUT, SOA_ROOT."/logout.php"));
client_header(SOA_HEAD1, SOA_HEAD2, $a);

$articles = array();
$tags = array();
try{
    $qTagList = $dbc->prepare('SELECT tid FROM '.DB_PRE.'_tagcon WHERE aid=?');
    
    // aquire nessesary info
    if($userrow['type'] == 1){ // client -> list all
        $qList = $dbc->prepare('SELECT * FROM '.DB_PRE.'_art WHERE uid=?');
        $qList->execute(array($userrow['id']));
        $articles = $qList->fetchAll();
        foreach ($articles as $value) {
            $value['tags'] = array();
            $qTagList->execute(array($value['id']));
            $r2 = $qTagList->fetchAll();
            foreach($r2 as $value2){
                @$tags[$value2['tid']] ++;
                array_push($value['tags'], $value2['tid']);
            }
        }
    }
    else{ // subclient -> be selective
        // find out their groups
        $scid = $userrow['id'];
        $q = $dbc->prepare('SELECT gid FROM '.DB_PRE.'_grp_cl WHERE uid=?');
        $q->execute(array($scid));
        $r = $q->fetchAll();
        $scgrps = array();
        foreach ($r as $value)
            array_push($scgrps, $value);
        $qList->execute(array($userrow['owner']));
        $articles = $qList->fetchAll();

        // query preparations
        $qCond = $dbc->prepare('SELECT * FROM '.DB_PRE.'_acon WHERE aid=?');

        // remove illegals
        foreach ($articles as $key => $value) {
            // is it public?
            if($value['pub'] == 1)
                continue;

            // find asscociated conditions
            $qCond->execute(array($value['id']));
            $r2 = $qCond->fetchAll();
            $rm = true;
            foreach ($r2 as $value2) {
                if($value2['id'] != $scid && !in_array($value2['id'], $scgrps)) // not applicable
                        continue;
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
                unset($articles[$key]);
                continue;
            }
            // get tag list
            $value['tags'] = array();
            $qTagList->execute(array($value['id']));
            $r = $qTagList->fetchAll();
            foreach($r2 as $value2){
                @$tags[$value2['tid']] ++;
                array_push($value['tags'], $value2['tid']);
            }
        }
        $articles = array_values($articles); // reindex it
    }

    // get tag names
    $qTagLookup = $dbc->prepare('SELECT * FROM '.DB_PRE.'_tags WHERE id=?');
    $tagAlphaList = array();

    foreach ($tags as $key => $value) {
        $qTagLookup->execute(array($key));
        array_push($tagAlphaList, array($qTagLookup->fetchAll()[0]['text'], $key));
    }
    array_multisort($tagAlphaList);
    
    // now print out page
    
    
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}



// write out sidebar


client_footer();
writefooter();

?>
