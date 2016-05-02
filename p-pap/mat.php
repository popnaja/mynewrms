<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$pagename = "วัตถุดิบ";

__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->extrascript = <<<END_OF_TEXT
<script>
function mat_name(){
    if($("#cat").val()==8){
        var val = $("#ptype option:selected").text()+" "+$("#size option:selected").text()+" "+$("#weight option:selected").text()+"g";
    } else {
        var val = $("#name").val();
    }
    $("#redirect").after("<input type='hidden' name='matname' value='"+val+"' />");
}
function check_mat(){
    $(document).ready(function(){
        $("#plot").on("change",function(){
            $("#lot").val($(this).val());
        });
        $("#pcost").on("change",function(){
            $("#cost").val($(this).val());
        });
    });
}
</script>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายจัดซื้อ",$pagename);

$form = new myform("papform","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$mid = filter_input(INPUT_GET,'mid',FILTER_SANITIZE_STRING);
if($action=="add"){
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $mat = array("0"=>"--กลุ่มวัตถุดิบ--")+$db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='mat_cat'");
    $paper_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_type' ORDER BY op_name ASC");
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size' ORDER BY op_name ASC");
    $paper_weight = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_weight' ORDER BY CAST(op_name AS DECIMAL) ASC");
    $res_cat = "sel-cat-".implode(" sel-cat-",array_diff(array_keys($mat),array(0,8)));
    $content .= "<h1 class='page-title'>เพิ่ม$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . $form->show_select("cat",$mat,"label-3070","กลุ่มวัตถุดิบ",null)
            . "<div class='sel-cat-8'>"
            . $form->show_select("ptype",$paper_type,"label-3070","ชนิด",null)
            . $form->show_select("size",$paper_size,"label-3070","ขนาด",null)
            . $form->show_select("weight",$paper_weight,"label-3070","แกรม",null)
            . $form->show_num("plot","",1,"","จำนวนแผ่นต่อห่อ<span class='v-unit'></span>","","label-3070")
            . $form->show_num("pcost","",0.01,"","ต้นทุน","","label-3070")
            . $form->show_select("pcost_t",$op_paper_costt,"label-3070","",null)
            . "</div><!-- .sel-cat-8 -->"
            . "<div class='$res_cat'>"
            . $form->show_text("name","name","","","ชื่อ","","label-3070")
            . $form->show_text("unit","unit","","","หน่วย","","label-3070")
            . $form->show_num("cost","",0.01,"","ต้นทุน (บาท/หน่วย)","","label-3070")
            . $form->show_num("lot","",1,"","ปริมาณสั่งซื้อขั้นต่ำ","","label-3070")
            . "</div><!-- .sel-cat-9 -->"
            . $form->show_num("lt","",1,"","ระยะเวลา สั่ง-จัดส่ง(วัน)","","label-3070");

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_mat")
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>"
            . "select_option_byval('cat');"
            . "check_mat();"
            . "</script>";
    $form->addformvalidate("ez-msg", array('lot','cost','lt'));
    $content .= $form->submitscript("$('#papform').submit(function(){mat_name();});")
            . "</div><!-- .col-100 -->";
} else if(isset($mid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_mat","mat_id",$mid)+$db->get_meta("pap_matmeta", "mat_id", $mid);
    $p_cost_base = (isset($info['paper_cost_base'])?$info['paper_cost_base']:null);
    $p_cost = (isset($info['paper_cost'])?$info['paper_cost']:"");
    //edit
    $mat = array("0"=>"--กลุ่มวัตถุดิบ--")+$db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='mat_cat'");
    $paper_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_type' ORDER BY op_name ASC");
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size' ORDER BY op_name ASC");
    $paper_weight = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_weight' ORDER BY CAST(op_name AS DECIMAL) ASC");
    $res_cat = "sel-cat-".implode(" sel-cat-",array_diff(array_keys($mat),array(0,8)));
    $content .= "<h1 class='page-title'>แก้ไข$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . $form->show_select("cat",$mat,"label-3070","กลุ่มวัตถุดิบ",$info['mat_cat_id'])
            . "<div class='sel-cat-8'>"
            . $form->show_select("ptype",$paper_type,"label-3070","ชนิด",$info['mat_type'])
            . $form->show_select("size",$paper_size,"label-3070","ขนาด",$info['mat_size'])
            . $form->show_select("weight",$paper_weight,"label-3070","แกรม",$info['mat_weight'])
            . $form->show_num("plot",$info['mat_order_lot_size'],1,"","จำนวนแผ่นต่อห่อ<span class='v-unit'></span>","","label-3070")
            . $form->show_num("pcost",$p_cost,0.01,"","ต้นทุน","","label-3070")
            . $form->show_select("pcost_t",$op_paper_costt,"label-3070","",$p_cost_base)
            . "</div><!-- .sel-cat-8 -->"
            . "<div class='$res_cat'>"
            . $form->show_text("name","name",$info['mat_name'],"","ชื่อ","","label-3070")
            . $form->show_text("unit","unit",$info['mat_unit'],"","หน่วย","","label-3070")
            . $form->show_num("lot",$info['mat_order_lot_size'],1,"","ปริมาณสั่งซื้อขั้นต่ำ(ล๊อต)","","label-3070")
            . $form->show_num("cost",$info['mat_std_cost'],0.01,"","ต้นทุน (บาท/หน่วย)","","label-3070")
            . "</div><!-- .sel-cat-9 -->"
            . $form->show_num("lt",$info['mat_std_leadtime'],1,"","ระยะเวลา สั่ง-จัดส่ง(วัน)","","label-3070");

    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_mat")
            . $form->show_hidden("mid","mid",$mid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "<script>select_option_byval('cat');"
            . "check_mat();"
            . "</script>";
    $form->addformvalidate("ez-msg", array('lot','cost','lt'));
    $content .= $form->submitscript("$('#papform').submit(function(){mat_name();});")
            . "</div><!-- .col-100 -->";
} else {
/* --------------------------------------------------   VIEW MAT ----------------------------------------------------------------------*/
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $cat = (isset($_GET['cat'])&&$_GET['cat']>0?$_GET['cat']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $iperpage = 15;
    
    //list
    $cats = $db->get_keypair("pap_option", "op_id", "op_name", "WHERE op_type='mat_cat'");
    
    //view
    $head = array("แก้ไข","กลุ่ม","ชื่อ","หน่วย","ปริมาณสั่งขั้นต่ำ","ต้นทุนต่อหน่วย","ระยะเวลาสั่งซื้อ(วัน)");
    $rec = $tbpdo->view_mat($pauth,$cat,$s,$page, $iperpage);
    $all_rec = $tbpdo->view_mat($pauth,$cat,$s);
    $max = ceil(count($all_rec)/$iperpage);
    $addhtml = "";
    if($pauth>1){
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
    
    $content .= "<h1 class='page-title'>$pagename $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาจากชื่อวัตถุดิบ",$s)
            . $tb->show_filter(current_url(), "cat", $cats, $cat,"--กลุ่มวัตถุดิบ--")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec)
            . "</div>";
}
    
$content .= $menu->showfooter();
echo $content;

