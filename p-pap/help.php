<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
if(!isset($_SESSION['upap'])){
    header("location:".PAP."login.php");
    exit;
}
$root = PAP;
$aroot = AROOTS;
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->pap_menu();
$menu->pageTitle = "PAP | ช่วยเหลือ";
$menu->extrascript = <<<END_OF_TEXT
<style>
    .help-list {
        padding-left:20px;
    }
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->pappanel("หน้าแรก","");
$csv = $root."help/วิธีการเปิดไฟล์ CSV ด้วย OFFICE 2003-2007.pdf";
$form = new myform("papform");
$content .= "<h1 class='page-title'>วิธีใช้งานโปรแกรม PAP</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . "<ul class='help-list'>"
        . "<li><a href='$csv' title='วิธีการเปิดไฟล์ CSV' target='_blank'>วิธีการเปิดไฟล์ CSV ด้วย MS.OFFICE 2003-2007</a></li>"
        . "</ul>"
            . "</div><!-- .col-100 -->";
$content .= $menu->showfooter();
echo $content;
