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

if(count($params) > 0){
    $pgnum = $params[1];
}
else{
    $pgnum = 1;
}

define("SOA_ART_PER_PG", 2);


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

    $max = 0;
    $min = 99999;
    foreach ($tags as $key => $value) {
        $qTagLookup->execute(array($key));
        array_push($tagAlphaList, array($qTagLookup->fetchAll()[0]['text'], $key));
        if($value > $max)
            $max = $value;
        if($value < $min)
            $min = $value;
    }
    $median = ceil(($max - $min)/2 + $min);
    array_multisort($tagAlphaList);
    
    // now print out page
    echo
'           <div id="content_sidebar">'.NL;
    
    if(false){ // TODO: Add tag filtering
        echo
'               <div class="content_sideh1">Filtered</div><br />'.NL;

// tags
    /*
'               <a href="#" class="content_sidebarop">PHP</a>'
                    */
    }
    
    // List all non-filtered tags held by filtered articles
    echo
'               <br />'.NL.
'               <div class="content_sideh1">Tags</div><br />'.NL;
    foreach ($tagAlphaList as $value){
        if($tags[$value[1]] < $median-(($max-$min)/2))
            $s = "content_sidebaropxs";
        elseif($tags[$value[1]] < $median-1)
            $s = "content_sidebarops";
        elseif($tags[$value[1]] >= $median-1 && $tags[$value[1]] <= $median +1)
            $s = "content_sidebaropm";
        elseif($tags[$value[1]] > $median+(($max-$min)/2))
            $s = "content_sidebaropxl";
        else $s = "content_sidebaropl";
        
        echo
'               <a href="#" class="'.$s.'">'.$value[0].'</a>'.NL;
    }

    echo
'           </div>'.NL.
'           <div id="content_main">'.NL;
    
    $i=0;
    foreach($articles as $value){
        $i++;
        if($i <= SOA_ART_PER_PG * ($pgnum-1))
            continue; // too early
        if($i > SOA_ART_PER_PG * ($pgnum))
            break;
        echo 
'               <div class="content_articlestub">'.NL.
'                   <div class="article_heading"><a class="article_heading" href="'.
                SOA_ROOT.params(array("art",$value['id'])).'">'.$value['name'].'</a></div>'.NL;
        @$maxChars = strpos(strip_tags($value['text']), " ", 1000);
        if($maxChars==0) $maxChars = -1;
        $txt = @substr(strip_tags($value['text']), 0, $maxChars); // first 1000 characters
        echo
'                   <p>'.$txt.'</p>'.NL;;
        echo
'                   <p class="article_cont">'.NL.
'                       <a class="article_cont" href="'.SOA_ROOT.params(array("art",$value['id'])).'">[Continued]</a>'.NL.
'                   </p>'.NL.
'               </div>';
    }
    $tot = count($articles);
    $pges = ceil($tot / SOA_ART_PER_PG);
    echo
'               <ul id="content_pagesel">'.NL;
    if($pges > $pgnum)
        echo
'                   <li class="next"><a href="'.SOA_ROOT.params(array($pgnum+1)).'">Next »</a></li>'.NL;
    else
        echo
'                   <li class="next_none">Next »</li>'.NL;
    
    for($i=$pges; $i>0;$i--){
        if($i == $pgnum){
            echo
'                   <li class="active">'.$i.'</li>'.NL;
        }else{
            echo
'                   <li><a href="'.SOA_ROOT.params(array($i)).'">'.$i.'</a></li>'.NL;
        }
    }
    if($pgnum > 1)
        echo
'                   <li class="prev"><a href="'.SOA_ROOT.params(array($pgnum-1)).'">«Previous</a></li>'.NL;
    else
        echo
'                   <li class="prev_none">«Previous</li>'.NL;
echo
'               </ul>'.NL.
'               <br />'.NL.
'           </div>'.NL;
    
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}



// write out sidebar


client_footer();
writefooter();

?>
