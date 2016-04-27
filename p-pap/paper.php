<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "ใบสั่งซื้อวัตถุดิบ";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
include_once("pdo/pdo_po.php");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$oid = filter_input(INPUT_GET,'oid',FILTER_SANITIZE_NUMBER_INT);
$poid = filter_input(INPUT_GET,'poid',FILTER_SANITIZE_NUMBER_INT);
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
    $content = $menu->showhead()
            . show_mat_po($poid)
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
$menu->ascript[] = $root."js/pap.js";
$menu->ascript[] = $root."js/mat_order.js";
$menu->ascript[] = $root."js/order.js";
$menu->astyle[] = $root."css/mat_order.css";
$menu->astyle[] = $root."css/status.css";

$menu->extrascript = <<<END_OF_TEXT

END_OF_TEXT;

$pdo_po = new pdo_po();
$tb = new mytable();
$db = new PAPdb(DB_PAP);
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$content = $menu->showhead();
if($action == "viewpo"||isset($poid)||$action=="po"){
    $content .= $menu->pappanel("ฝ่ายจัดซื้อ",$pagename);
} else {
    $content .= $menu->pappanel("ฝ่ายจัดซื้อ","สั่งกระดาษ");
}

if($action=="po"){
    /*----------------------------------------------------- CREATE PO -------------------------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //prep pre-order
    if(isset($_SESSION['upap']['po'])){
        $porder = $_SESSION['upap']['po'];
        unset($_SESSION['upap']['po']);
        $pre = array();
        foreach($porder as $v){ //[oid,mid,rim]
            $od = explode(",",$v);
            $info = $db->porder_info($od[0], $od[1]);
            array_push($pre,array($od[1],$info['mname'],$od[0],$info['order'],$od[2],round($info['mcost']*500),2));
        }
        $jpre = "po_function(".json_encode($pre).");";
    } else {
        $jpre = "po_function();";
    }

    $supplier = array("0"=>"--ผู้ผลิต--")+$db->get_keypair("pap_supplier", "id", "CONCAT(code,':',name)");
    $mat = $db->get_keypair("pap_mat", "mat_id", "CONCAT(mat_name,' (ขนาดบรรจุ ',mat_order_lot_size,mat_unit,')')","ORDER BY mat_name ASC");
    $content .= "<h1 class='page-title'>เปิดใบสั่งวัตถุดิบ</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='form-box'><div class='col-50'>"
        . $form->show_select("supplier",$supplier,"label-3070","ผู้ผลิต $req",null)
        . "<div id='sup_ct_box'></div>"
        . $form->show_text("due","due","","yyyy-mm-dd","กำหนดส่ง $req","","label-3070")
        . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-3070")
        . $form->show_hidden("status","status",1)
        . "</div><!-- .col-50 -->"
        . "</div><!-- .form-box -->";

    $header = array("ลบ","แก้ไข","รายการ","อ้างอิง","จำนวน","ราคา/ขนาดบรรจุ","ราคา");
    $rec = array();
    $inside = "<div id='add-mat-msg'></div>"
            . $form->show_text("mat_auto","mat_auto","","ค้นหาวัตถุดิบ 3 ตัวอักษรขึ้นไป","วัตถุดิบ $req","","label-3070")
            . $form->show_text("oref","oref","","ค้นหางานอ้างอิง 3 ตัวอักษรขึ้นไป","อ้างอิง","","label-3070")
            . $form->show_num("amount", "", 0.002, "", "จำนวน $req", "กระดาษเป็นริม, 1 ริม = 500แผ่น", "label-3070")
            . $form->show_num("cost", "", 0.001, "", "ราคา/หน่วย $req", "กระดาษเป็นราคา/ริม", "label-3070")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-mat' value='เพิ่มลงรายการ'/>";
    $content .= $form->my_toggle_tab("add-more-mat", "รายการสั่งซื้อ", $inside)
            . "<div id='po-list'>"
            . $tb->show_table($header,$rec,"tb-po-list")
            . "</div>";
    $content .= $form->show_submit("submit","Add New","but-right")
        . $form->show_hidden("request","request","add_mat_po")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('due'),null,null,array('supplier'));
    $content .= $form->submitscript("check_po(e);")
            . "<script>"
            . "$('#due').datepicker({dateFormat: 'yy-mm-dd'});"
            . $jpre
            . "search_job('oref','".PAP."request_ajax.php');"
            . "sel_supplier();"
            . "</script>";
} else if(isset($poid)){
    /* --------------------------------------------------------   EDIT PO --------------------------------------------------------------*/
    //check
    if($pauth<2){
        header("location:$redirect");
        exit();
    }
    //load info
    $info = $db->get_info("pap_mat_po", "po_id", $poid);

    //prep order
    $dt = $db->get_infos("pap_mat_po_detail", "po_id", $poid);
    $pre = array();
    foreach($dt as $k=>$v){
        $oid = $v['order_ref'];
        $mid = $v['mat_id'];
        $order = $db->porder_info($oid, $mid);
        $ref = (isset($order['order'])?$order['order']:"");
        array_push($pre,array($mid,$order['mname'],$oid,$ref,$v['mat_qty'],$v['mat_cost']));
    }
    $jpre = "po_function(".json_encode($pre).");";


    $supplier = array("0"=>"--ผู้ผลิต--")+$db->get_keypair("pap_supplier", "id", "CONCAT(code,':',name)");
    $ct = $db->get_keypair("pap_supplier_ct", "id", "name", "WHERE supplier_id='".$info['supplier_id']."'");
    $st = $op_po_status;
    unset($st[4]);
    unset($st[5]);
    unset($st[9]);

    $content .= "<h1 class='page-title'>แก้ไข $pagename</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . "<div class='col-100'>"
        . $form->show_st_form()
        . "<div class='form-box'><div class='col-50'>"
        . $form->show_text("pocode","pocode",$info['po_code'],"","รหัส PO","","label-3070 readonly",null,"readonly")
        . $form->show_select("supplier",$supplier,"label-3070","ผู้ผลิต $req",$info['supplier_id'])
        . "<div id='sup_ct_box'>"
        . $form->show_select("sup_ct", $ct, "label-3070", "ผู้ติดต่อ", $info['ct_id'])
        . "</div>"
        . $form->show_text("due","due",$info['po_delivery_plan'],"yyyy-mm-dd","กำหนดส่ง $req","","label-3070")
        . $form->show_num("payment", $info['po_payment'], 1, "", "การชำระเงิน", "0 คือ เงินสด, มากกว่า 0 คือจำนวนวันเครติต", "label-3070")
        . $form->show_textarea("remark",$info['po_remark'],4,10,"","หมายเหตุ","label-3070")
        . "</div><!-- .col-50 -->"
        . "<div class='col-50'>"
        . "<div class='form-section'>"
        . "<h4>เปลี่ยนสถานะใบ PO</h4>"
        . $form->show_select("status", $st , "label-3070", "สถานะ", $info['po_status'])
        . "<input type='button' class='blue-but' value='Update' style='float:right' onClick='submit2()' />"
        . "</div><!-- .form-section -->"
        . "</div><!-- .col-50 -->"
        . "</div><!-- .form-box -->";

    $inside = $form->show_text("mat_auto","mat_auto","","ค้นหา 3 ตัวอักษรขึ้นไป","วัตถุดิบ $req","","label-3070")
            . $form->show_text("oref","oref","","ค้นหา 3 ตัวอักษรขึ้นไป","อ้างอิง $req","","label-3070")
            . $form->show_num("amount", "", 0.002, "", "จำนวน $req", "กระดาษหน่วยเป็น ริม, 1 ริม = 500แผ่น", "label-3070")
            . $form->show_num("cost", "", 0.001, "", "ราคา/หน่วย $req", "กระดาษใช้ราคาต่อริม", "label-3070")
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>"
            . "<input type='button' id='add-mat' value='เพิ่มลงรายการ'/>";
    $header = array("ลบ","แก้ไข","รายการ","อ้างอิง","จำนวน","ราคา/ขนาดบรรจุ","ราคา");
    $rec = array();
    $content .= $form->my_toggle_tab("add-more-mat", "รายการสั่งซื้อ", $inside)
            . "<div id='po-list'>"
            . $tb->show_table($header,$rec,"tb-po-list")
            . "</div>";
    if($pauth==4){
        $del = "<span id='del-mat-po' class='red-but'>Delete</span>"
                    . "<script>del_mat_po();</script>";
    } else {
        $del = "";
    }
    $content .= $del
        . $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","edit_mat_po")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . $form->show_hidden("redirect","redirect",$redirect)
        . $form->show_hidden("poid","poid",$poid);
    $form->addformvalidate("ez-msg", array('due'),null,null,array('supplier'));
    $content .= $form->submitscript("check_po(e);")
            . "<script>"
            . "$('#due').datepicker({dateFormat: 'yy-mm-dd'});"
            . $jpre
            . "search_job('oref','".PAP."request_ajax.php');"
            . "</script>"
            . "</div><!-- .col-100 -->";
} else if($action=="viewpo"){
    /* --------------------------------------------------------   VIEW PO --------------------------------------------------------------*/
    //GET
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $iperpage = 20;

    //view
    $head = array("รหัส","รหัส Supplier","วันที่สั่ง","สถานะ");
    $rec = $pdo_po->view_po($pauth,$op_po_status_icon, $status, $mm, $s,$page, $iperpage);
    $all_rec = $pdo_po->view_po($pauth,$op_po_status_icon, $status, $mm,$s);
    $max = ceil(count($all_rec)/$iperpage);

    $arrmm = $db->get_keypair("pap_mat_po", "DATE_FORMAT(po_created,'%m%Y')", "DATE_FORMAT(po_created,'%b-%Y')", "");
    $new = "";
    if($pauth>1){
        array_unshift($head, "แก้ไข");
        $new = "<a href='$redirect?action=po' title='Add new'>Add New</a>";
    }

    $content .= "<h1 class='page-title'>$pagename $new</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาจากรหัสใบสั่งวัตถุดิบ",$s)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_po_status, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_mm", $arrmm, $mm,"เดือน")
            . "<div class='tb-clear-filter'><a href='$redirect?action=viewpo' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-po")
            . "<div class='tb-legend'>"
            . my_legend($op_po_status,$op_po_status_icon)
            . "</div>"
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "delete_po();"
            . "</script>";
} else {
    /*----------------------------------------------------- VIEW ORDER AND ORDER PAPER ----------------------------------------------*/
    $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);

    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 10;

    //view
    $head = array("รหัสงาน","ชื่อ","ยอดผลิต","กำหนดส่ง","สถานะงาน","กระดาษ");
    $rec = $pdo_po->view_purchase($pauth,$op_job_status,$status,$s, $page, $iperpage);
    $addhtml = "";

    $all_rec = $pdo_po->view_purchase($pauth,$op_job_status,$status,$s);
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . $addhtml
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัสหรือชื่อ",$s)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_prod, $status,"สถานะ")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table_keygroup($head,$rec,"tb-matorder",";");
    if($pauth>1){
        $content .= $form->show_submit("submit","เปิดใบสั่งซื้อ","but-right");
    }
    $content .= $form->show_hidden("request","request","po_paper")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?action=po")
            . $form->submitscript("$('#papform').submit();")
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "order_search();"
            . "</script>";
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;
