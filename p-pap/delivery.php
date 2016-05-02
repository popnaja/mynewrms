<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
include_once("ud/doc_default.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "ใบส่งของ/แจ้งหนี้";
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
    /*----------------------------------------------------- CREATE DELIVERY ---------------------------------------------------------------*/
    //loaddata
    $aoid = explode(",",$oid);
    $rec = [];
    $acid = [];
    foreach($aoid as $k=>$v){
        $info = $db->get_job_remain_deli($v);
        array_push($rec,array($v,$info['order_no'].":".$info['name'],$info['amount'],$info['deli']));
        array_push($acid,$info['customer_id']);
    }

    $str_cid = implode(",",$acid);
    $cinfo = $db->get_info("pap_customer", "customer_id", $acid[0]);
    $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
    $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id IN ($str_cid)");
    $ct = $db->get_keypair("pap_contact", "contact_id", "contact_name", "WHERE customer_id IN ($str_cid)");

    $inside = $form->show_text("oref","oref","","","งานพิมพ์","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดค้างส่ง", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("deli", "", 1, "", "ยอดส่ง", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_select("deli_ct", $ct, "label-inline", "ผู้ติดต่อ", null)
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",0)
            . "<a href='shipping_address.php?cid=$acid[0]' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_text("date","date","","yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-inline")
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_job_deli")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- col-50 -->";
    $form->addformvalidate("ez-msg", array('date'));
    $content .= $form->submitscript("check_deli(e);")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "deli_function(".json_encode($rec).");"
            . "</script>";
} else if($action == "edit"){
/*----------------------------------------------------- EDIT DELIVERY -------------------------------------------------------------------*/
    if(isset($did)){
        $info = $db->get_info("pap_delivery", "id", $did);

        $st_cid = $db->get_acid_from_oid($oid);
        $acid = explode(",",$st_cid);
        $cinfo = $db->get_info("pap_customer", "customer_id", $acid[0]);
        $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
        $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id IN ($st_cid)");
        $ct = $db->get_keypair("pap_contact", "contact_id", "contact_name", "WHERE customer_id IN ($st_cid)");

        $content .= "<h1 class='page-title'>แก้ไขใบแจ้งหนี้</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_select("deli_ct", $ct, "label-inline", "ผู้ติดต่อ", $info['contact'])
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",$info['address'])
            . "<div><a href='shipping_address.php?cid=$acid[0]' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a></div>"
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline");
        if($pauth == 4){
            $del = "<span id='del-job-deli' class='red-but'>Delete</span>"
                    . "<script>del_job_deli();</script>";
        } else {
            $del = "";
        }
        $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_job_deli")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("did","did",$did)
            . "</div><!-- col-50 -->";
        $form->addformvalidate("ez-msg", array('date'));
        $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
        $content .= show_deli($did);
/*----------------------------------------------------- EDIT TEMP DELIVERY --------------------------------------------------------------*/
    } else if(isset($tdid)){
        $info = $db->get_info("pap_temp_deli", "id", $tdid);
        $ddt = $db->get_keypair("pap_temp_dt", "order_id", "qty", "WHERE temp_deli_id=$tdid");

        $st_cid = $db->get_acid_from_oid($oid);
        $acid = explode(",",$st_cid);
        $cinfo = $db->get_info("pap_customer", "customer_id", $acid[0]);
        $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
        $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id IN ($st_cid)");
        $ct = $db->get_keypair("pap_contact", "contact_id", "contact_name", "WHERE customer_id IN ($st_cid)");


        $aoid = explode(",",$oid);
        $rec = [];
        foreach($aoid as $k=>$v){
            $tinfo = $db->get_job_remain_deli($v,$tdid);
            array_push($rec,array($v,$tinfo['order_no'].":".$tinfo['name'],$tinfo['amount'],$tinfo['deli'],$ddt[$v]));
        }
        $inside = $form->show_text("oref","oref","","","งานพิมพ์","","label-3070 readonly",null,"readonly")
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("remain", "", 1, "", "ยอดค้างส่ง", "", "label-3070 readonly","min='0' readonly")
            . $form->show_num("deli", "", 1, "", "ยอดส่ง", "", "label-3070","min=1")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-list' value='เพิ่มลงรายการ'/>";

        $content .= "<h1 class='page-title'>แก้ไขใบส่งของ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_select("deli_ct", $ct, "label-inline", "ผู้ติดต่อ", $info['contact'])
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",$info['address'])
            . "<a href='shipping_address.php?cid=$acid[0]' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ส่ง","","label-inline")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline")
            . $form->my_toggle_tab("add-more-deli", "รายการ", $inside)
            . "<div id='deli-list'></div>";
        if($pauth == 4){
            $del = "<span id='del-temp-deli' class='red-but'>Delete</span>"
                    . "<script>del_temp_deli();</script>";
        } else {
            $del = "";
        }
        $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_job_tdeli")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("tdid","tdid",$tdid)
            . $form->show_hidden("did","did",$info['deli_id'])
            . "</div><!-- col-50 -->";
        $form->addformvalidate("ez-msg", array('date'));
        $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "deli_function(".json_encode($rec).");"
            . "</script>";
    }
} else {
    /*----------------------------------------------------- VIEW ORDER -------------------------------------------------------------------*/
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);

    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;

    $jdeli = $db->get_job_deli_mm();

    //view
    $head = array("ชื่องาน","ลูกค้า","กำหนดส่ง","ยอดผลิต","รวม","สร้าง","ใบแจ้งหนี้","ใบส่งของ");
    $rec = $tbpdo->view_job_deli($pauth,$op_job_delivery_icon,$mm,$status,$s, $page, $iperpage);
    $all_rec = $tbpdo->view_job_deli($pauth,$op_job_delivery_icon,$mm,$status,$s);
    if($pauth>1){
        $csvlink = $root."csv_download.php?req=deli_csv&month=$mm";
    }
    $csv = "<a id='quote-csv' href='$csvlink' title='Download Data'><input type='button' class='blue-but' value='โหลดข้อมูล'/></a>";
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัส หรือชื่องาน",$s)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_delivery, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_mm", $jdeli, $mm,"เดือน")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-job-delivery")
            . "<div class='tb-legend'>"
            . my_legend($op_job_delivery,$op_job_delivery_icon)
            . $csv
            . "</div>"
            . $form->show_button("mix-deli", "สร้างใบส่งของรวม", "float-right")
            . "</div><!-- .col-100 -->";

    $content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "order_search();"
            . "mix_deli();"
            . "delete_deli();"
            . "</script>";
}

$content .= ($action=="print"?"":$menu->showfooter());
echo $content;
