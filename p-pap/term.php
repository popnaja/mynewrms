<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$tax = filter_input(INPUT_GET,'tax',FILTER_SANITIZE_STRING);
$root = PAP;
$redirect = $root.basename(__FILE__)."?tax=$tax";
__autoload("papmenu");
__autoload("pappdo");
__autoloada("term");
$termdb = new myterm(DB_PAP);
$submenu = $op_tax_name[$tax][1];
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $submenu";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = PAP."js/papterm.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
    .but-right {
        width:auto;
    }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel($op_tax_name[$tax][0],$submenu);

$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$tid = filter_input(INPUT_GET,'tid',FILTER_SANITIZE_STRING);

if(isset($tid)){
    if($pauth<2){
        header("location:$redirect");
        exit();
    }
/*-----------------------------------------------------------------------------EDIT ------------------------------------------------*/
    $info = $termdb->get_terminfo($tax,$tid);
    $parents = array(0=>"--ไม่มี--")+$termdb->get_parent($tax,$info['lineage']);
    $content .= "<h1 class='page-title'>$submenu</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . $form->show_st_form()
            . $form->show_text("name","name",$info['name'],"",$submenu.$req,"","label-inline")
            . $form->show_text("slug","slug",$info['slug'],"ภาษาอังกฤษ หรือ ตัวเลข 1-5 ตัวอักษร","รหัสกลุ่ม".$req,"","label-inline")
            . $form->show_select("parent", $parents, "label-inline", "กลุ่มใหญ่", $info['parent'])
            . $form->show_textarea("des",$info['des'],4,10,"","คำอธิบาย","label-inline")
            . $form->show_hidden("tax","tax",$tax)
            . $form->show_hidden("tid","tid",$tid)
            . $form->show_hidden("oparent","oparent",$info['parent']);
    if($pauth==4){
        $del = "<span id='del-term' class='red-but'>Delete</span>"
                    . "<script>del_term();</script>";
    } else {
        $del = "";
    }
    $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_term")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name',"slug"));
    $content .= $form->submitscript("$('#papform').submit();")
            . "</div><!-- .col-50 -->"
            . "<script>"
            . "mod_slug();"
            . "</script>";
} else {
    $content .= "<h1 class='page-title'>$submenu</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    if($pauth>1){
/*-----------------------------------------------------------------------------ADD ------------------------------------------------*/
        $parents = array(0=>"--ไม่มี--")+$termdb->get_parent($tax);
        $content .= "<div class='col-50'>"
                . "<h3>เพิ่ม $submenu</h3>"
                . $form->show_st_form()
                . $form->show_text("name","name","","",$submenu.$req,"","label-inline")
                . $form->show_text("slug","slug","","ภาษาอังกฤษ หรือ ตัวเลข 1-5 ตัวอักษร","รหัสกลุ่ม".$req,"","label-inline")
                . $form->show_select("parent", $parents, "label-inline", "กลุ่มใหญ่", null)
                . $form->show_textarea("des","",4,10,"","คำอธิบาย","label-inline")
                . $form->show_hidden("tax","tax",$tax);

        $content .= $form->show_submit("submit","Add New","but-right")
                . $form->show_hidden("request","request","add_term")
                . $form->show_hidden("redirect","redirect",$redirect);
        $form->addformvalidate("ez-msg", array('name',"slug"));
        $content .= $form->submitscript("$('#new').submit();")
                . "</div><!-- .col-50 -->"
                . "<script>"
                . "mod_slug();"
                . "</script>";
    }
    
    //show terms
    $tb = new mytable();
    //view
    $head = array("กลุ่ม","รหัส","คำอธิบาย");
    $rec = $termdb->view_term($pauth, $tax);
    //check
    if($pauth>1){
        array_unshift($head,"แก้ไข");
    }
    $content .= "<div class='col-50'>"
            . $tb->show_table($head,$rec,"tb-prod-cat")
            . "</div><!-- .col-50 -->";
}
    
$content .= $menu->showfooter();
echo $content;