<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | Process";
$menu->ascript[] = $root."js/pap.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
.but-right {
        float:left;
        width:100%;
        }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายผลิต","กระบวนการผลิต");

$form = new myform("process","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$pid = filter_input(INPUT_GET,'pid',FILTER_SANITIZE_STRING);
$op_yesno = array(
    "0" => "--ไม่ใช้งาน--",
    "1" => "ใช้งาน"
);
$show = array(
    "1"=>"Show",
    "2"=>"Hide"
);
if($action=="add"){
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $process_cat = $db->get_keypair("pap_process_cat", "id", "name");
    $content .= "<h1 class='page-title'>เพิ่มกระบวนการผลิต</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อกระบวนการ","","label-3070")
            . $form->show_select("show",$show,"label-3070","แสดง",null)
            . $form->show_select("cat",$process_cat,"label-3070","กลุ่มกระบวนการ",null)
            . $form->show_select("unit",array("0"=>"--หน่วย--")+$op_unit,"label-3070","หน่วย",null,"")
            . $form->show_select("source",$op_process_source,"label-3070","แหล่งผลิต",null,"")
            . "<div class='sel-source-0'>"
            . $form->show_num("setup_min","",1,"","ตั้งเครื่อง","(นาที)","label-3070")
            . $form->show_num("capacity","",1,"","กำลังการผลิต","(หน่วย/ชั่วโมง)","label-3070")
            . "</div><div class='sel-source-1'>"
            . $form->show_num("std_lt","",1,"","ระยะเวลาที่ใช้ในการสั่งผลิต(ชั่วโมง)","","label-3070")
            . "</div>"
            . "</div><!-- .col-50 -->";
    
    $costt = "";
    for($i=0;$i<5;$i++){
        $otherc = " sel-cond_$i-".implode(" sel-cond_$i-",array_keys($op_unit));
        $hid = ($i===0?"":"form-hide");
        $costt .= "<div class='tab-section cost-cond $hid'>"
        . $form->show_num("fcost_$i","",0.01,"","ต้นทุนคงที่ (บาท)","","label-inline left-50","min=0","fcost[]")
        . $form->show_num("min_$i","",0.01,"","ขั้นต่ำ (บาท)","","label-inline right-50","min=0","min[]")
        . $form->show_select("vunit_$i",$op_criteria,"label-inline left-50","หน่วยต้นทุนแปรผัน",null,"","vunit[]")
        . $form->show_num("cost_$i","",0.00001,"","(บาท/<span class='prod-unit-label'>หน่วย</span>)","","label-inline right-50","min=0","cost[]")
        . $form->show_select("cond_$i",array("0"=>"--ไม่มี--")+$op_criteria,"label-inline","ข้อกำหนด",null,"","cond[]")
        . "<div class='sel-cond_$i-0'>"
        . "</div>"
        . "<div class='$otherc'>"
        . $form->show_num("btw_$i","",1,"","ระหว่าง","","left-50 label-inline","min=0","btw[]")
        . $form->show_num("to_$i","",1,"","ถึง","","right-50 label-inline","min=0","to[]")
        . "</div>"
        . "</div><!-- .cost-cond -->"
        . "<script>select_option_byval('cond_$i');</script>";
    }
    $costt .= "<input id='view-more-but' type='button' value='เพิ่มเงื่อนไขต้นทุน' style='width:100%'/>";
    //detail variable cost
    /*
    $mat = array("0"=>"--วัตถุดิบ--") + $db->get_keypair("pap_mat", "mat_id", "CONCAT(mat_name,'(',mat_unit,')')", "WHERE mat_cat_id NOT IN (8,9)");
    $dtcost = "<div class='tab-section'>"
            . $form->show_select("usedetail",$op_yesno,"label-inline","คำนวณต้นทุนตามปริมาณการใช้วัตถุดิบ","0")
            . $form->show_num("labor","",1,"","จำนวนแรงงาน","","label-inline")
            . "</div><!-- .tab-section -->";
    for($i=0;$i<6;$i++){
        $dtcost .= $form->show_select("mat_$i",$mat,"left-50 label-inline",($i==0?"วัตถุดิบ":null),null,"","mat[]")
                . $form->show_num("usage_$i","","any","",($i==0?"ประมาณการใช้/หน่วยผลิต":null),"","right-50 label-inline",null,"usage[]");
    }
     * 
     */
            
    $content .= "<div class='col-50'>"
            . $form->show_tabs("cost-tab",["ต้นทุน"],[$costt],0)
            . "</div><!-- .col-50 -->";

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_process")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "select_option('source');"
            . "view_more_section('cost-cond');"
            . "unit_label();"
            . "</script>"
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'),null,null,array('unit'));
    $content .= $form->submitscript("$('#new').submit();");
} else if(isset($pid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_process","process_id",$pid);
    $meta = $db->get_meta("pap_process_meta", "process_id", $pid);
    //edit
    $process_cat = $db->get_keypair("pap_process_cat", "id", "name");
    $content .= "<h1 class='page-title'>แก้ไขกระบวนการผลิต</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name",$info['process_name'],"","ชื่อกระบวนการ","","label-3070")
            . $form->show_select("show",$show,"label-3070","แสดง",$meta['pc_show'],"")
            . $form->show_select("cat",$process_cat,"label-3070","กลุ่มกระบวนการ",$info['process_cat_id'])
            . $form->show_select("unit",array("0"=>"--หน่วย--")+$op_unit,"label-3070","หน่วย",$info['process_unit'],"")
            . $form->show_select("source",$op_process_source,"label-3070","แหล่งผลิต",$info['process_source'],"")
            . "<div class='sel-source-0'>"
            . $form->show_num("setup_min",$info['process_setup_min'],1,"","เวลาตั้งเครื่อง(นาที)","","label-3070")
            . $form->show_num("capacity",$info['process_cap'],1,"","กำลังการผลิต(หน่วย/ชั่วโมง)","","label-3070")
            . "</div><div class='sel-source-1'>"
            . $form->show_num("std_lt",$info['process_std_leadtime_hour'],1,"","ระยะเวลาที่ใช้ในการสั่งผลิต(ชั่วโมง)","","label-3070")
            . "</div>"
            . "</div><!-- .col-50 -->";

    $cost = (isset($meta['cost'])?json_decode($meta['cost'],true):array());
    $n = count($cost);
    $costt = "";
    for($i=0;$i<5;$i++){
        $otherc = " sel-cond_$i-".implode(" sel-cond_$i-",array_keys($op_criteria));
        $hid = ($i<$n?"":"form-hide");
        $uname = (isset($cost[$i])?$op_criteria[$cost[$i]['vunit']]:"หน่วย");
        $costt .= "<div class='form-section cost-cond $hid'>"
        . $form->show_num("fcost_$i",(isset($cost[$i]['fcost'])?$cost[$i]['fcost']:0),0.01,"","ต้นทุนคงที่","","label-inline left-50","min=0","fcost[]")
        . $form->show_num("min_$i",(isset($cost[$i])?$cost[$i]['min']:""),0.01,"","ต้นทุนขั้นต่ำ(บาท)","","label-inline right-50","min=0","min[]")
        . $form->show_select("vunit_$i",$op_criteria,"label-inline left-50","หน่วยต้นทุนแปรผัน",(isset($cost[$i])?$cost[$i]['vunit']:""),"","vunit[]")
        . $form->show_num("cost_$i",(isset($cost[$i])?$cost[$i]['cost']:""),0.00001,"","(บาท/<span class='prod-unit-label'>$uname</span>)","","label-inline right-50","min=0","cost[]")
        
        . $form->show_select("cond_$i",array("0"=>"--ไม่มี--")+$op_criteria,"label-inline","ข้อกำหนด",(isset($cost[$i])?$cost[$i]['cond']:""),"","cond[]")
        . "<div class='$otherc'>"
        . $form->show_num("btw_$i",(isset($cost[$i])?$cost[$i]['btw']:""),1,"","ระหว่าง","","left-50 label-inline","min=0","btw[]")
        . $form->show_num("to_$i",(isset($cost[$i])?$cost[$i]['to']:""),1,"","ถึง","","right-50 label-inline","min=0","to[]")
        . "</div><!-- .otherc -->"
        . "</div><!-- .cost-cond -->"
        . "<script>select_option_byval('cond_$i');</script>";
    }
    $costt .= "<input id='view-more-but' type='button' value='เพิ่มเงื่อนไขต้นทุน' style='width:100%'/>";
    
    //detail variable cost
    /*
    $mat = array("0"=>"--วัตถุดิบ--") + $db->get_keypair("pap_mat", "mat_id", "CONCAT(mat_name,'(',mat_unit,')')", "WHERE mat_cat_id NOT IN (8,9)");
    if(isset($meta['detail_cost'])){
        $dcost = json_decode($meta['detail_mat'],true);
    } else {
        $dcost = array();
    }
    
    $dtcost = "<div class='tab-section'>"
            . $form->show_select("usedetail",$op_yesno,"label-inline","คำนวณต้นทุนตามปริมาณการใช้วัตถุดิบ",(isset($meta['detail_cost'])?$meta['detail_cost']:"0"))
            . $form->show_num("labor",(isset($meta['detail_labor'])?$meta['detail_labor']:""),1,"","จำนวนแรงงาน","","label-inline")
            . "</div><!-- .tab-section -->";
    for($i=0;$i<6;$i++){
        $mid = (isset($dcost[$i][0])?$dcost[$i][0]:0);
        $usage = (isset($dcost[$i][1])?$dcost[$i][1]:"");
        $dtcost .= $form->show_select("mat_$i",$mat,"left-50 label-inline",($i==0?"วัตถุดิบ":null),$mid,"","mat[]")
                . $form->show_num("usage_$i",$usage,"any","",($i==0?"ประมาณการใช้/หน่วยผลิต":null),"","right-50 label-inline","min=0","usage[]");
    }
     * 
     */
    
    $content .= "<div class='col-50'>"
            . $form->show_tabs("cost-tab",["ต้นทุน"],[$costt],0)
            . "</div><!-- .col-50 -->";
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_process")
            . $form->show_hidden("pid","pid",$pid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "select_option('source');"
            . "view_more_section('cost-cond');"
            . "unit_label();"
            . "</script>"
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'),null,null,array('unit'));
    $content .= $form->submitscript("$('#new').submit();");
} else {
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    
    $cat = (isset($_GET['cat'])&&$_GET['cat']!=0?$_GET['cat']:null);
    $source = (isset($_GET['source'])&&$_GET['source']!=0?$_GET['source']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    
    //list
    $cats = $db->get_keypair("pap_process_cat", "id", "name");

    //view
    $head = array("แก้ไข","กระบวนการผลิต","กลุ่ม","หน่วย","แหล่งผลิต","เวลาตั้งเครื่อง<br/>(นาที)","กำลังการผลิต<br/>(หน่วย/ชม)","ระยะเวลาสั่งผลิต<br/>(ชั่วโมง)");
    $all_rec = $tbpdo->view_process($pauth,$cat,$source,$s);
    $rec = $tbpdo->view_process($pauth,$cat,$source,$s,$page,$iperpage);
    $max = ceil(count($all_rec)/$iperpage);
    $addhtml = "";
    if($pauth==1){
    } else {
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
    $content .= "<h1 class='page-title'>กระบวนการผลิต $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "cusid", "s","ค้นหา",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มการผลิต--")
            . $tb->show_filter(current_url(), "source", $op_process_source, $source,"--แหล่งผลิต--")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-process")
            . "</div>";
}
    
$content .= $menu->showfooter();
echo $content;

