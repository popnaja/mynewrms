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
$menu->pageTitle = "PAP | User";
$menu->ascript[] = $root."js/pap.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
        h3 {
            margin-bottom:15px;
        }
        .role-main {
            background-color:rgb(220,220,220) !important;
        }
        #tb-menu select{
            padding:0 25px 0 5px;
        }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("ผู้ดูแลระบบ","กลุ่มผู้ใช้");

$form = new myform("ulogin","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$opid = filter_input(INPUT_GET,'opid',FILTER_SANITIZE_STRING);
if(isset($opid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $rinfo = $db->get_info("pap_option","op_id",$opid);
    $auth = json_decode($rinfo['op_value'],true);
    //edit
    $content .= "<h1 class='page-title'>แก้ไขกลุ่มผู้ใช้</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("name","name",$rinfo['op_name'],"เช่น Sales,Production","ชื่อกลุ่ม","","label-inline")
            . "<h3 class='section-title'>กำหนดเมนูที่สามารถใช้งานได้</h3>"
            . $form->show_select("adj_all",$op_authlevel,"label-3070","ปรับทั้งหมด",null)
            . "<script>adj_role();</script>";
    //table
    $i=1;
    $content .= "<div id='tb-menu' class='ez-table'><table>"
            . "<tr class='tb-head'>"
            . "<th>เมนู</th>"
            . "<th>ลำดับขั้นการใช้งาน</th>"
            . "</tr>";
    foreach($menu->full_menu as $k=>$v){
        if(!is_array($v)){
            $file = basename($v);
            $op = (isset($auth[$file])?$auth[$file]:0);
            $content .= "<tr>"
                . "<td>$k</td>"
                . "<td>"
                . $form->show_hidden("page_".$i,"page[]",$file)
                . $form->show_select("auth_".$i,$op_authlevel,"",null,$op,"","auth[]")
                . "</td>"
                . "</tr>";
            $i++;
        } else {
            $content .= "<tr class='role-main'><td colspan='2'>$k</td></tr>";
            foreach($v as $kk=>$vv){
                $file = basename($vv);
                $op = (isset($auth[$file])?$auth[$file]:0);
                $content .= "<tr class='tb-data'>"
                . "<td class='role-sub'>$kk</td>"
                . "<td>"
                . $form->show_hidden("page_".$i,"page[]",$file)
                . $form->show_select("auth_".$i,$op_authlevel,"",null,$op,"","auth[]")
                . "</td>"
                . "</tr>";
                $i++;
            }
        }
    }
    $content .= "</table></div><!-- .ez-table -->";
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_urole")
            . $form->show_hidden("opid","opid",$opid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'));
    $content .= $form->submitscript("$('#new').submit();");
} else {
    $content .= "<h1 class='page-title'>กลุ่มผู้ใช้</h1>"
                . "<div id='ez-msg'>".  showmsg() ."</div>";
    //check
    if($pauth>1){
        //add
        $content .= "<div class='col-50'>"
                . "<h3>เพิ่มกลุ่มผู้ใช้</h3>"
                . $form->show_st_form()
                . $form->show_text("name","name","","เช่น Sales,Production","ชื่อกลุ่ม","","label-inline")
                . "<h3 class='section-title'>กำหนดเมนูที่สามารถใช้งานได้</h3>"
                . $form->show_select("adj_all",$op_authlevel,"label-3070","ปรับทั้งหมด",null)
                . "<script>adj_role();</script>";
        $i=1;
        $content .= "<div id='tb-menu' class='ez-table'><table>"
                . "<tr class='tb-head'>"
                . "<th>เมนู</th>"
                . "<th>ลำดับขั้นการใช้งาน</th>"
                . "</tr>";
        foreach($menu->full_menu as $k=>$v){
            if(!is_array($v)){
                $file = basename($v);
                $content .= "<tr>"
                    . "<td>$k</td>"
                    . "<td>"
                    . $form->show_hidden("page_".$i,"page[]",$file)
                    . $form->show_select("auth_".$i,$op_authlevel,"",null,0,"","auth[]")
                    . "</td>"
                    . "</tr>";
                $i++;
            } else {
                $content .= "<tr class='role-main'><td colspan='2'>$k</td></tr>";
                foreach($v as $kk=>$vv){
                    $file = basename($vv);
                    $content .= "<tr class='tb-data'>"
                    . "<td class='role-sub'>$kk</td>"
                    . "<td>"
                    . $form->show_hidden("page_".$i,"page[]",$file)
                    . $form->show_select("auth_".$i,$op_authlevel,"",null,0,"","auth[]")
                    . "</td>"
                    . "</tr>";
                    $i++;
                }
            }
        }
        $content .= "</table></div><!-- .ez-table -->";

        $content .= $form->show_submit("submit","Add New","but-right")
                . $form->show_hidden("request","request","add_urole")
                . $form->show_hidden("redirect","redirect",$redirect);
        $form->addformvalidate("ez-msg", array('name'));
        $content .= $form->submitscript("$('#new').submit();")
                . "</div><!-- .col-50 -->";
    }
    
    
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    //view
    $head = array("กลุ่มผู้ใช้");
    if($pauth==1){
        $rec = $tbpdo->view_option("role_auth",1);
    } else {
        array_unshift($head,"แก้ไข");
        $rec = $tbpdo->view_option("role_auth");
    }
    
    $content .= "<div class='col-50'>"
            . $tb->show_table($head,$rec)
            . "</div><!-- .col-50 -->";
}
    
$content .= $menu->showfooter();
echo $content;

