<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$uid = $_SESSION['upap'][0];
$pagename = "บัญชีสั่งซื้อ";
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");
include_once(dirname(__FILE__)."/pdo/pdo_ac.php");
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = $root."js/acc.js";
$menu->astyle[] = $root."css/acc.css";

$tb = new mytable();
$db = new PAPdb(DB_PAP);
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$pdo_ac = new pdo_ac();

$content = $menu->showhead();
$content .= $menu->pappanel("บัญชี",$pagename);

/*----------------------------------------------------- VIEW ORDER -------------------------------------------------------------------*/
$due = (isset($_GET['fil_due'])&&$_GET['fil_due']>0?$_GET['fil_due']:null);
$s_sup = (isset($_GET['s_sup'])&&$_GET['s_sup']!=""?$_GET['s_sup']:null);
$page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
$iperpage = 20;

$arrdue = $pdo_ac->get_po_due();
//view
$head = array("ใบสั่งซื้อ","ผู้ผลิต","วันที่รับสินค้า","กำหนดชำระ","ชำระเงิน");
$rec = $pdo_ac->view_po_list($pauth,$due,$s_sup,$page, $iperpage);
$all_rec = $pdo_ac->view_po_list($pauth,$due,$s_sup);
$max = ceil(count($all_rec)/$iperpage);

//csv
$csv = "";
if($pauth>3){
    $csv = "<a href='$root"."csv_download.php?req=ac_buy&due=$due' title='Download Data'><input type='button' class='blue-but' value='โหลดข้อมูล'/></a>";
}

$content .= "<h1 class='page-title'>$pagename </h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . "<div class='col-100'>"
        . $form->show_st_form()
        . $tb->show_search(current_url(), "cusid", "s_sup","ค้นหาผู้ผลิต",$s_sup)
        . $tb->show_filter(current_url(), "fil_due", $arrdue, $due,"กำหนดชำระ")
        . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
        . $tb->show_pagenav(current_url(), $page, $max)
        . $tb->show_table($head,$rec,"tb-po-list")
        . $csv;

$content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect."?action=add")
        . "</div><!-- .col-100 -->";
$box = "<h4>ชำระค่าสินค้า/บริการ</h4>"
        . "<div id='box-msg'></div>"
        . $form->show_st_form()
        . "<div class='sel-status-2 sel-status-5'>"
        . $form->show_text("date", "date", "", "", "วันที่ $req", "", "label-3070")
        . $form->show_text("ref", "ref", "", "", "เอกสารอ้างอิง $req", "", "label-3070")
        . "</div>"
        . $form->show_hidden("poid","poid","0")
        . $form->show_hidden("table","table","")
        . $form->show_submit("submit","กำหนด","but-right")
        . $form->show_hidden("request","request","update_po_paid")
        . $form->show_hidden("redirect","redirect",$redirect);
$form->addformvalidate("box-msg", array('date','ref'));
$box .= $form->submitscript("$('#papform').submit();")
        ."<script>"
        . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
        . "po_paid_function();"
        . "</script>";

$content .= $form->show_float_box($box,"paid-box");

$content .= $menu->showfooter();
echo $content;

