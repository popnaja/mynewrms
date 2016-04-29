<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
$root = PAP;
__autoload("menu");
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("slider");
$menu->pageTitle = "ระบบบริหารจัดการโรงงานครบวงจร | ". SITE;
$menu->ascript[] = AROOTS."js/jquery.mobile.custom.min.js";
$menu->ascript[] = $root."js/pap.js";
$menu->astyle[] = $root."css/index.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;
$menu->show_contact();
$content = $menu->showhead();
$content .= $menu->showpanel("","",false);
$link1 = PAP."login.php";
$link2 = "http://www.smartgreeny.com/";
$paplogo = PAP."image/pap_logo.jpg";
$greenlogo = AROOTS."image/greeny_logo.png";
$content .= $menu->contact
        . "<div class='banner'>"
        . "<img src='".LOGO."' alt='".SITE."'/>"
        . "<div>"
        . "<a class='app-logo' href='$link1' title='PAP'><img src='$paplogo' alt='PAP Logo' /></a>"
        . "<a class='app-logo' href='$link2' title='Smart Greeny'><img src='$greenlogo' alt='PAP Logo' /></a>"
        . "</div>"
        . "</div><!-- .banner -->"
        . "<script>response_index();</script>";
$content .= $menu->showfooter();
echo $content;