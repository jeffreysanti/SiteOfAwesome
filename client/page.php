<?php
/*
 * Main page of client/subclient interface
 */

if(!isset($userrow) || ($userrow['type'] != 1 && $userrow['type'] != 2))
{
    header("location: ".SOA_ROOT);
    soa_error("Access to [Sub]Client Page Denied");
}
define("PG_CLSCL", true); // used to verify [sub]client logged in
// now determine what interface to show

if($userrow['type'] == 1){
    define("SOA_CLID", $_SESSION['soa_uid']);
    
}else{
    try{
        $q = $dbc->prepare('SELECT owner FROM '.DB_PRE.'_users WHERE id=? AND type=?');
        $q->execute(array($userrow['id'], 2));
        if($q->rowCount() < 1)
            soa_error ("Failed to aquire subclient owner-> id:".$userrow['id']);
        define("SOA_CLID", $q->fetchAll()[0][0]);
    }catch(PDOException $e){
        soa_error("Database failure: ".$e->getMessage());
    }
    
    // TODO: Handle subclient stuff here
}
LoadSiteSettings(SOA_CLID); // loads right theme & stuff

// other site settings
define("SOA_TITLEBAR", getSiteDBParam("titlebar", SOA_CLID, "SiteOfAwesome"));
define("SOA_HEAD1", getSiteDBParam("head1", SOA_CLID, $userrow['username']));
define("SOA_HEAD2", getSiteDBParam("head2", SOA_CLID, "Is Awesome"));


// find out what page to show
if(count($params) > 0){
    $pg = $params[1];
}
else{
    $pg = "";
}

switch($pg){
    case "editor": {
        require("editor/page.php");
        break;
    }
    case "about": {
        require("about.php");
        break;
    }
    case "contact": {
        require("contact.php");
        break;
    }
    
    default: {
        require("mainpg.php");
        break;
    }
}

// general functions

class menuItem{
    var $text, $link;
    public function __construct($txt, $lnk = "") {
        $this->text = $txt;
        $this->link = $lnk;
    }
    function output(){
        if($this->link != "-")
            return '<a href="'.$this->link.'">'.$this->text.'</a>';
        else
            return ''.$this->text.''; // TODO: Fix this (defult link set to '')
    }
}

function client_header($t1 = SOA_HEAD1, $t2 = SOA_HEAD2, $menua = array(), $expand=true){
    echo
'       <div id="header">'.NL.
'           <span id="header_h1">'.$t1.'</span><br />'.NL.
'           <span id="header_h2">'.$t2.'</span><br /><br />'.NL.
'           <br />'.NL;
    if(count($menua) > 0)
    {
        echo
'           <div id="header_menu"><ul>'.NL;
        $wid = 100 / count($menua);
        $i = 1;
        foreach ($menua as $value) {
            $li = "";
            if($expand)
                $li = ' style="width:'.$wid.'%;"';
            if($i == 1)
                $li = $li . ' id="mostLeft"';
            if($i == count($menua))
                $li = $li . ' id="mostRight"';
            echo
'               <li'.$li.'>'.$value->output().'</li>'.NL;
            $i ++;
        }
        echo
'           </ul></div>'.NL;
    }
    echo
'       </div>'.NL.
'       <div id="content">'.NL;    
}
function client_footer(){
    echo
'       </div>'.NL;
}

?>
