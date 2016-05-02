<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "วางบิล/ใบเสร็จ/ใบกำกับ";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
include_once(dirname(__FILE__)."/pdo/pdo_ac.php");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$bid = filter_input(INPUT_GET,'bid',FILTER_UNSAFE_RAW);
$ivid = filter_input(INPUT_GET,'ivid',FILTER_SANITIZE_NUMBER_INT);
$rcid = filter_input(INPUT_GET,'rcid',FILTER_SANITIZE_NUMBER_INT);

if($action=="print"){
    include_once("ud/doc_default.php");
    $menu = new PAPmenu("th");
    $menu->__autoloadall("form");
    $menu->__autoloadall("table");
    $menu->pageTitle = "PAP | $pagename";
    $menu->astyle[] = $root."css/doc_default.css";
    $menu->extrascript = <<<END_OF_TEXT
<style>
body {
    background-color:#fff;
    margin:0;
    padding:0;
}
</style>
END_OF_TEXT;
    if(isset($bid)){
        $show = show_pbill($bid);
    } else if(isset($ivid)){
        $show = show_invoice($ivid);
    } else if(isset($rcid)){
        $show = show_receipt($rcid);
    }
    $content = $menu->showhead()
            . $show
            . "</body>";
    echo $content;
    exit;
}

$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = $root."js/order.js";
$menu->ascript[] = $root."js/acc.js";
$menu->astyle[] = $root."css/acc.css";
$menu->astyle[] = $root."css/status.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;

$tb = new mytable();
$db = new PAPdb(DB_PAP);
$pdo_ac = new pdo_ac();
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();

$content = $menu->showhead();
$content .= $menu->pappanel("บัญชี",$pagename);

if($action =="add"){
/*----------------------------------------------------- ADD P BILL -------------------------------------------------------------------*/
    if(isset($_SESSION['upap']['did'])){
        $arr_did = $_SESSION['upap']['did'];
        $arr_cid = $_SESSION['upap']['cid'];
        $str_cid = implode(",",$arr_cid);
        unset($_SESSION['upap']['did']);
        unset($_SESSION['upap']['cid']);
    } else {
        header("location:$redirect");
        exit();
    }
    $head = array("ลำดับ","ใบแจ้งหนี้","วันที่เอกสาร","การชำระ","กำหนดชำระ","ยอดเรียกเก็บ");
    $rec = $pdo_ac->get_bill_list(implode(",",$arr_did));
    $cus = $db->get_keypair("pap_customer", "customer_id", "customer_name", "WHERE customer_id IN ($str_cid)");
    $cinfo = $db->get_info("pap_customer", "customer_id", $arr_cid[0])+$db->get_meta("pap_customer_meta", "customer_id", $arr_cid[0]);
    $billg = find_date($cinfo,"bill");
    $chequeg = find_date($cinfo,"cheque");
    $ct = $db->get_keypair("pap_contact", "contact_id", "contact_name", "WHERE customer_id IN ($str_cid)");
    $content .= "<h1 class='page-title'>ออกใบวางบิล</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_select("cid", $cus, "label-inline", "ลูกค้า", null)
        . $form->show_select("pbill_ct", $ct, "label-inline", "ผู้ติดต่อ", null)
        . $form->show_text("date","date",$billg,"yyyy-mm-dd","วันที่วางบิล","","label-inline")
        . $form->show_text("paydate","paydate",$chequeg,"yyyy-mm-dd","วันนัดชำระ","","label-inline")
        . $form->show_select("payment", $op_bill_payment, "label-inline", "วิธีการชำระ", null)
        . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-inline");
    for($i=0;$i<count($arr_did);$i++){
        $content .= $form->show_hidden("did_$i","did[]",$arr_did[$i])
            . $form->show_hidden("price_$i","price[]",$rec[0][$i]);
    }

    $content .= "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . "<h4>รายการ</h4>"
        . $tb->show_table($head, $rec[1],"tb-bill-list");
    $content .= $form->show_submit("submit","Add New","but-right")
        . $form->show_hidden("request","request","add_p_bill")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date','paydate'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date,#paydate').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else if($action == "inv"){
/*----------------------------------------------------- CREATE INVOICE -------------------------------------------------------------------*/
    $did = filter_input(INPUT_GET,'did',FILTER_UNSAFE_RAW);
    $adid = explode(",",$did);
    $rec = [];
    foreach($adid as $k=>$v){
        $info = $pdo_ac->get_inv_remain($v);
        array_push($rec,array($v,$info['no'],$info['job'],$info['total'],$info['ivamount']));
    }
    $cus = $db->get_keypair("pap_customer AS cus", "cus.customer_id", "cus.customer_name", "RIGHT JOIN pap_delivery_dt AS dt ON dt.customer_id=cus.customer_id WHERE dt.deli_id=$did");
    $inside = $form->show_text("dno","dno","","","ใบแจ้งหนี้","","label-3070 readonly",null,"readonly")
            . $form->show_hidden("jobn","jobn","")
            . $form->show_num("amount", "", 1, "", "ยอดรวม", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดคงเหลือ", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("inv", "", 1, "", "ยอดออกใบกำกับ", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";
    $content .= "<h1 class='page-title'>ออกใบกำกับ</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_select("cid", $cus, "label-inline", "ลูกค้า", null)
        . $form->show_text("date","date",pap_today(),"yyyy-mm-dd","วันที่ใบกำกับ","","label-inline")
        . $form->show_num("discount", "", 0.01, "", "ส่วนลด", "", "label-inline","")
        . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-inline")
        . "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
        . "<div id='deli-list'></div>";

    $content .= $form->show_submit("submit","Add New","but-right")
        . $form->show_hidden("request","request","add_invoice")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "inv_function(".json_encode($rec).");"
            . "</script>";
} else if($action == "rc"){
/*----------------------------------------------------- CREATE RECEIPT -------------------------------------------------------------------*/
    $rec = [];
    $rinfo = $pdo_ac->get_rc_remain($ivid);
    //$total = ($rinfo['meta_value']=="yes"?0.97:1.04)*$rinfo['total']; //ยอดหหลังหัก ณที่จ่าย
    array_push($rec,array($ivid,$rinfo['no'],$rinfo['total'],$rinfo['pay']));

    $inside = $form->show_text("ivno","ivno","","","ใบกำกับภาษี","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดรวม", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดคงเหลือ", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("pay", "", 1, "", "ยอดออกใบเสร็จ", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";
    $content .= "<h1 class='page-title'>ออกใบเสร็จ</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_text("date","date",pap_today(),"yyyy-mm-dd","วันที่","","label-inline")
        . $form->show_select("payment", $op_bill_payment, "label-inline", "วิธีการชำระ", null)
        . "<div class='sel-payment-0'>"
            . $form->show_text("cno","cno","","","เช็คเลขที่","","label-inline")
            . $form->show_text("cdate","cdate","","","ลงวันที่","","label-inline")
            . $form->show_text("cbank","cbank","","","ธนาคาร","","label-inline")
            . $form->show_text("branch","branch","","","สาขา","","label-inline")
        . "</div><!-- .sel-payment-0 -->"
        . "<div class='sel-payment-1'>"
            . $form->show_text("tref","tref","","","รหัสการโอน","","label-inline")
            . $form->show_text("tbank","tbank","","","ธนาคาร","","label-inline")
        . "</div><!-- .sel-payment-1 -->"
        . "<div class='sel-payment-2'>"
            . $form->show_text("cash","cash","","","รายละเอียด","","label-inline")
        . "</div><!-- .sel-payment-2 -->"
        . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-inline")
        . "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
        . "<div id='deli-list'></div>";

    $content .= $form->show_submit("submit","Add New","but-right")
        . $form->show_hidden("request","request","add_receipt")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date,#cdate').datepicker({dateFormat: 'yy-mm-dd'});"
            . "rec_function(".json_encode($rec).");"
            . "select_option('payment');"
            . "</script>";
} else if($action == "editrc"){
/*----------------------------------------------------- EDIT RECEIPT -------------------------------------------------------------------*/
    $info = $db->get_info("pap_rc", "id", $rcid);
    $paid = $db->get_info("pap_rc_dt","rc_id",$rcid);
    $rec = [];
    $rinfo = $pdo_ac->get_rc_remain($paid['invoice_id'],$rcid);
    $total = ($rinfo['meta_value']=="yes"?0.97:1.04)*$rinfo['total']; //ยอดหหลังหัก ณที่จ่าย
    array_push($rec,array($paid['invoice_id'],$rinfo['no'],$total,$rinfo['pay'],$paid['amount']));

    $inside = $form->show_text("ivno","ivno","","","ใบกำกับภาษี","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดรวม", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดคงเหลือ", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("pay", "", 1, "", "ยอดออกใบเสร็จ", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";
    $content .= "<h1 class='page-title'>ออกใบเสร็จ</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_text("receipt","receipt",$info['no'],"","เลขที่ใบเสร็จ","","label-inline readonly",null,"readonly")
        . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่","","label-inline")
        . $form->show_select("payment", $op_bill_payment, "label-inline", "วิธีการชำระ", $info['payment'])
        . "<div class='sel-payment-0'>"
            . $form->show_text("cno","cno",$info['check_no'],"","เช็คเลขที่","","label-inline")
            . $form->show_text("cdate","cdate",$info['check_date'],"","ลงวันที่","","label-inline")
            . $form->show_text("cbank","cbank",$info['check_bank'],"","ธนาคาร","","label-inline")
            . $form->show_text("branch","branch",$info['check_bank_branch'],"","สาขา","","label-inline")
        . "</div><!-- .sel-payment-0 -->"
        . "<div class='sel-payment-1'>"
            . $form->show_text("tref","tref",$info['transfer_ref'],"","รหัสการโอน","","label-inline")
            . $form->show_text("tbank","tbank",$info['transfer_bank'],"","ธนาคาร","","label-inline")
        . "</div><!-- .sel-payment-1 -->"
        . "<div class='sel-payment-2'>"
            . $form->show_text("cash","cash",$info['cash_remark'],"","รายละเอียด","","label-inline")
        . "</div><!-- .sel-payment-2 -->"
        . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
        . "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
        . "<div id='deli-list'></div>";
    if($pauth == 4){
        $del = "<span id='del-receipt' class='red-but'>Delete</span>"
                . "<script>del_receipt();</script>";
    } else {
        $del = "";
    }
    $content .= $del
        . $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","edit_receipt")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("rcid","rcid",$rcid)
        . $form->show_hidden("ivid","ivid",$paid['invoice_id'])
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date,#cdate').datepicker({dateFormat: 'yy-mm-dd'});"
            . "rec_function(".json_encode($rec).");"
            . "select_option('payment');"
            . "</script>";
} else if($action =="editinv" && isset($ivid)){
/*----------------------------------------------------- EDIT INVOICE -------------------------------------------------------------------*/
    $info = $db->get_info("pap_invoice", "id", $ivid);
    $ivamount = $db->get_keypair("pap_invoice_dt", "deli_id", "amount","WHERE invoice_id=$ivid");
    $adid = $db->get_mm_arr("pap_invoice_dt", "deli_id", "invoice_id", $ivid);
    $rec = [];
    foreach($adid as $k=>$v){
        $dinfo = $pdo_ac->get_inv_remain($v,$ivid);
        array_push($rec,array($v,$dinfo['no'],$dinfo['job'],$dinfo['total'],$dinfo['ivamount'],$ivamount[$v]));
    }
    $cus = $db->get_keypair("pap_customer AS cus", "cus.customer_id", "cus.customer_name", "RIGHT JOIN pap_delivery_dt AS dt ON dt.customer_id=cus.customer_id WHERE dt.deli_id=$adid[0]");
    $inside = $form->show_text("dno","dno","","","ใบแจ้งหนี้","","label-3070 readonly",null,"readonly")
            . $form->show_hidden("jobn","jobn","")
            . $form->show_num("amount", "", 1, "", "ยอดรวม", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดคงเหลือ", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("inv", "", 1, "", "ยอดออกใบกำกับ", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";
    $content .= "<h1 class='page-title'>ออกใบกำกับ</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_text("delino","delino",$info['no'],"","เลขที่ใบกำกับ","","label-inline readonly",null,"readonly")
        . $form->show_select("cid", $cus, "label-inline", "ลูกค้า", $info['customer_id'])
        . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ใบกำกับ","","label-inline")
        . $form->show_num("discount", $info['discount'], 0.01, "", "ส่วนลด", "", "label-inline","")
        . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
        . "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
        . "<div id='deli-list'></div>";
    if($pauth == 4){
        $del = "<span id='del-invoice' class='red-but'>Delete</span>"
                . "<script>del_invoice();</script>";
    } else {
        $del = "";
    }
    $content .= $del
        . $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","edit_invoice")
        . $form->show_hidden("ivid","ivid",$ivid)
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));

    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "inv_function(".json_encode($rec).");"
            . "</script>";
} else if(isset($bid)){
/*----------------------------------------------------- EDIT P BILL -------------------------------------------------------------------*/
    $info = $db->get_info("pap_pbill", "id", $bid);
    $head = array("ลำดับ","ใบแจ้งหนี้","วันที่เอกสาร","การชำระ","กำหนดชำระ","ยอดเรียกเก็บ");
    $adid = $db->get_mm_arr("pap_pbill_dt", "deli_id", "pbill_id", $bid);
    $rec = $pdo_ac->get_bill_list(implode(",",$adid));
    $customer = $db->get_info("pap_customer", "customer_id", $info['customer_id']);
    $ct = $db->get_keypair("pap_contact", "contact_id", "contact_name", "WHERE customer_id=".$info['customer_id']);
    $content .= "<h1 class='page-title'>แก้ไขใบวางบิล</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-50'>"
        . $form->show_text("bill","bill",$info['no'],"","เลขที่วางบิล","","label-inline readonly",null,"readonly")
        . $form->show_text("customer","customer",$customer['customer_name'],"","ลูกค้า","","label-inline readonly",null,"readonly")
        . $form->show_hidden("cid","cid",$info['customer_id'])
        . $form->show_select("pbill_ct", $ct, "label-inline", "ผู้ติดต่อ", $info['contact'])
        . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่วางบิล","","label-inline")
        . $form->show_text("paydate","paydate",$info['pay_date'],"yyyy-mm-dd","วันนัดชำระ","","label-inline")
        . $form->show_select("payment", $op_bill_payment, "label-inline", "วิธีการชำระ", $info['payment'])
        . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline");
    for($i=0;$i<count($adid);$i++){
        $content .= $form->show_hidden("did_$i","did[]",$adid[$i])
            . $form->show_hidden("price_$i","price[]",$rec[0][$i]);
    }

    $content .= "</div><!-- .col-50 -->"
        . "<div class='col-100'>"
        . "<h4>รายการ</h4>"
        . $tb->show_table($head, $rec[1],"tb-bill-list");
    if($pauth == 4){
        $del = "<span id='del-pbill' class='red-but'>Delete</span>"
                . "<script>del_pbill();</script>";
    } else {
        $del = "";
    }
    $content .= $del
        . $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","edit_p_bill")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("bid","bid",$bid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date','paydate'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date,#paydate').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else {
/*----------------------------------------------------- VIEW ORDER -------------------------------------------------------------------*/
    $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $due = (isset($_GET['fil_due'])&&$_GET['fil_due']>0?$_GET['fil_due']:null);
    $s_cus = (isset($_GET['s_cus'])&&$_GET['s_cus']!=""?$_GET['s_cus']:null);

    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;

    $arrdue = $pdo_ac->get_job_pdue();
    $arrcid = $db->get_keypair("pap_order AS po", "quo.customer_id", "cus.customer_name", "LEFT JOIN pap_quotation AS quo ON quo.quote_id=po.quote_id LEFT JOIN pap_customer AS cus ON cus.customer_id=quo.customer_id");

    //csv
    $csv = ($pauth>3?"<a href='$root"."csv_download.php?req=acc&due=$mm' title='Download Data'><input type='button' class='blue-but' value='โหลดข้อมูล'/></a>":"");
    //view
    $head = array("ใบส่งของ","ลูกค้า","งาน","วันที่ส่ง","กำหนดชำระ","ใบวางบิล","วันนัดชำระ","ใบกำกับ","ใบเสร็จ");
    $rec = $pdo_ac->view_job_pbill($pauth,$due,$status,$s,$s_cus,$page, $iperpage);
    $all_rec = $pdo_ac->view_job_pbill($pauth, $due,$status,$s,$s_cus);
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัส หรือชื่องาน",$s)
            . $tb->show_search(current_url(), "cusid", "s_cus","ค้นหาลูกค้า",$s_cus)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_account, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_due", $arrdue, $due,"กำหนดชำระ")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table_keygroup($head,$rec,"tb-job-delivery",";")
            . $csv;
    if($pauth>1){
        $content .= $form->show_submit("submit","ออกใบวางบิล","but-right");
    }

    $content .= $form->show_hidden("request","request","add_billed")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?action=add")
            . $form->submitscript("check_sel(e);")
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "order_search();"
            . "place_bill();"
            . "customer_search();"
            . "</script>";
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;

function find_date($info,$txt){
    if($txt == "bill"){
        $type = $info['customer_collect_cheque'];
    } else {
        $type = $info['customer_place_bill'];
    }
    $today = date_create(null,timezone_open("Asia/Bangkok"));
    $month = date_format($today,"m");
    $year = date_format($today,"Y");
    if($type=="day"){
        $d = sprintf("%02s",$info[$txt."_day"]);
    } else if($type=="dofw"){
        $d = dofw_to_date($year, $month, $info[$txt.'_weekday'], $info[$txt.'_week']);
    } else if($type=="eofm"){
        $d = date_format($today,"t");
    }
    $res = date_create("$year-$month-$d",timezone_open("Asia/Bangkok"));
    if($res<$today){
        date_add($res,  date_interval_create_from_date_string("1 month"));
    }
    return date_format($res,"Y-m-d");
}