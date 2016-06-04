<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("calendar");
$menu->pap_menu();
$menu->pageTitle = "PAP | ตารางนัดหมายลูกค้า";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->pappanel("ลูกค้า","ตารางนัดหมาย");

$form = new myform("papform");

$month = date_format(date_create(null,timezone_open("Asia/Bangkok")),"m");
$year = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y");

$job = $db->get_meet_schedule($pauth,$year,$month);
$cd = new mycalendar($year,$month);
$content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . "<div id='mycd-div'>"
        . $cd->show_calendar($job,"month",null,"mycd_change('meet');")
        . "</div>";
$content .= $menu->showfooter();
echo $content;
