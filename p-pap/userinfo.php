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
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | User";
$menu->extrascript = <<<END_OF_TEXT
<style>
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ","ข้อมูลผู้ใช้");
$form = new myform("papform","",PAP."request.php");
$md = new mymedia(PAP."request_ajax.php");

$uid = $_SESSION['upap'][0];
$urole = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='role_auth'");
//load
$uinfo = $db->get_info("pap_user", "user_id", $uid) + $db->get_meta("pap_usermeta","user_id",$uid);
$sign = (isset($uinfo['signature'])?$uinfo['signature']:"");
$signature = $md->media_view($sign,ROOTS,RDIR);

//update
$content .= "<h1 class='page-title'>แก้ไขข้อมูลผู้ใช้</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-100 max-720'>"
        . $form->show_text("uname","uname",$uinfo['user_login'],"ภาษาอังกฤษตัวอักษรเล็ก","ชื่อผู้ใช้","","label-3070")
        . $form->show_text("email","email",$uinfo['user_email'],"","Email","","label-3070")
        //. $form->show_select("urole", $urole, "label-3070", "กลุ่มผู้ใช้", $uinfo['user_auth'])
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
$content .= $form->submitscript("$('#papform').submit();")
        . "<script>"
        . "pass_strength('pass','repass','pass-indicator');"
        . "auto_rename('uname');"
        //. "$('#user_expired').datepicker({dateFormat: 'yy-mm-dd'});"
        . "</script>";

$content .= $menu->showfooter();
echo $content;