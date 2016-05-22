<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$uid = $_SESSION['upap'][0];
$pagename = "Supplier";
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = $root."js/pap.js";
$menu->ascript[] = $root."js/customer.js";
$menu->astyle[] = $root."css/customer.css";

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายจัดซื้อ",$pagename);

__autoloada("term");
$termdb = new myterm(DB_PAP);
$cats = $termdb->get_parent("supplier");

$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$sid = filter_input(INPUT_GET,'sid',FILTER_SANITIZE_STRING);

if($action=="add"){
/*--------------------------------------------------------------  ADD ------------------------------------------------------------------*/
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    
    //add
    $content .= "<h1 class='page-title'>เพิ่ม $pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อบริษัท $req","","label-3070")
            . $form->show_text("taxid","taxid","","","เลขทะเบียนการค้า (Tax ID)","","label-3070")
            . $form->show_text("branch","branch","สำนักงานใหญ่","","สำนักงาน","","label-3070")
            . $form->show_select("cat",$cats,"label-3070","กลุ่มผู้ผลิต",null)
            . $form->show_textarea("address","",4,10,"","ที่อยู่ $req","label-3070")
            . $form->show_text("url","url","","","เว็บไซต์","","label-3070")
            . $form->show_text("email","email","","","อีเมล","","label-3070","email")
            . $form->show_text("tel","tel","","","โทรศัพท์ $req","","label-3070")
            . $form->show_text("fax","fax","","","โทรสาร","","label-3070")
            . $form->show_select("pay",$op_payment,"label-3070","การชำระเงิน",null)
            . "<div class='sel-pay-1'>"
            . $form->show_num("credit_day","",1,"","Credit(วัน)","","label-3070")
            . $form->show_num("credit","",1,"","วงเงิน(บาท)","","label-3070")
            . "</div><!-- .sel-pay-1 -->"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>";
    
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
            . $form->show_hidden("request","request","add_supplier")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>select_option_byval('pay');"
            . "format_id('taxid');"
            . "view_more_section('cus_ct');"
            . "</script>";
            
    $form->addformvalidate("ez-msg", array('name','address','tel',"cname_0","ctel_0"));
    $content .= $form->submitscript("$('#papform').submit();")
            . "</div><!-- .col-100 -->";
} else if(isset($sid)) {
/*--------------------------------------------------------------  EDIT ------------------------------------------------------------------*/
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_supplier","id",$sid)+$db->get_meta("pap_supplier_meta", "supplier_id", $sid);
    $tax = $db->get_info("pap_supplier_cat","supplier_id",$sid);
    $contacts = $db->get_sup_ct($sid);

    //edit
    $content .= "<h1 class='page-title'>รายละเอียดลูกค้า</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("code","code",$info['code'],"","รหัสลูกค้า","","label-3070 readonly",null,"readonly")
            . $form->show_text("name","name",$info['name'],"","ชื่อบริษัท $req","","label-3070")
            . $form->show_text("taxid","taxid",$info['taxid'],"","เลขทะเบียนการค้า (Tax ID)","","label-3070")
            . $form->show_text("branch","branch",(isset($info['branch'])?$info['branch']:""),"","สำนักงาน","","label-3070")
            . $form->show_select("cat",$cats,"label-3070","กลุ่มลูกค้า",$tax['tax_id'])
            . $form->show_hidden("ori_cat","ori_cat",$tax['tax_id'])
            . $form->show_textarea("address",$info['address'],4,10,"","ที่อยู่ $req","label-3070")
            . $form->show_text("url","url",$info['url'],"","เว็บไซต์","","label-3070")
            . $form->show_text("email","email",$info['email'],"","อีเมล","","label-3070","email")
            . $form->show_text("tel","tel",$info['tel'],"","โทรศัพท์ $req","","label-3070")
            . $form->show_text("fax","fax",$info['fax'],"","โทรสาร","","label-3070")
            . $form->show_select("pay",$op_payment,"label-3070","การชำระเงิน",$info['payment'])
            . "<div class='sel-pay-1'>"
            . $form->show_num("credit_day",$info['credit_day'],1,"","Credit(วัน)","","label-3070")
            . $form->show_num("credit",$info['credit_amount'],1,"","วงเงิน(บาท)","","label-3070")
            . "</div><!-- .sel-pay-1 -->"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>";

    $tb = new mytable();
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
        . "<input id='add-more-ct' type='button' value='เพิ่มรายชื่อผู้ติดต่อ' style='width:100%'/>"
        . "</div><!-- .form-section -->";

    $content .= "</div><!-- .col-50 -->";
    $arrname = json_encode($form2->array_name);
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_supplier")
            . $form->show_hidden("sid","sid",$sid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "select_option_byval('pay');"
            . "format_id('taxid');"
            . "add_sup_ct($arrname);"
            . "</script>";
    $form->addformvalidate("ez-msg", array('name','address','tel'));
    $content .= $form->submitscript("$('#papform').submit();");
} else {
/*--------------------------------------------------------------  VIEW ALL ------------------------------------------------------------------*/
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    $cat = (isset($_GET['cat'])&&$_GET['cat']>0?$_GET['cat']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    $all_rec = $tbpdo->view_supplier($pauth, $cat,$s);
    $max = ceil(count($all_rec)/$iperpage);
    
    //view
    $head = array("แก้ไข","รหัส","บริษัท","กลุ่ม","อีเมล","โทร");
    $addhtml = "";
    if($pauth>1){
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
    $rec = $tbpdo->view_supplier($pauth,$cat,$s,$page,$iperpage);
    $content .= "<h1 class='page-title'>ฐานข้อมูล $pagename $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "cusid", "s","ค้นหา",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มผู้ผลิต--")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-supplier")
            . "</div><!-- .col-100 -->";
}
    
$content .= $menu->showfooter();
echo $content;



