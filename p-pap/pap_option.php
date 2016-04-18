<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$type = filter_input(INPUT_GET,'type',FILTER_SANITIZE_STRING);
$root = PAP;
$redirect = $root.basename(__FILE__)."?type=$type";
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | Option";
$menu->extrascript = <<<END_OF_TEXT
<style>
        h3 {
            margin-bottom:15px;
        }
        #tb-prod-cat tr th:first-child,
        #tb-prod-cat tr td:first-child {
            width:12%;
        }
</style>
END_OF_TEXT;

$submenu = $op_type_name[$type][1];
$content = $menu->showhead();
$content .= $menu->pappanel($op_type_name[$type][0],$submenu);

$form = new myform("option","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$opid = filter_input(INPUT_GET,'opid',FILTER_SANITIZE_STRING);

if(isset($opid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_option","op_id",$opid);
    //edit
    $process_cat = array("0"=>"--กระบวนการ--")+$db->get_keypair("pap_process_cat", "id", "name");
    $name = $form->show_text("name","name",$info['op_name'],"","ชื่อ$submenu","","label-3070");
    if($type == "paper_size"){
        $size = json_decode($info['op_value'],true);
        $value = $form->show_num("width",$size['width'],0.01,"","กว้าง(นิ้ว)","","label-3070","min='1'")
            . $form->show_num("length",$size['length'],0.01,"","ยาว(นิ้ว)","","label-3070","min='1'");
    } else if($type=="paper_allo"){
        $v = explode(",",$info['op_value']);
        $name = $form->show_num("name",$info['op_name'],1,"","เผื่อกระดาษเสีย(แผ่น)","","label-3070","min='1'");
        $value = $form->show_num("from",$v[0],1,"","จำนวนสั่งพิมพ์ ระหว่าง","","label-3070")
                . $form->show_num("to",$v[1],1,"","ถึง","","label-3070");
    } else if($type == "product_cat"){
        $proc = explode(",",$info['op_value']);
        $value = "<div class='tab-section'>"
                . "<h4>ขั้นตอนการผลิต</h4>";
        for($i=0;$i<13;$i++){
            $value .= $form->show_select("proc_$i",$process_cat,"label-3070",$i+1,(isset($proc[$i])?$proc[$i]:null),"","proc[]");
        }
        $value .= "</div><!-- .tab-section -->";
    } else {
        $value = $form->show_textarea("value",$info['op_value'],4,10,"","คำอธิบาย","label-3070");
    }
    $content .= "<h1 class='page-title'>แก้ไข$submenu</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $name
            . $value
            . $form->show_hidden("type","type",$type);
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_option")
            . $form->show_hidden("opid","opid",$opid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'));
    $content .= $form->submitscript("$('#new').submit();");
} else {
    $content .= "<h1 class='page-title'>$submenu</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    //check
    if($pauth>1){
        $process_cat = array("0"=>"--กระบวนการ--")+$db->get_keypair("pap_process_cat", "id", "name");
        //add
        $name = $form->show_text("name","name","","","ชื่อ$submenu","","label-3070");
        if($type == "paper_size"){
            $value = $form->show_num("width","",0.01,"","กว้าง(นิ้ว)","","label-3070","min='1'")
                . $form->show_num("length","",0.01,"","ยาว(นิ้ว)","","label-3070","min='1'");
        } else if($type=="paper_allo"){
            $name = $form->show_num("name","",1,"","เผื่อกระดาษเสีย(แผ่น)","","label-3070","min='1'");
            $value = $form->show_num("from","",1,"","จำนวนสั่งพิมพ์ ระหว่าง","","label-3070")
                    . $form->show_num("to","",1,"","ถึง","","label-3070");
        } else if($type == "product_cat"){
            $value = "<div class='tab-section'>"
                    . "<h4>ขั้นตอนการผลิต</h4>";
            for($i=0;$i<13;$i++){
                $value .= $form->show_select("proc_$i",$process_cat,"label-3070",$i+1,null,"","proc[]");
            }
            $value .= "</div><!-- .tab-section -->";
        } else {
            $value = $form->show_textarea("value","",4,10,"","คำอธิบาย","label-3070");
        }
        $content .= "<div class='col-50'>"
                . "<h3>เพิ่ม$submenu</h3>"
                . $form->show_st_form()
                . $name
                . $value
                . $form->show_hidden("type","type",$type);

        $content .= $form->show_submit("submit","Add New","but-right")
                . $form->show_hidden("request","request","add_option")
                . $form->show_hidden("redirect","redirect",$redirect);
        $form->addformvalidate("ez-msg", array('name'));
        $content .= $form->submitscript("$('#new').submit();")
                ."</div><!-- .col-50 -->";
    }
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    //view
    $head = array("กลุ่ม","อธิบาย");
    //check
    if($pauth==1){
        $rec = $tbpdo->view_option($type,1);
    } else {
        array_unshift($head,"แก้ไข");
        $rec = $tbpdo->view_option($type);
    }
    $content .= "<div class='col-50'>"
            . $tb->show_table($head,$rec,"tb-prod-cat")
            . "</div><!-- .col-50 -->";
}
    
$content .= $menu->showfooter();
echo $content;

