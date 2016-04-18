<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root."upload.php";
__autoload("papmenu");
__autoload("pappdo");
__autoloada("phpcsv");
$pagename = "Upload";
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->extrascript = <<<END_OF_TEXT
<style>

</style>
END_OF_TEXT;
//check
if($pauth<4){
    header("location:$root");
    exit();
}

$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ",$pagename);

$form = new myform("pap-form","","request_upload.php");
$csv = new phpcsv("test_csv");
$type = array(
    "upload_paper" => "Paper",
    "upload_customer_group" => "Customer Group",
    "upload_customer" => "Customer"
);
$content .= "<h1 class='page-title'>$pagename</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form(null,true)
        . "<div class='col-50'>"
        . $csv->sel_csv_file("csv-input","csv-input")
        . $form->show_select("request", $type, "label-inline", "Type", null);

$content .= $form->show_submit("submit","Upload","but-right")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- .col-50 -->";
$form->addformvalidate("ez-msg", array('csv-input'));
$content .= $form->submitscript("")
        . "<script>"
        . "</script>";

$content .= $menu->showfooter();
echo $content;
