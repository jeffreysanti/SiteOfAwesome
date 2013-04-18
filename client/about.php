<?php

/*
 * About page for client
 */

if(!defined("PG_CLSCL"))
    soa_error("about.php page accessed without permission");

// gather db info
try{
    // client info
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_users WHERE id=?');
    $q->execute(array(SOA_CLID));
    $crow = $q->fetchAll()[0];
    
    // about section
    $q = $dbc->prepare('SELECT * FROM '.DB_PRE.'_client_info WHERE uid=? AND ft=?');
    $q->execute(array(SOA_CLID, -1));
    $inforow = $q->fetchAll();
    
    $imgPath = "";
    $enImage = getSiteDBParam("profimge", SOA_CLID);
    if($enImage == 1)
        $imgPath = SOA_ROOT.'/kcfinder/upload/'.SOA_CLID.'/'.getSiteDBParam("profimg", SOA_CLID);
    
}catch(PDOException $e){
    soa_error("Database failure: ".$e->getMessage());
}


writeheader(SOAL_ABOUT.' - '.getSiteDBParam("titlebar", SOA_CLID), "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME_HOME, SOA_ROOT));
array_push($a, new menuItem(SOAL_ABOUT, SOA_ROOT.params(array("about"))));
array_push($a, new menuItem(SOAL_CONTACT, SOA_ROOT.params(array("contact"))));
if($userrow['type'] == 1)
    array_push($a, new menuItem(SOAL_EDITOR, SOA_ROOT.params(array("editor"))));
array_push($a, new menuItem(SOAL_LOGOUT, SOA_ROOT."/logout.php"));
client_header(SOA_HEAD1, SOA_HEAD2, $a);




echo
'           <span class="content_h1">'.SOAL_ABOUT.' '.$crow['name'].':</span><br/><br />'.NL.
'           <span class="content_h2">'.SOAL_BASIC_INFO.':</span><br />'.NL.
'           <table width="100%"><tr><td valign="top">'.NL.
'               <table>'.NL;
foreach ($inforow as $value) {
    echo
'                   <tr>'.NL.
'                       <td><span class="content_field">'.$value['field'].': </span></td>'.NL.
'                       <td>'.$value['info'].'</td>'.NL.
'                   </tr>'.NL;
}
echo
'               </table></td>'.NL.
'               <td valign="top" align="right">';
if($imgPath != "")
    echo
'                   <img src="'.$imgPath.'" alt="'.$crow['name'].'" width="30%"/>'.NL;
echo 
'               </td></tr>'.NL.
'           </table>'.NL;

client_footer();
writefooter();

?>
