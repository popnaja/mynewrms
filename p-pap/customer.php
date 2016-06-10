<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$uid = $_SESSION['upap'][0];
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | Customer";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->ascript[] = AROOTS."js/chart.js";
$menu->ascript[] = "https://www.gstatic.com/charts/loader.js";
$menu->ascript[] = $root."js/pap.js";
$menu->ascript[] = $root."js/acc.js";
$menu->ascript[] = $root."js/customer.js";
$menu->astyle[] = $root."css/customer.css";
$menu->astyle[] = $root."css/status.css";


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$cid = filter_input(INPUT_GET,'cid',FILTER_SANITIZE_STRING);
if($action == "note"){
    
} else {
    $content = $menu->showhead();
    $content .= $menu->pappanel("ลูกค้า","รายการลูกค้า");
}
__autoloada("term");
$termdb = new myterm(DB_PAP);
$cats = $termdb->get_parent("customer");

$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$md = new mymedia(PAP."request_ajax.php");
$tbpdo = new tbPDO();
$tb = new mytable();

$sale = array("-1"=>"ไม่กำหนด")+$db->get_keypair("pap_user", "pap_user.user_id", "user_login", "LEFT JOIN pap_usermeta AS um ON um.user_id=pap_user.user_id AND meta_key='user_auth' WHERE meta_value='17'");
if($action=="add"){
/*-----------------------------------------------------------------------------ADD ------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $content .= "<h1 class='page-title'>เพิ่มฐานข้อมูลลูกค้า</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อบริษัท $req","","label-3070")
            . $form->show_text("taxid","taxid","","","เลขทะเบียนการค้า (Tax ID)","","label-3070")
            . $form->show_text("branch","branch","สำนักงานใหญ่","","สำนักงาน","","label-3070")
            . $form->show_select("ntax",array("no"=>"ไม่ได้รับการยกเว้นภาษี","yes"=>"ยกเว้นภาษี"),"label-3070","องค์กรณ์ที่ได้รับการยกเว้นภาษี",null)
            . $form->show_select("cat",$cats,"label-3070","กลุ่มลูกค้า $req",null)
            . $form->show_select("status",$op_cus_status,"label-3070","สถานะลูกค้า",null)
            . $form->show_textarea("address","",4,10,"","ที่อยู่ $req","label-3070")
            . $form->show_text("url","url","","","เว็บไซต์","","label-3070")
            . $form->show_text("email","email","","","อีเมล","","label-3070","email")
            . $form->show_text("tel","tel","","","โทรศัพท์ $req","","label-3070")
            . $form->show_text("fax","fax","","","โทรสาร","","label-3070")
            . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-3070")
            . $form->show_select("pay",$op_payment,"label-3070","การชำระเงิน",null)
            . "<div class='sel-pay-1'>"
            . $form->show_num("credit_day","",1,"","Credit(วัน)","","label-3070")
            . $form->show_num("credit","",1,"","วงเงิน(บาท)","","label-3070")
            . $form->show_select("bill",$op_bill_type,"label-3070","วันวางบิล",null)
            . "<div class='sel-bill-2'>"
            . $form->show_text("bill_day","bill_day","","","วันที่","","label-3070")
            . "</div><!-- .sel-bill-2 -->"
            . "<div class='sel-bill-3'>"
            . $form->show_text("bill_weekday","bill_weekday","","","วันของสัปดาห์","","label-3070")
            . $form->show_text("bill_week","bill_week","","","สัปดาห์ที่","","label-3070")
            . "</div><!-- .sel-bill-3 -->"
            . $form->show_text("bill_remark","bill_remark","","","หมายเหตุวันวางบิล","","label-3070")
            . $form->show_select("cheque",$op_cheque_type,"label-3070","วันรับเช็ค",null)
            . "<div class='sel-cheque-2'>"
            . $form->show_text("cheque_day","cheque_day","","","วันที่","","label-3070")
            . "</div><!-- .sel-cheque-2 -->"
            . "<div class='sel-cheque-3'>"
            . $form->show_text("cheque_weekday","cheque_weekday","","","วันของสัปดาห์","","label-3070")
            . $form->show_text("cheque_week","cheque_week","","","สัปดาห์ที่","","label-3070")
            . "</div><!-- .sel-cheque-3 -->"
            . $form->show_text("cheque_remark","cheque_remark","","","หมายเหตุวันรับเช็ค","","label-3070")
            . "</div><!-- .sel-pay-1 -->"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>";
    
    //pic-tab
    $pictab = $md->show_minput("cpics","cpics","");
    
    //form-tab
    if($pauth>3){
        $saletab = $form->show_select("sale",$sale,"label-3070","Sale รับผิดชอบ",null);
        $content .= $form->show_tabs("cus-tab",array("Sales","ภาพประกอบ"),array($saletab,$pictab),0);
    } else {
        $content .= $form->show_tabs("cus-tab",array("ภาพประกอบ"),array($pictab))
            . $form->show_hidden("sale","sale",$uid);
    }
    //Contact person
    for($i=0;$i<5;$i++){
        $hid = ($i==0?"":"form-hide");
        $content .= "<div class='form-section cus_ct $hid'>"
            . "<h4>ผู้ติดต่อ</h4>"
            . $form->show_text("cname_$i","cname[]","","","ชื่อ $req","","label-3070")
            . $form->show_text("cemail_$i","cemail[]","","","Email","","label-3070","email")
            . $form->show_text("ctel_$i","ctel[]","","","โทร $req","","label-3070")
            . $form->show_textarea("cetc_$i","",3,10,"","อื่นๆ","label-3070","cetc[]")
            . "</div><!-- .form-section -->";
    }
    $content .= "<input id='view-more-but' type='button' value='เพิ่มรายชื่อผู้ติดต่อ' style='width:100%'/>"
            . "</div><!-- .col-50 -->";
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_customer")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . "<script>select_option_byval('pay');"
            . "select_option_byval('bill');"
            . "select_option_byval('cheque');"
            . "format_id('taxid');"
            . "view_more_section('cus_ct');"
            . "</script>";
            
    $form->addformvalidate("ez-msg", array('name','address','tel'),null,null,array("cat"));
    $content .= $form->submitscript("$('#papform').submit();")
            . "</div><!-- .col-100 -->";
} else if(isset($cid)) {
    //check
    $salerep = $db->get_info("pap_sale_cus","cus_id",$cid);
    if($pauth<=1){
        header("location:$redirect");
        exit();
    } else if($pauth<4&&$uid!=$salerep['user_id']){
        header("location:$redirect");
        exit();
    }
    /*----------------------------------------------------------------------------------note -------------------------------------------*/
    if($action =="note"){
        __autoload("pdo_tb");
        $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
        $type = (isset($_GET['fil_type'])&&$_GET['fil_type']>0?$_GET['fil_type']:null);
        $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
        $iperpage = 20;
        
        $menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
        $menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
        $content = $menu->showhead();
        $content .= $menu->pappanel("ลูกค้า","รายการลูกค้า");
        $info = $db->get_info("pap_customer","customer_id",$cid);
        
        $note = $form->show_select("type", $op_note_type, "label-3070", "ประเภท", null)
                . $form->show_text("date","date","","yyyy-mm-dd","วันที่ $req","","label-3070")
                . $form->show_num("hour", "", 1, "", "HH", "", "label-3070","min='0' max='24'")
                . $form->show_num("min", "", 1, "", "MM", "", "label-3070","min='0' max='60'")
                . $form->show_textarea("note","",4,10,"","บันทึก $req","label-3070")
                . $form->show_submit("submit","เพิ่มบันทึก","but-right")
                . "<input type='button' id='cancel-edit' value='ยกเลิกการแก้ไข' class='form-hide' />";
        $send = $form->show_text("receiver","receiver","","ค้นหา 3 ตัวอักษรขึ้นไป","ผู้รับ","","label-inline")
                . "<div id='rec-list'></div>"
                . $form->show_button("send_email", "ส่งอีเมล","but-right");
        
        $content .= "<h1 class='page-title'>บันทึกการติดต่อลูกค้า</h1>"
                . "<div id='ez-msg'>".  showmsg() ."</div>"
                . $form->show_st_form()
                . "<div class='col-50'>"
                . $form->show_text("code","code",$info['customer_code'],"","รหัสลูกค้า","","label-3070 readonly",null,"readonly")
                . $form->show_text("name","name",$info['customer_name'],"","ชื่อบริษัท","","label-3070 readonly",null,"readonly")
                . $form->show_tabs("note-tab",array("บันทึก","ส่งบันทึก"),array($note,$send),0);
                
        $content .= $form->show_hidden("request","request","add_note")
                . $form->show_hidden("cid","cid",$cid)
                . $form->show_hidden("uid","uid",$uid)
                . $form->show_hidden("nid","nid",0)
                . $form->show_hidden("pauth","pauth",$pauth)
                . $form->show_hidden("redirect","redirect",$redirect."?action=note&cid=$cid")
                . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
                . "<script>"
                . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
                . "edit_note();"
                . "send_note();"
                . "customer_fun();"
                . "</script>";
        $form->addformvalidate("ez-msg", array('date','note'));
        $content .= $form->submitscript("$('#papform').submit();")
                . "</div><!-- .col-50 -->";
        
        //show table
        $head = array("Type","วันที่","บันทึก");
        $rec = $tbpdo->view_note($op_note_type,$pauth,$cid,$uid,$type,$s,$page,$iperpage);
        $all_rec = $tbpdo->view_note($op_note_type,$pauth,$cid,$uid,$type,$s);
        $max = ceil(count($all_rec)/$iperpage);
        $clear_redirect = $redirect."?action=note&cid=$cid";
        $content .= "<div class='col-100'>"
                . $tb->show_search(current_url(), "scid", "s","ค้นหาใบสั่งงาน จากรหัส หรือชื่องาน",$s)
                . $tb->show_filter(current_url(), "fil_type", $op_note_type, $type,"Type")
                . "<div class='tb-clear-filter'><a href='$clear_redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
                . $tb->show_pagenav(current_url(), $page, $max)
                . $tb->show_table($head,$rec,"tb-note")
                . "</div><!-- .col-100 -->";
     /*----------------------------------------------------------------------------------view -------------------------------------------*/
    } else if($action=="view"){
        __autoload("pdo_report");
        $rp = new reportPDO();
        $info = $db->get_info("pap_customer","customer_id",$cid)+$db->get_meta("pap_customer_meta", "customer_id", $cid);
        $tax = $db->get_info("pap_customer_cat","customer_id",$cid);
        $month = array(pap_nmonth()=>date_format(date_create(null),"M-Y"))+$db->get_quote_month("%Y%m");
        
        //pic
        if(isset($info['picture'])){
            $pic = "<h4>รูปภาพ : </h4>"
                . $md->media_mul_show(explode(",",$info['picture']), ROOTS, RDIR);
        } else {
            $pic = "";
        }
        
        $head = array("รายการ","ข้อมูล");
        $rec = array();
        array_push($rec,array("รหัสลูกค้า",$info['customer_code']));
        array_push($rec,array("ชื่อบริษัท",$info['customer_name']));
        array_push($rec,array("เลขทะเบียนการค้า",$info['customer_taxid']));
        array_push($rec,array("สิทธิทางภาษี",$tax_ex[$info['tax_exclude']]));
        array_push($rec,array("กลุมลูกค้า",$cats[$tax['tax_id']]));
        array_push($rec,array("สถานะลูกค้า",$op_cus_status[$info['customer_status']]));
        array_push($rec,array("ที่อยู่",$info['customer_address']));
        array_push($rec,array("Website",$info['customer_url']));
        array_push($rec,array("Email",$info['customer_email']));
        array_push($rec,array("เบอร์โทร",$info['customer_tel']));
        array_push($rec,array("Fax",$info['customer_fax']));
        array_push($rec,array("การชำระเงิน",$op_payment[$info['customer_pay']]));
        array_push($rec,array("เครดิต(วัน)",$info['customer_credit_day']));
        array_push($rec,array("วงเงิน",$info['customer_credit_amount']));
        array_push($rec,array("วันวางบิล",$info['customer_place_bill']));
        array_push($rec,array("วันรับเช็ค",$info['customer_collect_cheque']));

        //quote report
        $rec2 = $rp->report_quote($cid,$op_quote_status_icon, pap_nmonth());
        $quote = $form->show_select("month", $month, "label-3070", "เดือน", null)
                . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
                . $form->show_hidden("cid","cid",$cid)
                . "<div id='quote_rp'>"
                . $tb->show_table(array("สถานะ","จำนวน","ยอดรวม"),$rec2)
                . "</div>"
                . "<div class='tb-legend'>"
                . my_legend($op_quote_status, $op_quote_status_icon)
                . "</div>";
        
        $m_order = $rp->rp_monthly_order($cid, date_format(date_create(null),"Y"));
        $year = date_format(date_create(null),"Y");
        $cdata = json_encode($m_order);
        $content .= "<h1 class='page-title'>รายละเอียดลูกค้า</h1>"
                . "<div id='ez-msg'>".  showmsg() ."</div>"
                . "<div class='col-50'>"
                . $tb->show_table($head,$rec)
                . "</div><!-- .col-50 -->"
                . "<div class='col-50'>"
                . $pic
                . "</div><!-- .col-50 -->"
                . "<div class='col-100'>"
                . $form->show_tabs("rp-tab",array("ใบเสนอราคา"),array($quote),0)
                . "<div id='chart-order'></div>"
                . "</div><!-- .col-100 -->"
                . "<script>"
                . "customer_fun();"
                . "column_chart('รายงานยอดสั่งรายเดือน (ปี $year)','เดือน','ยอดสั่ง',$cdata,'chart-order');"
                . "</script>";
        
        
     /*----------------------------------------------------------------------------------edit -------------------------------------------*/
    } else {
        //load
        $info = $db->get_info("pap_customer","customer_id",$cid)+$db->get_meta("pap_customer_meta", "customer_id", $cid);
        $tax = $db->get_info("pap_customer_cat","customer_id",$cid);
        $contacts = $db->get_contact($cid,1);
        //edit
        $note = "&nbsp;<a href='$redirect"."?action=note&cid=$cid' class='icon-comment-discussion'></a>";
        $cus_cat = array("0"=>"--กลุ่มลูกค้า--")+$db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='customer_cat'");
        $content .= "<h1 class='page-title'>แก้ไขข้อมูลลูกค้า $note</h1>"
                . "<div id='ez-msg'>".  showmsg() ."</div>"
                . $form->show_st_form()
                . "<div class='col-50'>"
                . $form->show_text("code","code",$info['customer_code'],"","รหัสลูกค้า","","label-3070 readonly",null,"readonly")
                . $form->show_text("name","name",$info['customer_name'],"","ชื่อบริษัท $req","","label-3070")
                . $form->show_text("taxid","taxid",$info['customer_taxid'],"","เลขทะเบียนการค้า (Tax ID)","","label-3070")
                . $form->show_text("branch","branch",$info['c_branch'],"","สำนักงาน","","label-3070")
                . $form->show_select("ntax",$tax_ex,"label-3070","องค์กรณ์ที่ได้รับการยกเว้นภาษี",$info['tax_exclude'])
                . $form->show_select("cat",$cats,"label-3070","กลุ่มลูกค้า",$tax['tax_id'])
                . $form->show_hidden("ori_cat","ori_cat",$tax['tax_id'])
                . $form->show_select("status",$op_cus_status,"label-3070","สถานะลูกค้า",$info['customer_status'])
                . $form->show_textarea("address",$info['customer_address'],4,10,"","ที่อยู่ $req","label-3070")
                . $form->show_text("url","url",$info['customer_url'],"","เว็บไซต์","","label-3070")
                . $form->show_text("email","email",$info['customer_email'],"","อีเมล","","label-3070","email")
                . $form->show_text("tel","tel",$info['customer_tel'],"","โทรศัพท์ $req","","label-3070")
                . $form->show_text("fax","fax",$info['customer_fax'],"","โทรสาร","","label-3070")
                . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-3070")
                . $form->show_select("pay",$op_payment,"label-3070","การชำระเงิน",$info['customer_pay'])
                . "<div class='sel-pay-1'>"
                . $form->show_num("credit_day",$info['customer_credit_day'],1,"","Credit(วัน)","","label-3070")
                . $form->show_num("credit",$info['customer_credit_amount'],1,"","วงเงิน(บาท)","","label-3070")
                . $form->show_select("bill",$op_bill_type,"label-3070","วันวางบิล",$info['customer_place_bill'])
                . "<div class='sel-bill-2'>"
                . $form->show_text("bill_day","bill_day",(isset($info['bill_day'])?$info['bill_day']:""),"","วันที่","","label-3070")
                . "</div><!-- .sel-bill-2 -->"
                . "<div class='sel-bill-3'>"
                . $form->show_text("bill_weekday","bill_weekday",(isset($info['bill_weekday'])?$info['bill_weekday']:""),"","วันของสัปดาห์","","label-3070")
                . $form->show_text("bill_week","bill_week",(isset($info['bill_week'])?$info['bill_week']:""),"","สัปดาห์ที่","","label-3070")
                . "</div><!-- .sel-bill-3 -->"
                . $form->show_text("bill_remark","bill_remark",$info['bill_remark'],"","หมายเหตุวันวางบิล","","label-3070")
                . $form->show_select("cheque",$op_cheque_type,"label-3070","วันรับเช็ค",$info['customer_collect_cheque'])
                . "<div class='sel-cheque-2'>"
                . $form->show_text("cheque_day","cheque_day",(isset($info['cheque_day'])?$info['cheque_day']:""),"","วันที่","","label-3070")
                . "</div><!-- .sel-cheque-2 -->"
                . "<div class='sel-cheque-3'>"
                . $form->show_text("cheque_weekday","cheque_weekday",(isset($info['cheque_weekday'])?$info['cheque_weekday']:""),"","วันของสัปดาห์","","label-3070")
                . $form->show_text("cheque_week","cheque_week",(isset($info['cheque_week'])?$info['cheque_week']:""),"","สัปดาห์ที่","","label-3070")
                . "</div><!-- .sel-cheque-3 -->"
                . $form->show_text("cheque_remark","cheque_remark",$info['cheque_remark'],"","หมายเหตุวันรับเช็ค","","label-3070")
                . "</div><!-- .sel-pay-1 -->"
                . "</div><!-- .col-50 -->"
                . "<div class='col-50'>";
        
        //pic-tab
        if(isset($info['picture'])){
            $pics = $md->media_mul_view(explode(",",$info['picture']), ROOTS, RDIR);
        } else {
            $pics = "";
        }
        $pictab = $md->show_minput("cpics","cpics",$pics)
                . $form->show_hidden("ori_media","ori_media",(isset($info['picture'])?$info['picture']:""));

        //form-tab
        if($pauth>3){
            $saletab = $form->show_select("sale",$sale,"label-3070","Sale รับผิดชอบ",(isset($salerep['user_id'])?$salerep['user_id']:null));
            $content .= $form->show_tabs("cus-tab",array("Sales","ภาพประกอบ"),array($saletab,$pictab),0);
            $del = "<span id='del-cus' class='red-but'>Delete</span>"
                    . "<script>del_cus();</script>";
        } else {
            $content .= $form->show_tabs("cus-tab",array("ภาพประกอบ"),array($pictab))
                . $form->show_hidden("sale","sale",$uid);
            $del = "";
        }

        $head = array("ชื่อ","โทร");
        $form2 = new myform("ct-form");
        $content .= "<div class='form-section'>"
            . "<h4>รายชื่อผู้ติดต่อ</h4>"
            . $tb->show_table($head,$contacts)
            . $form2->show_text("cname","cname","","","ชื่อ $req","","label-3070")
            . $form2->show_text("cemail","cemail","","","Email","","label-3070","email")
            . $form2->show_text("ctel","ctel","","","โทร $req","","label-3070")
            . $form2->show_textarea("cetc","",3,10,"","อื่นๆ","label-3070","cetc")
            . $form->show_hidden("ctid","ctid",0)
            . $form->show_hidden("ct_cat","ct_cat",1)
            . "<input id='add-more-ct' type='button' value='เพิ่มรายชื่อผู้ติดต่อ' style='width:100%'/>"
            . "</div><!-- .form-section -->";

        $content .= "</div><!-- .col-50 -->";
        $arrname = json_encode($form2->array_name);
        $content .= $del
                . $form->show_submit("submit","Update","but-right")
                . $form->show_hidden("request","request","edit_customer")
                . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
                . $form->show_hidden("cid","cid",$cid)
                . $form->show_hidden("redirect","redirect",$redirect)
                . "<script>"
                . "select_option_byval('pay');"
                . "format_id('taxid');"
                . "add_contact($arrname);"
                . "select_option_byval('bill');"
                . "select_option_byval('cheque');"
                . "</script>";
        $form->addformvalidate("ez-msg", array('name','address','tel'),null,null,array("cat"));
        $content .= $form->submitscript("$('#papform').submit();");
    }
} else {
    $cat = (isset($_GET['cat'])&&$_GET['cat']>0?$_GET['cat']:null);
    $status = (isset($_GET['st'])&&$_GET['st']>0?$_GET['st']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $sid = (isset($_GET['sid'])&&$_GET['sid']!=0?$_GET['sid']:null);
    
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    $all_rec = $tbpdo->view_customer($pauth,$op_cus_status_icon, $cat,$status,($pauth>3?$sid:$uid),$s);
    $max = ceil(count($all_rec)/$iperpage);
    
    //view
    $head = array("บริษัท","กลุ่ม","อีเมล","โทร","สถานะ");
    $rec = $tbpdo->view_customer($pauth,$op_cus_status_icon,$cat,$status,($pauth>3?$sid:$uid),$s,$page,$iperpage);
    $addhtml = "";
    $fil_sale = "";
    if($pauth>1){
        array_unshift($head, "แก้ไข","Note");
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    } 
    if($pauth>3){
        $fil_sale = $tb->show_filter(current_url(), "sid", $sale, $sid,"--Sale--");
    }
    $content .= "<h1 class='page-title'>ฐานข้อมูลลูกค้า $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $tb->show_search(current_url(), "cusid", "s","ค้นหาจากรายการลูกค้า",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มลูกค้า--")
            . $tb->show_filter(current_url(), "st", $op_cus_status, $status,"--สถานะลูกค้า--")
            . $fil_sale
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-cus")
            . "<div class='tb-legend'>"
            . my_legend($op_cus_status, $op_cus_status_icon)
            . "</div>"
            . "</div><!-- .col-100 -->"
            . "<script>customer_search();</script>";
}
    
$content .= $menu->showfooter();
echo $content;

