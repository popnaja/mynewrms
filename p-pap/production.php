<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$pagename = "แผนการผลิต";
__autoload("papmenu");
__autoload("pappdo");
__autoload("prod");
__autoload("table");
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = $root."js/production.js";
$menu->astyle[] = $root."css/production.css";
$menu->extrascript = <<<END_OF_TEXT
<style>
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายผลิต",$pagename);

$db = new PAPdb(DB_PAP);
$tb = new mytable();
$form = new myform("papform","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$oid = filter_input(INPUT_GET,'oid',FILTER_SANITIZE_STRING);
$compid = filter_input(INPUT_GET,'compid',FILTER_SANITIZE_NUMBER_INT);
$cproid = filter_input(INPUT_GET,'cproid',FILTER_SANITIZE_NUMBER_INT);
$date = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d");
$plan = new prodPlan($date, "hour");

if($action=="addplan"&&isset($oid)) {
    __autoload("pdo_report");
    $rp = new reportPDO();
/*----------------------------------------------------- ADD PLAN -------------------------------------------------------------------*/
    $content .= "<h1 class='page-title'>กำหนด$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    //show info
    $info = $rp->rp_order($oid);
    $head1 = array("ชื่องาน","ชนิด","ยอดพิมพ์","หน้า","ขนาด","กำหนดส่ง","แผนส่งเพลต","เพลตเข้า","แผนส่งกระดาษ","กระดาษเข้า");
    $rec1 = array(array(
        $info['order_no'].":<br/>".$info['name'],
        $info['cat'],
        number_format($info['amount'],0),
        $info['pages'],
        $info['size'],
        thai_date($info['plan_delivery'],true),
        (isset($info['plate_plan'])?thai_date($info['plate_plan'],true):""),
        (isset($info['plate_received'])?thai_date($info['plate_received'],true):""),
        (isset($info['paper_plan'])?thai_date($info['paper_plan'],true):""),
        (isset($info['paper_received'])?thai_date($info['paper_received'],true):""),
    ));
    $head = array("ชิ้นส่วน","กระบวนการ","ลักษณะ","ยอด","เวลา<br/>(ชม.)","เครื่องจักร","แผนเริ่ม","แผนเสร็จ");
    $rec = $db->get_job_comp_process($oid);
    
    $inside = "<div class='col-100'>"
            . $form->show_st_form()
            . $form->show_text("pc-name","pc-name","","","กระบวนการ","","label-3070 readonly",null,"readonly")
            . "<div id='sel-machine'></div>"
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070","min='1'")
            . $form->show_num("prodtime", "", 0.01, "", "เวลาผลิต(ชม)", "", "label-3070","min='0.01'")
            . "<div style='float:left;padding-left:30%;'>"
            . $form->show_text("stdate","stdate","","yyyy-mm-dd","เริ่ม","","label-inline left-50")
            . $form->show_select("timeh", time_hour(), "label-inline left-25", "HH", null)
            . $form->show_select("timem", time_min(), "label-inline left-25", "MM", null)
            . "</div>"
            . $form->show_hidden("cpid","cpid",0)
            . $form->show_hidden("type","type",0)
            . "<input type='button' id='cancel' value='Cancel' class='form-hide float-left'/>";
    
    $inside .= $form->show_submit("submit","ใส่ลงแผน","but-right")
            . $form->show_hidden("request","request","edit_job_plan")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("oid","oid",$oid)
            . $form->show_hidden("redirect","redirect",$redirect."?action=addplan&oid=$oid")
            . "</div><!-- col-100 -->";
    $form->addformvalidate("ez-msg", array('stdate','amount','prodtime'));
    $inside .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#stdate').datepicker({dateFormat: 'yy-mm-dd'});"
            . "prod_edit();"
            . "</script>";

    $content .= "<div class='col-100'>"
            . "<h4>รายละเอียดงาน</h4>"
            . $tb->show_table($head1,$rec1,"tb-job-dt")
            . $form->my_toggle_tab("myplan-action", "กำหนดแผนการผลิต", $inside)
            . $tb->show_table_keygroup($head,$rec,"tb-job-process")
            . "</div><!-- .col-100 -->";
    //show plan
    $mach = $db->get_mach();
    $schedule = $db->get_schedule($date);
    $result = $db->get_result($date);
    $content .= "<div id='my-plan-div'>"
            . $plan->show_vplan($mach,$schedule,$result)
            . "</div><!-- #my-plan-div -->";
} else if(isset($cproid)){
    $info = $db->get_info("pap_comp_process", "id", $cproid);
    $process = $db->get_keypair("pap_process", "process_id", "process_name","LEFT JOIN pap_process_cat AS cat ON cat.id=process_cat_id ORDER BY cat.id ASC, process_name ASC");
    $pedit = $form->show_select("process",$process , "label-3070", "กระบวนการผลิต",$info['process_id'])
            . $form->show_text("name","name",$info['name'],"","ชื่อกระบวนการ","","label-3070")
            . $form->show_num("amount", $info['volume'], 1, "", "ยอดผลิต", "", "label-3070","min='1'")
            . $form->show_num("prodtime", $info['est_time_hour'], 0.01, "", "เวลาผลิต(ชม)", "", "label-3070","min='0.01'");
    $leveling = $form->show_num("leveling", 0, 1, "", "เฉลี่ยงานเป็น (ส่วน)", "", "label-3070","");
    $content .= "<h1 class='page-title'>แก้ไขกระบวนการผลิต</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_tabs("edit-job-tab", array("แก้ข้อมูล","เกลี่ยงาน"), array($pedit,$leveling));
    if($pauth>2){
        $del = "<span id='del-cpro' class='red-but'>Delete</span>"
                . "<script>del_cpro();</script>";
    } else {
        $del = "";
    }
    $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_cpro")
            . $form->show_hidden("cproid","cproid",$cproid)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?action=addplan&oid=$oid")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name','amount','prodtime'));
    $content .= $form->submitscript("$('#papform').submit();");
    
} else if($action=="add"){
    $info = $db->get_info("pap_order_comp", "id", $compid);
    $process = $db->get_keypair("pap_process", "process_id", "process_name","LEFT JOIN pap_process_cat AS cat ON cat.id=process_cat_id ORDER BY cat.id ASC, process_name ASC");
    $content .= "<h1 class='page-title'>เพิ่มกระบวนการผลิต</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("comp","comp",$info['name'],"","ชิ้นส่วน","","label-3070 readonly",null,"readonly")
            . $form->show_select("process",$process , "label-3070", "กระบวนการผลิต",null)
            . $form->show_text("name","name","","","ชื่อกระบวนการ","","label-3070")
            . $form->show_num("amount", "", 1, "", "ยอดผลิต", "", "label-3070","min='1'")
            . $form->show_num("prodtime", "", 0.01, "", "เวลาผลิต(ชม)", "", "label-3070","min='0.01'");
    $content .= $form->show_submit("submit","Add","but-right")
            . $form->show_hidden("request","request","add_cpro")
            . $form->show_hidden("compid","compid",$compid)
            . $form->show_hidden("redirect","redirect",$redirect."?action=addplan&oid=$oid")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name','amount','prodtime'));
    $content .= $form->submitscript("$('#papform').submit();");
} else {
/*----------------------------------------------------- SHOW PLAN -------------------------------------------------------------------*/
    $mach = $db->get_mach();
    $schedule = $db->get_schedule($date);
    $result = $db->get_result($date);
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . "<div id='my-plan-div'>"
            . $plan->show_vplan($mach,$schedule,$result)
            . "</div><!-- #my-plan-div -->";
}
    
$content .= $menu->showfooter();
echo $content;