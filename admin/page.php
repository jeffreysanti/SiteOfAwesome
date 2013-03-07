<?php
/*
 * Index page for admin interface
 * 
 */

if(!isset($userrow) || $userrow['type'] != 0)
{
    header("location: ".SOA_ROOT);
    soa_error("Access to Admin Page Denied");
}

// find out what page to show
if(count($params) > 0){
    $pg = $params[1];
}
else{
    $pg = "";
}

switch($pg){
    default: {      // main page of admin screen
        writeheader("SiteOfAwesome Administration", "admin.css");
        
        // menu entires
        $a = array();
        array_push($a, new AdminNavEntry("Main Page", "", true));
        array_push($a, new AdminNavEntry("Accounts", SOA_ROOT.params(array("accounts"))));
        array_push($a, new AdminNavEntry("Appearance", SOA_ROOT.params(array("look"))));
        
        admin_writeheader("SiteOfAwesome Administration", $a);
        admin_writefooter();
        writefooter();
        break;
    }
}

class AdminNavEntry{
    public $link, $text, $active;
    public function __construct($txt, $lnk, $act=false) {
        $this->active = $act;
        $this->link = $lnk;
        $this->text = $txt;
    }
    
    public function write(){
        if($this->active){
            echo '              <li id="active">'.$this->text.'</li>';
        }
        else{
            echo '              <li><a href="'.$this->link.'">'.$this->text.'</a></li>';
        }
        echo NL;
    }
}

function admin_writeheader($title = "SiteOfAwesome Administration", $a= array())
{
    echo 
'       <div id="header">'.NL.
'           '.$title.NL.
'       </div>'.NL.
'       <div id="content_leftnav">'.NL.
'           <ul>'.NL;
    foreach ($a as $value) {
        $value->write();
    }
    echo
'           </ul>'.NL.
'       </div>'.NL.
'       <div id="content_main">'.NL;
}
function admin_writefooter()
{
    echo 
'       </div>'.NL;
}
?>
