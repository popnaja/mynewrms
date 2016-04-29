<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$pagename = "ที่อยู่จัดส่ง";
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->ascript[] = $root."js/shipping_address.js";
$menu->extrascript = <<<END_OF_TEXT
        <style>
        #tb-address th:nth-child(1) {
            width:50px;
        }
        .but-right{
            width:50%;
        }
        </style>
END_OF_TEXT;

$cid = filter_input(INPUT_GET,'cid',FILTER_SANITIZE_NUMBER_INT);
$adid = filter_input(INPUT_GET,'adid',FILTER_SANITIZE_NUMBER_INT);

$tb = new mytable();
$tbpdo = new tbPDO();
$db = new PAPdb(DB_PAP);
$md = new mymedia(PAP."request_ajax.php");
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$content = $menu->showhead();
$content .= $menu->pappanel("ลูกค้า",$pagename);

if(isset($cid)){
    /* ------------------------------------------------------  ADD ADDRESS ------------------------------------------------------------*/
    $info = $db->get_info("pap_customer", "customer_id", $cid);
    $head = array("แก้ไข","ที่อยู่");
    $rec = $tbpdo->view_cus_ad($cid);
    
    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("cus","cus",$info['customer_code']." : ".$info['customer_name'],"","ลูกค้า","","label-inline readonly",null,"readonly")
            . "<div class='label-inline'>"
            . "<label for='map'>ภาพแผนที่</label>"
            . "<div>"
            . "<div id='map-box'></div>"
            . $md->show_input("map","map","")
            . "</div>"
            . "</div><!-- .label-inline -->"
            . $form->show_text("name","name","","","ชื่อสถานที่ $req","","label-inline")
            . $form->show_textarea("address","",6,10,"","ที่อยู่จัดส่ง $req","label-inline")
            . $form->show_submit("submit","Add","but-right")
            . $form->show_hidden("request","request","add_cus_ad")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_hidden("redirect","redirect",$redirect."?cid=$cid")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $tb->show_table($head, $rec, "tb-address")
            . "</div><!-- .col-50 -->";
    $form->addformvalidate("ez-msg", array('address'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "</script>";
} else if(isset($adid)){
    /* ------------------------------------------------------  EDIT ADDRESS ------------------------------------------------------------*/
    $adinfo = $db->get_info("pap_cus_ad","id",$adid);
    $cid = $adinfo['customer_id'];
    $info = $db->get_info("pap_customer", "customer_id", $cid);
    
    //load media
    $map = $md->media_view($adinfo['map'],ROOTS,RDIR);
    
    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("cus","cus",$info['customer_code']." : ".$info['customer_name'],"","ลูกค้า","","label-inline readonly",null,"readonly")
            . "<div class='label-inline'>"
            . "<label for='map'>ภาพแผนที่</label>"
            . "<div>"
            . "<div id='map-box'></div>"
            . $md->show_input("map","map",$map)
            . "</div>"
            . "</div><!-- .label-inline -->"
            . $form->show_text("name","name",$adinfo['name'],"","ชื่อสถานที่ $req","","label-inline")
            . $form->show_textarea("address",$adinfo['address'],6,10,"","ที่อยู่จัดส่ง $req","label-inline")
            . $form->show_hidden("ori_media","ori_media",$adinfo['map'])
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_cus_ad")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("adid","adid",$adid)
            . $form->show_hidden("redirect","redirect",$redirect."?cid=$cid")
            . "</div><!-- .col-50 -->";
    $form->addformvalidate("ez-msg", array('address'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "</script>";
} else {
    /* ------------------------------------------------------   SEARCH CUSTOMER ------------------------------------------------------------*/
    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . $form->show_text("cus","cus","","ค้นหา 3 ตัวอักษรขึ้นไป","ค้นหาลูกค้า $req","","label-inline")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-50 -->"
            . "<script>"
            . "search_customer();"
            . "</script>";
}

$content .= $menu->showfooter();
echo $content;