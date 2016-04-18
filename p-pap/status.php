<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "สถานะการผลิต";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$oid = filter_input(INPUT_GET,'oid',FILTER_UNSAFE_RAW);
$mid = filter_input(INPUT_GET,'mid',FILTER_SANITIZE_NUMBER_INT);

$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = $root."js/order.js";
$menu->ascript[] = $root."js/status.js";
$menu->astyle[] = $root."css/status.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;

$tbpdo = new tbPDO();
$tb = new mytable();
$db = new PAPdb(DB_PAP);
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายผลิต",$pagename);

if(isset($mid)){
/*----------------------------------------------------- RESULT BY MID -------------------------------------------------------------------*/
    $info = $db->get_info("pap_machine", "id", $mid);
    $minfo = $db->get_mach_info($mid);
    $mach = array("0"=>"--เครื่องจักร--")+$db->get_keypair("pap_machine AS mach", "mach.id", "mach.name", "LEFT JOIN pap_process AS pro ON pro.process_id=mach.process_id ORDER BY pro.process_cat_id ASC,mach.name ASC");
    $head = array("งาน","กระบวนการ","ยอด","เวลาผลิต","แผนเริ่ม","แผนเสร็จ","เริ่ม","เสร็จ","ชิ้นงาน","หมายเหตุ");
    $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d");
    $date = (isset($_GET['date'])&&$_GET['date']>0?$_GET['date']:$today);
    $rec = $db->get_job_result_by_mach($mid,$date);
    $inside = $form->show_st_form()
            . "<div id='result-input'></div><!-- #result-input -->"
            . $form->show_submit("submit","ใส่ข้อมูล","but-right")
            . $form->show_hidden("request","request","edit_job_result")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?mid=$mid")
            . $form->show_hidden("mid","mid",$mid)
            . $form->submitscript("$('#papform').submit();");
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . "<h4>เครื่องจักร</h4>"
            . $tb->show_vtable($minfo, "tb-mach-info")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div class='tab-section'>"
            . "<h4>รายเครื่องจักร</h4>"
            . $form->show_select("mach", $mach, "label-inline", "", $mid)
            . $form->show_text("date","date",$date,"","วันที่","","label-inline")
            . "</div><!-- .tab-section -->"
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $form->my_toggle_tab("up-result", "ผลการผลิต", $inside)
            . $tb->show_table($head,$rec,"tb-job-result")
            . "<script>"
            . "status_update();"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "mach_sel('$redirect');"
            . "</script>"
            . "</div><!-- .col-100 -->";
} else if($action =="edit"){
/*----------------------------------------------------- ADD RESULT -------------------------------------------------------------------*/
    __autoload("pdo_report");
    $rp = new reportPDO();
    $info = $rp->rp_order($oid);
    $head = array("ชิ้นส่วน","กระบวนการ","ลักษณะ","ยอด","เวลา<br/>(ชม.)","เครื่องจักร","เวลาเริ่ม","เสร็จ","ชิ้นงาน","หมายเหตุ");
    $rec = $db->get_job_status($oid);
    $head1 = array("ชื่องาน","ชนิด","ยอดพิมพ์","หน้า","ขนาด","กำหนดส่ง","แผนส่งเพลต","เพลตเข้า","แผนส่งกระดาษ","กระดาษเข้า");
    $rec1 = array(array(
        $info['order_no'].":<br/>".$info['name'],
        $info['cat'],
        $info['amount'],
        $info['pages'],
        $info['size'],
        thai_date($info['plan_delivery'],true),
        (isset($info['plate_plan'])?thai_date($info['plate_plan'],true):""),
        (isset($info['plate_received'])?thai_date($info['plate_received'],true):""),
        (isset($info['paper_plan'])?thai_date($info['paper_plan'],true):""),
        (isset($info['paper_received'])?thai_date($info['paper_received'],true):""),
    ));
    $inside = $form->show_st_form()
            . "<div class='col-50'>"
            . "<div id='result-input'></div><!-- #result-input -->"
            . $form->show_submit("submit","ใส่ข้อมูล","but-right")
            . $form->show_hidden("request","request","edit_job_result")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?action=edit&oid=$oid")
            . "</div><!-- .col-50 -->"
            . $form->submitscript("$('#papform').submit();");
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . "<h4>รายละเอียดงาน</h4>"
            . $tb->show_table($head1,$rec1,"tb-job-dt")
            . $form->my_toggle_tab("up-result", "ผลการผลิต", $inside)
            . $tb->show_table_keygroup($head,$rec,"tb-job-status")
            . "<script>"
            . "status_update();"
            . "</script>"
            . "</div><!-- .col-100 -->";
    
} else {
/*----------------------------------------------------- VIEW ORDER -------------------------------------------------------------------*/
    $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']>0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $due = (isset($_GET['fil_due'])&&$_GET['fil_due']>0?$_GET['fil_due']:null);

    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    
    $arrdue = $db->get_job_due();
    
    //view
    $head = array("Update","ชื่องาน","กำหนดส่ง","สภานะรวม","ชิ้นงาน","สถานะ");
    $rec = $tbpdo->view_job_status($pauth,$op_job_prod,$due,$status,$s, $page, $iperpage);
    $all_rec = $tbpdo->view_job_status($pauth,$op_job_prod,$due,$status,$s,null,null,true);
    $max = ceil(count($all_rec)/$iperpage);
    //machine
    $mach = array("0"=>"--เครื่องจักร--")+$db->get_keypair("pap_machine AS mach", "mach.id", "mach.name", "LEFT JOIN pap_process AS pro ON pro.process_id=mach.process_id ORDER BY pro.process_cat_id ASC,mach.name ASC");
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . "<div class='tab-section'>"
            . "<h4>รายเครื่องจักร</h4>"
            . $form->show_select("mach", $mach, "label-inline", "", null)
            . "</div><!-- .tab-section -->"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัส หรือชื่องาน")
            . $tb->show_filter(current_url(), "fil_status", $op_job_prod, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_due", $arrdue, $due,"กำหนดส่ง")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table_keygroup($head,$rec,"tb-pstatus")
            . "</div><!-- .col-100 -->";
    
    $content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "order_search();"
            . "mach_sel('$redirect');"
            . "</script>";
    
    $box = "<h4>เปลียนสถานะชิ้นงาน</h4>"
            . "<div id='box-msg'></div>"
            . $form->show_st_form()
            . "<div id='sel-comp-status'></div>"
            . $form->show_hidden("compid","compid","0")
            . $form->show_hidden("oid","oid","0")
            . $form->show_submit("submit","กำหนด","but-right")
            . $form->show_hidden("request","request","update_comp_status")
            . $form->show_hidden("redirect","redirect",$redirect);
    $box .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "status_update();"
            . "</script>";

    $content .= $form->show_float_box($box,"status-box");
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;