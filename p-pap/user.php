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
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | User";
$menu->extrascript = <<<END_OF_TEXT
<style>
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ","รายการผู้ใช้");
$form = new myform("ulogin","",PAP."request.php");
$md = new mymedia(PAP."request_ajax.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_STRING);
if($action=="add"){
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }

    $urole = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='role_auth'");
    //add
    $content .= "<h1 class='page-title'>เพิ่มผู้ใช้</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("uname","uname","","ภาษาอังกฤษตัวอักษรเล็ก","ชื่อผู้ใช้","","label-3070")
            . $form->show_text("email","email","","","Email","","label-3070")
            . $form->show_select("urole", $urole, "label-3070", "กลุ่มผู้ใช้", 2)
            . $form->show_text("pass","pass","","password","รหัสผ่าน","","label-3070","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","ยืนยันรหัสผ่าน","","label-3070","password"," maxlength='32'")
            . "<div class='label-3070'>"
            . "<label for='sig'>ลายเซ็นต์</label>"
            . "<div>"
            . "<div id='sig-box'></div>"
            . $md->show_input("sig","sig","")
            . "</div>"
            . "</div><!-- .label-3070 -->"
            . "<div id='pass-indi-box'><div id='pass-indicator' class='p-indi'>Strength Indicator</div></div>";
    
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_user")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
            
    $form->addformvalidate("ez-msg", array('uname','email','pass'),array("pass","repass"),"email");
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "pass_strength('pass','repass','pass-indicator');"
            . "auto_rename('uname');"
            //. "$('#user_expired').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else if(isset($uid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    $urole = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='role_auth'");
    //load
    $uinfo = $db->get_info("pap_user", "user_id", $uid) + $db->get_meta("pap_usermeta","user_id",$uid);
    $sign = (isset($uinfo['signature'])?$uinfo['signature']:"");
    $signature = $md->media_view($sign,ROOTS,RDIR);
    
    //update
    $content .= "<h1 class='page-title'>แก้ไขข้อมูลผู้ใช้</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("uname","uname",$uinfo['user_login'],"ภาษาอังกฤษตัวอักษรเล็ก","ชื่อผู้ใช้","","label-3070")
            . $form->show_text("email","email",$uinfo['user_email'],"","Email","","label-3070")
            . $form->show_select("urole", $urole, "label-3070", "กลุ่มผู้ใช้", $uinfo['user_auth'])
            . $form->show_text("pass","pass","","password","รหัสผ่าน","","label-3070","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","ยืนยันรหัสผ่าน","","label-3070","password"," maxlength='32'")
            . $form->show_hidden("ori_media","ori_media",$sign)
            . "<div class='label-3070'>"
            . "<label for='sig'>ลายเซ็นต์</label>"
            . "<div>"
            . $md->show_input("sig","sig",$signature)
            . "</div>"
            . "</div><!-- .label-3070 -->"
            . "<div id='pass-indi-box'><div id='pass-indicator' class='p-indi'>Strength Indicator</div></div>";

    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_user")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
            
    $form->addformvalidate("ez-msg", array('uname','email'),null,"email");
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "pass_strength('pass','repass','pass-indicator');"
            . "auto_rename('uname');"
            //. "$('#user_expired').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else {
    __autoload("pdo_tb");
    $tb = new mytable();
    $tbpdo = new tbPDO();
    
    $cat = (isset($_GET['cat'])&&$_GET['cat']>0?$_GET['cat']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    
    $head = array("แก้ไข","ผู้ใช้","กลุ่มผู้ใช้","วันที่ลงทะเบียน");
    $all_rec = $tbpdo->view_user($pauth,$cat,$s);
    $rec = $tbpdo->view_user($pauth,$cat,$s,$page,$iperpage);
    $max = ceil(count($all_rec)/$iperpage);
    
    //list
    $cats = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='role_auth'");
    
    //view
    $addhtml = "";
    if($pauth>1){
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
    
    $content .= "<h1 class='page-title'>รายการผู้ใช้ $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "cusid", "s","ค้นหา",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มผู้ใช้--")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec)
            . "</div>";
}
    
$content .= $menu->showfooter();
echo $content;

