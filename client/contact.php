<?php

/*
 * Contact page for client
 */

if(!defined("PG_CLSCL"))
    soa_error("contact.php page accessed without permission");

// gather db info
try{
    // client info
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE id=?');
    $q->execute(array(SOA_CLID));
    $crow = $q->fetchAll()[0];
    
    // contact section
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_client_info WHERE uid=? AND ft!=?');
    $q->execute(array(SOA_CLID, -1));
    $inforow = $q->fetchAll();
    
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}

writeheader(SOAL_CONTACT.' - '.getSiteDBParam("titlebar", SOA_CLID), "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME_HOME, SOA_ROOT));
array_push($a, new menuItem(SOAL_ABOUT, SOA_ROOT.params(array("about"))));
array_push($a, new menuItem(SOAL_CONTACT, SOA_ROOT.params(array("contact"))));
if($userrow['type'] == 1)
    array_push($a, new menuItem(SOAL_EDITOR, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_LOGOUT, SOA_ROOT."/logout.php"));
client_header(SOA_HEAD1, SOA_HEAD2, $a);



echo
'           <span class="content_h1">'.SOAL_CONTACT.' '.$crow['name'].':</span><br/><br />'.NL;

foreach ($ctypes as $ctid => $ct) {
    // is there a contact of this type?
    $a = array();
    foreach ($inforow as $value) {
        if($value['ft'] == $ctid)
            array_push ($a, $value);
    }
    if(count($a) > 0)
    {
        echo
'           <span class="content_h2">'.$ct.':</span><br />'.NL.
'               <table>'.NL;
        foreach ($a as $value) {
            echo 
'                   <tr>'.NL.
'                       <td><span class="content_field">'.$value['field'].': </span></td>'.NL.
'                       <td>'.$value['info'].'</td>'.NL.
'                   </tr>'.NL;
        }
        echo 
'               </table><br />'.NL;
    }
}

client_footer();
writefooter();

?>
