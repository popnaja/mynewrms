<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
include_once("ud/doc_default.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "Manual/ใบแจ้งหนี้";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$oid = filter_input(INPUT_GET,'oid',FILTER_UNSAFE_RAW);
$did = filter_input(INPUT_GET,'did',FILTER_SANITIZE_NUMBER_INT);
$tdid = filter_input(INPUT_GET,'tdid',FILTER_SANITIZE_NUMBER_INT);

if($action=="print"){
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
    $content = $menu->showhead()
            . (isset($did)?show_deli($did):show_tdeli($tdid))
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
$menu->ascript[] = $root."js/delivery.js";
$menu->astyle[] = $root."css/delivery.css";
$menu->astyle[] = $root."css/status.css";
$menu->astyle[] = $root."css/doc_default.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;

$tbpdo = new tbPDO();
$tb = new mytable();
$db = new PAPdb(DB_PAP);
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายผลิต",$pagename);
if($action=="add"){
    /*----------------------------------------------------- MANUAL ADD DELIVERY ------------------------------------------------------------*/
    //loaddata
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $typen = json_encode($product_type);
    $jdt = "";
    for($i=1;$i<=8;$i++){
        $jdt .= $form->show_text("jdt_$i","jdt[]","","","รายละเอียด $i","","label-3070",null,"");
    }
    $inside = $form->show_text("name","name","","","งานพิมพ์","","label-3070",null,"")
            . $jdt
            . $form->show_select("type",$product_type,"label-3070","ประเภทงาน",null)
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070","min='0'")
            . $form->show_num("price", "", 0.01, "", "ราคาต่อหน่วย", "", "label-3070","min='0'")
            . $form->show_num("discount", "", 0.01, "", "ส่วนลด", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

    $content .= "<h1 class='page-title'>สร้าง$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("scid","scid","","ค้นหา 3 ตัวอักษรขึ้นไป","ลูกค้า","","label-inline")
            . $form->show_hidden("cid","cid","0")
            . "<div id='cus_info'></div>"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $form->show_text("date","date","","yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-inline")
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_mjob_deli")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("pauth","pauth",$pauth)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("check_mdeli(e);")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "mdeli_function('',$typen);"
            . "get_cus_info();"
            . "</script>";
} else if ($action=="edit"&&isset($did)){
    /*----------------------------------------------------- EDIT MANUAL DELIVERY ------------------------------------------------------------*/
    //loaddata
    $info = $db->get_info("pap_delivery", "id", $did);
    $dtinfo = $db->get_infos("pap_delivery_dt", "deli_id", $info['id']);
    $cid = $dtinfo[0]['customer_id'];
    $credit = $dtinfo[0]['credit'];
    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$cid);
    $cinfo = $db->get_info("pap_customer", "customer_id", $cid);
    $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
    $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id=$cid");
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $typen = json_encode($product_type);
    
    $rec = [];
    foreach($dtinfo as $k=>$v){
        $meta = $db->get_meta("pap_delidt_meta", "dtid", $v['id']);
        $detail = (isset($meta['job_detail'])?$meta['job_detail']:"");
        array_push($rec,array($v['job_name'],$v['type'],$v['qty'],$v['price']/$v['qty'],$v['discount'],$detail));
    }
    $jrec = json_encode($rec);
    $jdt = "";
    for($i=1;$i<=8;$i++){
        $jdt .= $form->show_text("jdt_$i","jdt[]","","","รายละเอียด $i","","label-3070",null,"");
    }
    $inside = $form->show_text("name","name","","","งานพิมพ์","","label-3070",null,"")
            . $jdt
            . $form->show_select("type",$product_type,"label-3070","ประเภทงาน",null)
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070","min='0'")
            . $form->show_num("price", "", 0.01, "", "ราคาต่อหน่วย", "", "label-3070","min='0'")
            . $form->show_num("discount", "", 0.01, "", "ส่วนลด", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

    $content .= "<h1 class='page-title'>แก้ไข$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("dno","dno",$info['no'],"","รหัสใบแจ้งหนี้","","label-inline readonly",null," readonly")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_select("deli_ct", $contacts, "label-inline", "ผู้ติดต่อ", $info['contact'])
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",$info['address'])
            . "<a href='shipping_address.php?cid=$cid' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_num("credit", $credit, 1, "", "เครดิต(วัน)", "", "label-inline","min=0")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";
    if($pauth > 3){
        $del = "<span id='del-job-deli' class='red-but'>Delete</span>"
                . "<script>del_job_deli();</script>";
    } else {
        $del = "";
    }
    $content .= $del 
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_mjob_deli")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("did","did",$did)
            . $form->show_hidden("pauth","pauth",$pauth)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("check_mdeli(e);")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "mdeli_function($jrec,$typen);"
            . "</script>";
} else if ($action=="addtdeli"&&isset($did)){
    /*----------------------------------------------------- ADD TEMP DELIVERY ------------------------------------------------------------*/
    //loaddata
    $info = $db->get_info("pap_delivery", "id", $did);
    $dtinfo = $db->get_infos("pap_delivery_dt", "deli_id", $info['id']);
    $cid = $dtinfo[0]['customer_id'];
    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$cid);
    $cinfo = $db->get_info("pap_customer", "customer_id", $cid);
    $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
    $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id=$cid");
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $typen = json_encode($product_type);
    
    $rec = [];
    foreach($dtinfo as $k=>$v){
        $deli_qty = $db->get_job_mdeli($v['job_name']);
        array_push($rec,array($v['job_name'],$v['qty'],$v['qty']-$deli_qty['qty'],$v['qty']-$deli_qty['qty']));
    }
    $jrec = json_encode($rec);
    $inside = $form->show_text("name","name","","","งานพิมพ์","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070 readonly","min='1' readonly")
            . $form->show_num("remain", "", 1, "", "ค้างส่ง", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("deli", "", 1, "", "ยอดส่ง", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

    $content .= "<h1 class='page-title'>สร้างใบส่งของชั่วคราว</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("dno","dno",$info['no'],"","รหัสใบแจ้งหนี้","","label-inline readonly",null," readonly")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_select("deli_ct", $contacts, "label-inline", "ผู้ติดต่อ", $info['contact'])
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",$info['address'])
            . "<a href='shipping_address.php?cid=$cid' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_mjob_tdeli")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("did","did",$did)
            . $form->show_hidden("pauth","pauth",$pauth)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("check_mdeli(e);")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "mtdeli_function($jrec);"
            . "</script>";
} else if ($action=="edit"&&isset($tdid)){
    /*----------------------------------------------------- EDIT TEMP DELIVERY ------------------------------------------------------------*/
    //loaddata
    $info = $db->get_info("pap_temp_deli", "id", $tdid);
    $dtinfo = $db->get_infos("pap_delivery_dt", "deli_id", $info['deli_id']);
    $ctinfo = $db->get_info("pap_contact","contact_id",$info['contact']);
    $cid = $ctinfo['customer_id'];
    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$cid);
    $cinfo = $db->get_info("pap_customer", "customer_id", $cid);
    $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
    $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id=$cid");
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $typen = json_encode($product_type);
    
    $rec = [];
    foreach($dtinfo as $k=>$v){
        $deli_qty = $db->get_job_mdeli($v['job_name'],$tdid);
        array_push($rec,array($v['job_name'],$v['qty'],$v['qty']-$deli_qty['qty'],$v['qty']-$deli_qty['qty']));
    }
    $jrec = json_encode($rec);
    $inside = $form->show_text("name","name","","","งานพิมพ์","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070 readonly","min='1' readonly")
            . $form->show_num("remain", "", 1, "", "ค้างส่ง", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("deli", "", 1, "", "ยอดส่ง", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

    $content .= "<h1 class='page-title'>สร้างใบส่งของชั่วคราว</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("dno","dno",$info['no'],"","รหัสใบแจ้งหนี้","","label-inline readonly",null," readonly")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_select("deli_ct", $contacts, "label-inline", "ผู้ติดต่อ", $info['contact'])
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",$info['address'])
            . "<a href='shipping_address.php?cid=$cid' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";
    if($pauth >3){
        $del = "<span id='del-temp-deli' class='red-but'>Delete</span>"
                . "<script>del_temp_deli();</script>";
    } else {
        $del = "";
    }
    $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_mjob_tdeli")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("tdid","tdid",$tdid)
            . $form->show_hidden("did","did",$info['deli_id'])
            . $form->show_hidden("pauth","pauth",$pauth)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("check_mdeli(e);")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "mtdeli_function($jrec);"
            . "</script>";
} else {
    /*----------------------------------------------------- VIEW ใบแจ้งหนี้ -------------------------------------------------------------------*/
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);

    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;

    $jdeli = $db->get_job_deli_mm();
    
    //view
    $head = array("ชื่องาน","ลูกค้า","ยอดผลิต","ราคา","ใบแจ้งหนี้","สถานะ","ใบส่งของ");
    $rec = $tbpdo->view_job_mdeli($pauth,$op_job_delivery_icon,$mm,$status,$s, $page, $iperpage);
    $all_rec = $tbpdo->view_job_mdeli($pauth,$op_job_delivery_icon,$mm,$status,$s);
    $addhtml = "";
    if($pauth>1){
        $csvlink = $root."csv_download.php?req=mdeli_csv&month=$mm";
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
    $csv = "<a id='quote-csv' href='$csvlink' title='Download Data'><input type='button' class='blue-but' value='โหลดข้อมูล'/></a>";
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัส หรือชื่องาน",$s)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_delivery, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_mm", $jdeli, $mm,"เดือน")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-mjob-delivery")
            . "<div class='tb-legend'>"
            . my_legend($op_job_delivery,$op_job_delivery_icon)
            . $csv
            . "</div>"
            . "</div><!-- .col-100 -->";

    $content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "</script>";
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;