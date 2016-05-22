<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$pagename = "เครื่องจักร";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = $root."js/machine.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
.but-right {
        float:left;
        width:100%;
        }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายผลิต",$pagename);

$form = new myform("papform","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$mid = filter_input(INPUT_GET,'mid',FILTER_SANITIZE_STRING);
if($action=="add"){
/*--------------------------------------------------------------  ADD ------------------------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $process = array("0"=>"--กระบวนการผลิต--")+$db->get_keypair("pap_process", "process_id", "process_name","WHERE process_source=1 ORDER BY process_cat_id ASC, process_name ASC");
    $users = $db->get_userpair(68);
    $content .= "<h1 class='page-title'>เพิ่ม$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_select("sel_process",$process,"label-3070","กระบวนการ",null)
            . $form->show_text("name","name","","","ชื่อ$pagename","","label-3070")
            . "<div id='pinfo'></div>"
            . "</div><!-- .col-50 -->";
    
    $operator = $form->show_checkbox("operator", "operator", $users, "ช่าง", "label-3070");
            
    $content .= "<div class='col-50'>"
            . $form->show_tabs("mach-tab",array("ช่าง"),array($operator),0)
            . "</div><!-- .col-50 -->";

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_machine")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("ajax_rq","ajax_rq",$root."request_ajax.php")
            . "<script>"
            . "get_process_info();"
            . "</script>"
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name','setup_min',"capacity"));
    $content .= $form->submitscript("$('#papform').submit();");
} else if(isset($mid)) {
/*--------------------------------------------------------------  EDIT ------------------------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_machine","id",$mid);
    $pinfo = $db->get_info("pap_process","process_id",$info['process_id']);
    
    $process = array("0"=>"--กระบวนการผลิต--")+$db->get_keypair("pap_process", "process_id", "process_name","WHERE process_source=1 ORDER BY process_cat_id ASC, process_name ASC");
    $users = $db->get_userpair(68);
    $user_checked = $db->get_mm_arr("pap_mach_user", "user_id", "mach_id", $mid);
    $checked_users = $form->checked_array($users, $user_checked);
    $content .= "<h1 class='page-title'>เพิ่ม$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_select("sel_process",$process,"label-3070","กระบวนการ",$info['process_id'])
            . $form->show_text("name","name",$info['name'],"","ชื่อ$pagename","","label-3070")
            . "<div id='pinfo'>"
            . $form->show_text("unit", "unit", $op_unit[$pinfo['process_unit']], "", "หน่วย", "", "label-3070 readonly", null, "readonly")
            . $form->show_num("setup_min",$info['setup_min'],1,"","ตั้งเครื่อง(นาที)","","label-3070")
            . $form->show_num("capacity",$info['cap'],1,"","กำลังการผลิต","(หน่วย/ชั่วโมง)","label-3070")
            . "</div>"
            . "</div><!-- .col-50 -->";
    
    $operator = $form->show_checkbox("operator", "operator", $checked_users, "ช่าง", "label-3070");
            
    $content .= "<div class='col-50'>"
            . $form->show_tabs("mach-tab",array("ช่าง"),array($operator),0)
            . "</div><!-- .col-50 -->";
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_machine")
            . $form->show_hidden("mid","mid",$mid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("ajax_rq","ajax_rq",$root."request_ajax.php")
            . "<script>"
            . "get_process_info();"
            . "</script>"
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name','setup_min',"capacity"));
    $content .= $form->submitscript("$('#papform').submit();");
} else {
/*--------------------------------------------------------------  VIEW ALL ------------------------------------------------------------------*/
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    $cat = (isset($_GET['cat'])&&$_GET['cat']!=0?$_GET['cat']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    
    //list
    $cats = $db->get_keypair("pap_process_cat", "id", "name");
    
    //view
    $head = array("เครื่องจักร","กระบวนการ","หน่วย","เวลาตั้งเครื่อง(นาที)","กำลังการผลิต(หน่วย/ชม)","ช่าง");
    $all_rec = $tbpdo->view_machine($pauth,$cat,$s);
    $rec = $tbpdo->view_machine($pauth,$cat,$s,$page,$iperpage);
    $max = ceil(count($all_rec)/$iperpage);
    $addhtml = "";
    if($pauth==1){
    } else {
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
        array_unshift($head, "แก้ไข");
    }
    
    $content .= "<h1 class='page-title'>$pagename $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "cusid", "s","ค้นหาชื่อเครื่องจักร หรือชื่อกระบวนการผลิต",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มการผลิต--")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec)
            . "</div>";
}
    
$content .= $menu->showfooter();
echo $content;

