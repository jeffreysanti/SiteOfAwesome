<?php

/*
 * Tile Menu system for client editor
 */

if(!defined("PG_CL"))
    soa_error("editor/mainpg.php page accessed without permission");

writeheader(SOAL_EDITORTITLE, "main.css");
$a = array();
array_push($a, new menuItem(SOAL_HOME.ARROW, SOA_ROOT));
array_push($a, new menuItem(SOAL_EDITOR, SOA_ROOT.params(array("editor"))));

client_header(SOAL_SOA, SOAL_EDITOR, $a, false);

echo
'           <span class="content_h1">'.SOAL_EDTDIR.'</span><br/><br />'.NL;

$d = new TileDirectory();
$d->AddBlock(new DirectoryBlock(SOAL_CGEDITOR, SOA_ROOT.params(array("editor","cg"))));
$d->AddBlock(new DirectoryBlock(SOAL_LOOKEDITOR, SOA_ROOT.params(array("editor","look"))));
$d->AddBlock(new DirectoryBlock(SOAL_FILEBROWSER, SOA_ROOT."/kcfinder/browse.php"));
$d->AddBlock(new DirectoryBlock(SOAL_YOUCONTACTEDITOR, SOA_ROOT.params(array("editor","info"))));
$d->AddBlock(new DirectoryBlock(SOAL_ARTICLEEDITOR, SOA_ROOT.params(array("editor","art"))));


echo $d->output();

client_footer();
writefooter();

// classes

class TileDirectory{
    public function __construct(){
        $this->list = array();
    }
    public function AddBlock($blk){
        array_push($this->list, $blk);
    }
    public function output(){
        $out = "";
        $out = $out .
'           <table class="tileRegion" align="center">'.NL;
        foreach ($this->list as $key => $value) {
            if($key % 2 == 0) // odd: start row
            {
                if($key > 1){
                    $out = $out .
'               </tr>'.NL;
                }
                $out = $out .
'               <tr>'.NL;
            }
            $out = $out . $value->output();
        }
        $out = $out .
'               </tr>'.NL.
'           </table>'.NL;
        return $out;
    }
    private $list;
}

class DirectoryBlock{
    var $text, $link;
    public function __construct($txt, $lnk="#") {
        $this->text = $txt;
        $this->link = $lnk;
    }
    public function output(){
        return 
'                   '.
'<td><a class="tile" href="'.$this->link.'"><span class="tilea">'.$this->text.'</span></a></td>'.NL;
    }
}
?>
