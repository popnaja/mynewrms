<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root."setting.php";
__autoload("papmenu");
__autoload("pappdo");
$pagename = "ตั้งค่าระบบ";
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->extrascript = <<<END_OF_TEXT
<style>
        .ez-table {
            margin-bottom:25px;
        }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ",$pagename);

$form = new myform("papform","",PAP."request.php");
$md = new mymedia(PAP."request_ajax.php");
//check
if($pauth<1){
    header("location:$root");
    exit();
}
//load
$info = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");

//logo
$clogo = $md->media_view($info['c_logo'],ROOTS,RDIR);

$cominfo = "<h4>ข้อมูลบริษัท</h4>"
        . "<div class='label-3070'>"
        . "<label for='sig'>โลโก้บริษัท</label>"
        . "<div>"
        . $md->show_input("clogo","clogo",$clogo)
        . "</div>"
        . "</div><!-- .label-3070 -->"
        . $form->show_hidden("ori_media","ori_media",$info['c_logo'])
        . $form->show_text("name","name",(isset($info['name'])?$info['name']:""),"","ชื่อบริษัท","","label-3070")
        . $form->show_text("address","address",(isset($info['address'])?$info['address']:""),"","ที่อยู่","","label-3070")
        . $form->show_text("email","email",(isset($info['email'])?$info['email']:""),"","Email","","label-3070")
        . $form->show_text("tel","tel",(isset($info['tel'])?$info['tel']:""),"","เบอร์โทร","","label-3070")
        . $form->show_text("tax_id","tax_id",(isset($info['tax_id'])?$info['tax_id']:""),"","เลขประจำตัวผู้เสียภาษี","","label-3070");
        
$doc = "<h4>การเรียงลำดับเอกสาร</h4>"
    . $form->show_select("cdigit",$op_digit,"label-3070","รหัสลูกค้า",(isset($info['c_digit'])?$info['c_digit']:null),"ตัวอย่าง 3 digits A001,A002 ")
    . $form->show_select("s_digit",$op_digit,"label-3070","รหัสผู้ผลิต",(isset($info['s_digit'])?$info['s_digit']:null),"")
    . $form->show_select("rno_quote",$op_run_no,"label-3070","รหัสใบเสนอราคา",(isset($info['rno_quote'])?$info['rno_quote']:null),"")
    //. $form->show_select("rno_order",$op_run_order,"label-3070","รหัสใบสั่งงาน",(isset($info['rno_order'])?$info['rno_order']:null),"")
    . $form->show_select("rno_matpo",$op_run_po,"label-3070","รหัสใบสั่งซื้อวัตถุดิบ",(isset($info['rno_matpo'])?$info['rno_matpo']:null),"")
    . $form->show_select("rno_prodpo",$op_run_ppo,"label-3070","รหัสใบสั่งผลิต",(isset($info['rno_prodpo'])?$info['rno_prodpo']:null),"")
    . $form->show_select("rno_deli",$op_run_deli,"label-3070","รหัสใบส่งของ",(isset($info['rno_deli'])?$info['rno_deli']:null),"")
    . $form->show_select("rno_bill",$op_run_bill,"label-3070","รหัสใบวางบิล",(isset($info['rno_bill'])?$info['rno_bill']:null),"")
    . $form->show_select("rno_invoice",$op_run_invoice,"label-3070","รหัสใบกำกับภาษี",(isset($info['rno_invoice'])?$info['rno_invoice']:null),"")
    . $form->show_select("rno_rc",$op_run_rc,"label-3070","รหัสใบเสร็จ",(isset($info['rno_rc'])?$info['rno_rc']:null),"");

$grip = "<h4></h4>"
        . $form->show_num("margin",(isset($info['margin'])?$info['margin']:""),0.01,"","Margin (%)","","label-3070")
        . $form->show_num("grip",(isset($info['grip_size'])?$info['grip_size']:""),0.01,"","ขนาดกริ๊ปเครื่องพิมพ์ (ซ.ม.)","","label-3070")
        . $form->show_num("bleed",(isset($info['bleed_size'])?$info['bleed_size']:""),0.01,"","ขนาด Bleed (ซ.ม.)","","label-3070");

//update
$content .= "<h1 class='page-title'>$pagename</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-100'>"
        . $form->show_tabs("cinfo-tab",array("ข้อมูลบริษัท","ลำดับเอกสาร","อื่นๆ"),array($cominfo,$doc,$grip),0);

$content .= $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","update_setting")
        . $form->show_hidden("redirect","redirect",$redirect)
        . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
        . "</div><!-- .col-100 -->";
$form->addformvalidate("ez-msg", array('name','address','email','tel','tax_id'));
$content .= $form->submitscript("$('#papform').submit();")
        . "<script>"
        . "format_id('tax_id');"
        . "</script>";

$content .= $menu->showfooter();
echo $content;



