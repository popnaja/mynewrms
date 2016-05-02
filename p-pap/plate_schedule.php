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
$pagename = "ตารางส่งเพลต";
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("calendar");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายกราฟฟิก",$pagename);

$form = new myform("papform");

$month = date_format(date_create(null,timezone_open("Asia/Bangkok")),"m");
$year = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y");

$job = $db->get_plate_sch($op_job_code,$year, $month);
$cd = new mycalendar($year,$month,150);
$content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . "<div id='mycd-div'>"
        . $cd->show_calendar($job,"month","mycd_change('plate_cd');")
        . "</div>";
$content .= $menu->showfooter();
echo $content;
