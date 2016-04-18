<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root."process_cat.php";
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | Process Cat";
$menu->extrascript = <<<END_OF_TEXT
<style>
        .ez-table {
            margin-bottom:25px;
        }
</style>
END_OF_TEXT;
$pagename = "กลุ่มกระบวนการ";
$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ",$pagename);

$form = new myform("process","",PAP."request.php");
$pcid = filter_input(INPUT_GET,'pcid',FILTER_SANITIZE_STRING);
if(isset($pcid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $rinfo = $db->get_info("pap_process_cat","id",$pcid);
    //edit
    $content .= "<h1 class='page-title'>แก้ไข$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("name","name",$rinfo['name'],"","ชื่อ$pagename","","label-inline");
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_process_cat")
            . $form->show_hidden("pcid","pcid",$pcid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'));
    $content .= $form->submitscript("$('#new').submit();");
} else {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $content .= "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . $form->show_st_form()
            . "<h2 class='page-title'>เพิ่ม$pagename</h2>"
            . $form->show_text("name","name","","","ชื่อกระบวนการ","","label-3070");

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_process_cat")
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name'));
    $content .= $form->submitscript("$('#new').submit();")
            . "</div><!-- .col-50 -->";
    
    //view
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    $content .= "<div class='col-50'>"
            . "<h2 class='page-title'>$pagename</h2>";

    $head = array("แก้ไข","กลุ่มกระบวนการผลิต");
    $rec = $tbpdo->view_process_cat();
    
    $content .= $tb->show_table($head,$rec)
            . "</div><!-- .col-50 -->";
}
    
$content .= $menu->showfooter();
echo $content;