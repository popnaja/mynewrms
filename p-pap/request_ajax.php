<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
__autoload("pappdo");
__autoloada("form");
if(!$_POST){
    header("location:".ROOTS);
}
$req = filter_input(INPUT_POST,'request',FILTER_SANITIZE_STRING);
$db = new PAPdb(DB_PAP);
$form = new myform();
/* =======================================================  MEDIA ========================================================================*/
if($req=="show_pic"){
    __autoloada("media");
    $md = new mymedia();
    $dir = dirname(__FILE__)."/image/temp/".date("Y-m")."/";
    $pic = $md->save_tmp_file($_FILES['fileUp'], $dir);
    foreach($pic AS $k=>$v){
        $pic[$k] = str_replace(RDIR,"",$v);
    }
    if($_POST['flag']=="multi"){
        $html = $md->media_mul_view($pic,ROOTS,RDIR);
        echo json_encode(array("append",'md-pics-box',$html));
    } else {
        $html = $md->media_view($pic[0],ROOTS,RDIR);
        echo json_encode(array("replace",'media-pic-box',$html));
    }
} else if($req =="del_pic_file"){
    $ext = pathinfo($_POST['pic'],PATHINFO_EXTENSION);
    $sfile = RDIR.$_POST['pic'];
    $ofile = str_replace("_s.$ext",".$ext",$sfile);
    if(file_exists($sfile)){
        unlink($sfile);
    }
    if(file_exists($ofile)){
        unlink($ofile);
    }
    echo json_encode(array(""));
} else if($req =="up_pdf"){
    __autoloada("media");
    $md = new mymedia();
    $dir = dirname(__FILE__)."/image/temp/".date("Y-m")."/";
    $files = $md->move_temp_file($_FILES['fileUp'], $dir);
    $html = $md->file_view(str_replace(RDIR,"",$files[0]),ROOTS,RDIR);
    echo json_encode(array("replace",'media-file-box',$html));
/* =======================================================  DELETE AJAX ===================================================================*/
} else if($req == "delete_mat_po"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    $arr_oid = $db->get_mm_arr("pap_mat_po_detail", "order_ref", "po_id", $_POST['poid']);
    //delete
    $db->delete_data("pap_mat_po", "po_id", $_POST['poid']);
    //update oid
    foreach($arr_oid as $val){
        $db->check_req_vs_delivery($val);
        $db->check_req_vs_po($val);
    }
    $_SESSION['message'] = "ลบข้อมูลใบสั่งซื้อสำเร็จ";
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_term"){
    __autoloada("term");
    $termdb = new myterm(DB_PAP);
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $termdb->del_term($_POST['tax'], $_POST['tid']);
    if($_POST['tax']=="customer"){
        $_SESSION['message'] = "ลบกลุ่มลูกค้าสำเร็จ";
    } else if($_POST['tax']=="supplier"){
        $_SESSION['message'] = "ลบกลุ่มผู้ผลิตสำเร็จ";
    } else {
        $_SESSION['message'] = "ลบกลุ่มสำเร็จ";
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_process_po"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $db->delete_data("pap_process_po", "po_id", $_POST['poid']);
    $_SESSION['message'] = "ลบข้อมูลใบจ้างผลิตสำเร็จ";
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "del_job_delivery"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //get arr oid
    $arr_oid = $db->get_mm_arr("pap_delivery_dt", "order_id", "deli_id", $_POST['did']);
    //delete
    $db->delete_data("pap_delivery", "id", $_POST['did']);
    //update job status
    foreach($arr_oid as $val){
        $db->update_data("pap_order", "order_id", $val, array("status"=>69)); //69 = พร้อมส่ง
    }
    echo json_encode(array("reload"));
} else if($req == "delete_customer"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $db->delete_data("pap_customer", "customer_id", $_POST['cid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_quote"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $db->delete_data("pap_quotation", "quote_id", $_POST['qid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_process_deli"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $dinfo = $db->get_info("pap_wip_delivery", "id", $_POST['dyid']);
    $db->delete_data("pap_wip_delivery", "id", $_POST['dyid']);
    //check po vs delivery and update po status;
    $db->check_ppo_vs_delivery($dinfo['po_id']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_mat_deli"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $dinfo = $db->get_info("pap_mat_delivery", "id", $_POST['dyid']);
    $arr_oid = $db->get_mm_arr("pap_mat_po_detail", "order_ref", "po_id", $dinfo['po_id']);
    $db->delete_data("pap_mat_delivery", "id", $_POST['dyid']);
    //check po vs delivery and update po status;
    $db->check_po_vs_delivery($dinfo['po_id']);
    //recheck job vs delivery
    foreach($arr_oid as $val){
        $db->check_req_vs_delivery($val);
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_temp_deli"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //get oid info
    $arr_oid = $db->get_mm_arr("pap_temp_dt", "order_id", "temp_deli_id", $_POST['tdid']);
    //get jobname
    $arr_jname = $db->get_mm_arr("pap_temp_dt", "job_name", "temp_deli_id", $_POST['tdid']);
    //delete
    $db->delete_data("pap_temp_deli", "id", $_POST['tdid']);
    //update job status and delivery
    foreach($arr_oid AS $oid){
        if($oid>0){
            $info = $db->get_job_remain_deli($oid);
            if($info['deli']==0){
                $db->update_data("pap_order", "order_id", $oid, array("status"=>69,"delivery"=>null)); //69 = พร้อมส่ง
            } else {
                $db->update_data("pap_order", "order_id", $oid, array("status"=>70)); //70 = ส่งบางส่วน
            }
        }
    }
    //update manual temp deli
    foreach($arr_jname AS $jname){
        if($jname!=""){
            $info = $db->get_job_mdeli($jname);
            $status = ($info['qty']==0?69:70);
            $db->update_data("pap_delivery", "id", $_POST['did'], array("status"=>$status));
        }
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_job_deli"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //get oid info
    $arr_oid = $db->get_mm_arr("pap_delivery_dt", "order_id", "deli_id", $_POST['did']);
    //delete
    $db->delete_data("pap_delivery", "id", $_POST['did']);
    //update job status and delivery
    foreach($arr_oid AS $oid){
        $db->update_data("pap_order", "order_id", $oid, array("status"=>69,"delivery"=>null,"billed"=>null,"invoiced"=>null,"paid"=>0)); //69 = พร้อมส่ง
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_pbill"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //get did info
    $adid = $db->get_mm_arr("pap_pbill_dt", "deli_id", "pbill_id", $_POST['bid']);
    //delete
    $db->delete_data("pap_pbill", "id", $_POST['bid']);

    foreach($adid AS $did){
        //update job status and delivery
        $db->update_data("pap_order", "delivery", $did, array("billed"=>null));
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_invoice"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //get did info
    $adid = $db->get_mm_arr("pap_invoice_dt", "deli_id", "invoice_id", $_POST['ivid']);
    //get receipt info
    $arc = $db->get_mm_arr("pap_rc_dt", "rc_id", "invoice_id", $_POST['ivid']);
    //del receipt
    foreach($arc AS $rcid){
        //delete
        $db->delete_data("pap_rc", "id", $rcid);
    }
    //delete
    $db->delete_data("pap_invoice", "id", $_POST['ivid']);
    foreach($adid AS $did){
        //update delivery status
        $db->update_data("pap_delivery", "id", $did, array("status"=>80)); //80 = มีบแจ้งหนี้
        //update job
        $db->update_data("pap_order", "delivery", $did, array("invoiced"=>null,"paid"=>0));
    }
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_receipt"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $db->delete_data("pap_rc", "id", $_POST['rcid']);
    $adid = $db->get_mm_arr("pap_invoice_dt", "deli_id", "invoice_id", $_POST['ivid']);
    update_job_paid($adid);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "delete_cpro"){
    //log
    $db->pap_log($_SESSION['upap'][0], $req, json_encode($_POST));
    //delete
    $db->delete_data("pap_comp_process", "id", $_POST['cproid']);
    echo json_encode(array("redirect",$_POST['redirect']));
/* =======================================================  CALENDAR AJAX ===================================================================*/
} else if($req == "meet_month"){
    __autoloada("calendar");
    $pauth = page_auth(basename(current_url()));
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_meet_schedule($pauth,$_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "meet_type"){
    __autoloada("calendar");
    $pauth = page_auth(basename(current_url()));
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_meet_schedule($pauth,$_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "mycd_change_month"){
    __autoloada("calendar");
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_job_schedule($_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "mycd_change_type"){
    __autoloada("calendar");
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_job_schedule($_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "plate_cd_month"){
    __autoloada("calendar");
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_plate_sch($op_job_code,$_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "plate_cd_type"){
    __autoloada("calendar");
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $db->get_plate_sch($op_job_code,$_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "mycd_bill_month"){
    __autoloada("calendar");
    include_once(dirname(__FILE__)."/pdo/pdo_ac.php");
    $pdo_ac = new pdo_ac();
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $pdo_ac->get_bill_check($_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "mycd_bill_type"){
    __autoloada("calendar");
    include_once(dirname(__FILE__)."/pdo/pdo_ac.php");
    $pdo_ac = new pdo_ac();
    $cd = new mycalendar($_POST['year'], $_POST['month']);
    $data = $pdo_ac->get_bill_check($_POST['year'], $_POST['month']);
    $html = $cd->show_calendar($data,$_POST['type'],$_POST['week'],"mycd_change('".$_POST['req']."');");
    echo json_encode(array("html_replace","mycd-div",$html));
} else if($req == "plan_change_month"){
    __autoload("prod");
    $plan = new prodPlan($_POST['date'], $_POST['type']);
    $mach = $db->get_mach();
    $schedule = $db->get_schedule($_POST['date']);
    $result = $db->get_result($_POST['date']);
    $html = $plan->show_vplan($mach, $schedule,$result);
    echo json_encode(array("html_replace","my-plan-div",$html));
} else if($req == "get_mach"){
    $mach = $db->get_keypair("pap_machine", "id", "name", "WHERE process_id=".$_POST['pid']);
    $html = $form->show_select("mcid", $mach, "label-3070", "หน่วยผลิต", null);
    echo json_encode(array("html_replace",$_POST['target'],$html));
/* =======================================================  OTHERS AJAX ===================================================================*/
} else if($req =="get_process_info"){
    $info = $db->get_info("pap_process","process_id",$_POST['pid']);
    $html = $form->show_text("unit", "unit", $op_unit[$info['process_unit']], "", "หน่วย", "", "label-3070 readonly", null, "readonly")
            . $form->show_num("setup_min",$info['process_setup_min'],1,"","ตั้งเครื่อง(นาที)","","label-3070")
            . $form->show_num("capacity",$info['process_cap'],1,"","กำลังการผลิต","(หน่วย/ชั่วโมง)","label-3070");
    echo json_encode(array("html_replace",$_POST['target'],$html));
} else if($req == "get_sup_ct"){
    $sid = $_POST['sid'];
    $ct = $db->get_keypair("pap_supplier_ct", "id", "name", "WHERE supplier_id='$sid'");
    $html = $form->show_select("sup_ct", $ct, "label-3070", "ผู้ติดต่อ", null);
    echo json_encode(array("html_replace",$_POST['target'],$html));
} else if($req=="find_job"){
    $res = $db->find_job($_POST['f']);
    echo json_encode($res);
} else if($req=="find_user_email"){
    $res = $db->find_uemail($_POST['f']);
    echo json_encode($res);
} else if($req=='send_email'){
    include_once(dirname(dirname(__FILE__))."/p-admin/email_function.php");
    __autoloada("table");
    __autoload("pdo_tb");

    $tb = new mytable();
    $tbpdo = new tbPDO();
    $head = array("วันที่","บันทึก");
    $rec = $tbpdo->view_note($_POST['pauth'],$_POST['cid'],$_POST['uid']);
    $uinfo = $db->get_info("pap_user","user_id",$_POST['uid']);
    $cinfo = $db->get_info("pap_customer","customer_id",$_POST['cid']);
    $ct = "<h1 style='font-size:18px;color:#444;'>รายงานบันทึกการติดต่อลูกค้า ".$cinfo['customer_code']." : ".$cinfo['customer_name']."</h1>"
            . "<p style='font-size:16px;color:#444;'>โดย ".$uinfo['user_login']
            . $tb->show_email_table($head,$rec,"tb-note");
    $body = note_email($_POST['subject'], $ct);
    php_mailer_ndh($_POST['email'], $uinfo['user_email'], $_POST['subject'], $ct);
    echo json_encode(array("myOK","Message","ส่งอีเมลสำเร็จ"));
} else if($req=="update_quote_report"){
    __autoloada("table");
    __autoload("pdo_report");
    $tb = new mytable();
    $rp = new reportPDO();
    $rec = $rp->report_quote($_POST['cid'], $op_quote_status_icon,$_POST['month']);
    $html = $tb->show_table(array("สถานะ","จำนวน","ยอดรวม"),$rec);
    echo json_encode(array("html_replace","quote_rp",$html));
} else if($req == "find_quote"){
    $res = $db->find_quote($_POST['f']);
    echo json_encode($res);
} else if($req == "find_mat"){
    $res = $db->find_mat($_POST['f']);
    echo json_encode($res);
} else if($req == "find_process"){
    $res = $db->find_process($_POST['f']);
    echo json_encode($res);
} else if($req == "find_cproid"){
    $res = $db->find_cproid($_POST['f']);
    echo json_encode($res);
} else if($req == "find_customer"){
    $res = $db->find_customer($_POST['f']);
    echo json_encode($res);
} else if($req == "get_contact_ad"){
    $cid = $_POST['cid'];
    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$cid);
    $cinfo = $db->get_info("pap_customer", "customer_id", $cid);
    $ad[0] = $cinfo['customer_name']."<br/>".$cinfo['customer_address'];
    $address = $ad+$db->get_keypair("pap_cus_ad", "id", "CONCAT(name,'<br/>',address)", "WHERE customer_id=$cid");
    $html = $form->show_select("deli_ct", $contacts, "label-inline", "ผู้ติดต่อ", null)
            . $form->show_radio("address", $address, "radio-inline", "ที่อยู่จัดส่ง",0)
            . "<a href='shipping_address.php?cid=$cid' title='เพิ่มที่อยู่จัดส่ง'>เพิ่มที่อยู่จัดส่ง</a>"
            . $form->show_num("credit", $cinfo['customer_credit_day'], 1, "", "เครดิต(วัน)", "", "label-inline","min=0");
    echo json_encode(array("html_replace","cus_info",$html));
} else if($req == "find_size"){
    $res = $db->find_size($_POST['f']);
    echo json_encode($res);
} else if($req == "check_remain_deli"){
    $tdid = filter_input(INPUT_POST,'tdid',FILTER_SANITIZE_NUMBER_INT);
    $remain = $db->get_job_remain_deli($_POST['oid'],$tdid);
    $html = $form->show_num("remain", $remain['remain'], 1, "", "ยอดค้างส่ง", "", "label-inline readonly","min=1 readonly");
    echo json_encode(array("html_replace","ip-remain",$html));
} else if($req == "get_sel_comp_status"){
    $compid = $_POST['compid'];
    $compstatus = $db->get_keypair("pap_comp_process AS cpro", "cat.id", "cat.name", "LEFT JOIN pap_process AS pro ON pro.process_id=cpro.process_id LEFT JOIN pap_process_cat AS cat ON cat.id=pro.process_cat_id WHERE cpro.comp_id=$compid AND cat.id<12 GROUP BY cat.id");
    $info = $db->get_info("pap_order_comp", "id", $compid);
    $html = $form->show_select("status", $compstatus, "label-3070", "สถานะชิ้นงาน", $info['status']);
    echo json_encode(array("html_replace","sel-comp-status",$html));
} else if($req == "get_sel_main_status"){
    $mainst = array(
      "7" => "Plate Ready",
      "8" => "พร้อมพิมพ์",
      "9" => "พิมพ์",
      "19" => "หลังพิมพ์",
      "69" => "พร้อมส่ง"
    );
    $info = $db->get_info("pap_order","order_id",$_POST['oid']);
    $html = $form->show_select("status", $mainst, "label-3070", "สถานะงาน", $info['status']);
    echo json_encode(array("html_replace","sel-comp-status",$html));
} else if($req == "get_start_status"){
    $info = $db->get_info("pap_comp_process", "id", $_POST['cproid']);
    $planst = (is_null($info['plan_start'])?"":thai_dt($info['plan_start']));
    if(isset($info['start'])){
        $date = new DateTime($info['start'],new DateTimeZone("Asia/Bangkok"));
    } else {
        $date = new DateTime(null,new DateTimeZone("Asia/Bangkok"));
    }
    $html = "<div class='col-50'>"
            . $form->show_text("pc-name","pc-name",$info['name'],"","กระบวนการ","","label-inline readonly",null,"readonly")
            . $form->show_text("pstart","pstart",$planst,"","แผนเริ่มผลิต","","label-inline readonly",null,"readonly")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $form->show_text("stdate","stdate",$date->format("Y-m-d"),"","เริ่มผลิต","","label-inline left-50")
            . $form->show_select("timeh", time_hour(), "label-inline left-25", "HH", $date->format("H"))
            . $form->show_select("timem", time_min(), "label-inline left-25", "MM", $date->format("i"))
            . $form->show_hidden("cproid","cproid",$_POST['cproid'])
            . $form->show_hidden("type","type","start")
            . "<script>$('#stdate').datepicker({dateFormat: 'yy-mm-dd'});</script>"
            . "</div><!-- .col-50 -->";
    echo json_encode(array("html_replace",$_POST['target'],$html));
} else if($req == "get_end_status"){
    $info = $db->get_info("pap_comp_process", "id", $_POST['cproid']);
    $planen = (is_null($info['plan_end'])?"":thai_dt($info['plan_end']));
    $now = new DateTime(null,new DateTimeZone("Asia/Bangkok"));
    if(isset($info['end'])){
        $end = new DateTime($info['end'],new DateTimeZone("Asia/Bangkok"));
        $date = $end->format("Y-m-d");
        $h = $end->format("H");
        $m = $end->format("i");
    } else {
        $date = $now->format("Y-m-d");
        $h = $now->format("H");
        $m = $now->format("i");
    }
    $html = "<div class='col-50'>"
            . $form->show_text("pc-name","pc-name",$info['name'],"","กระบวนการ","","label-inline readonly",null,"readonly")
            . $form->show_text("pend","pend",$planen,"","แผนผลิตเสร็จ","","label-inline readonly",null,"readonly")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $form->show_text("endate","endate",$date,"","ผลิตเสร็จ","","label-inline left-50")
            . $form->show_select("timeh",time_hour(), "label-inline left-25", "HH", $h)
            . $form->show_select("timem",time_min(), "label-inline left-25", "MM", $m)
            . $form->show_num("result", (isset($info['result'])?$info['result']:""), 1, "", "ชิ้นงานสำเร็จ", "", "label-inline","min='1'")
            . $form->show_textarea("remark",(isset($info['remark'])?$info['remark']:""), 4, 10, "", "หมายเหตุ", "label-inline")
            . $form->show_hidden("cproid","cproid",$_POST['cproid'])
            . $form->show_hidden("type","type","end")
            . "<script>$('#endate').datepicker({dateFormat: 'yy-mm-dd'});</script>"
            . "</div><!-- .col-50 -->";
    echo json_encode(array("html_replace",$_POST['target'],$html));
} else if($req == "check_mjob_name"){
    if($db->check_dup("pap_delivery_dt", "job_name", $_POST['name'], " order_id=0")){
        echo json_encode("dup");
    } else {
        echo json_encode("ok");
    }
}
function update_job_paid($adid){
    global $db;
    include_once("pdo/pdo_ac.php");
    $pdo_ac = new pdo_ac();
    foreach($adid as $key=>$did){
        $info = $pdo_ac->check_job_paid($did);
        foreach($info as $k=>$v){
            $paid_before_tax = $v['paid']/($v['tax_ex']=="yes"?0.97:1.04);
            $opaid = $paid_before_tax*$v['price']/$v['total'];
            $db->update_data("pap_order", "order_id", $v['oid'], array("paid"=>$opaid));
            $paid = $paid_before_tax;
            $tt = $v['total'];
        }
        if($paid==$tt){
            //update deli status
            $db->update_data("pap_delivery", "id", $did, array("status"=>99)); //99 = ชำระครบ
        } else {
            $db->update_data("pap_delivery", "id", $did, array("status"=>98)); //99 = ชำระบางส่วน
        }
    }
}
