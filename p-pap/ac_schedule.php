<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "ตารางวางบิลรับเช็ค";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
include_once(dirname(__FILE__)."/pdo/pdo_ac.php");
$pdo_ac = new pdo_ac();
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("calendar");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = $root."js/acc.js";
$menu->astyle[] = $root."css/acc.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->pappanel("บัญชี",$pagename);

$form = new myform("papform");
$month = date_format(date_create(null,timezone_open("Asia/Bangkok")),"m");
$year = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y");
$bill = $pdo_ac->get_bill_check($year,$month);
$cd = new mycalendar($year,$month);
$content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . "<div id='mycd-div'>"
        . $cd->show_calendar($bill,"month",null,"mycd_change('mycd_bill');")
        . "</div>";
$content .= $menu->showfooter();
echo $content;