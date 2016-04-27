<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$pagename = "ใบสั่งงาน";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$oid = filter_input(INPUT_GET,'oid',FILTER_SANITIZE_NUMBER_INT);
$d = filter_input(INPUT_GET,'d',FILTER_SANITIZE_STRING);

if($action=="print"){
    include_once("ud/doc_default.php");
    $menu = new PAPmenu("th");
    $menu->pageTitle = "PAP | ใบสั่งผลิต";
    $menu->astyle[] = $root."css/doc_default.css";
    $menu->extrascript = <<<END_OF_TEXT
<style>
body {
    background-color:#fff;
    margin:0;
    padding:0;
}
</style>
END_OF_TEXT;
    $content = $menu->showhead()
            . show_order($oid);
    echo $content;
    exit;
}

$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | Quotation";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->ascript[] = $root."js/pap.js";
$menu->ascript[] = $root."js/order.js";
$menu->astyle[] = $root."css/status.css";
$menu->astyle[] = $root."css/order.css";
$menu->astyle[] = $root."css/doc_default.css";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;

$form = new myform("papform","",PAP."request.php");
$md = new mymedia(PAP."request_ajax.php");

$content = $menu->showhead();
if(isset($d)){
    $mm = "ฝ่ายกราฟฟิก";
} else {
    $mm = "ฝ่ายผลิต";
}
$content .= $menu->pappanel($mm,$pagename);


if(isset($oid)){
    //check
    if($pauth<2){
        header("location:$redirect");
        exit();
    }
    /* ------------------------------------------------- Edit ใบสั่งผลิต -----------------------------------------------------------------*/
    __autoload("pdo_report");
    $rp = new reportPDO();
    $info = $rp->rp_order($oid);
    $comps = $rp->rp_order_comp($oid);
    $paperhtml = "";
    for($i=0;$i<count($comps);$i++){
        if(count($comps)>1&&$comps[$i]['type']==9){
            continue;
        } else {
            $paperhtml .= $form->show_select("comp_$i",$op_paper_div,"label-3070",$comps[$i]['name'],$comps[$i]['paper_cut'],"","comp[]")
                    . $form->show_text("printsize_$i","printsize[]",$comps[$i]['print_size'],"","ขนาดพิมพ์","","label-3070")
                    . $form->show_num("allo_$i", $comps[$i]['allowance'], 1, "", "กระดาษเผื่อ", "", "label-3070", "min='0'", "allo[]")
                . $form->show_hidden("compo_$i", "compo[]", $comps[$i]['paper_cut'])
                . $form->show_hidden("compid_$i", "compid[]", $comps[$i]['id'])
                . $form->show_hidden("clay_$i", "clay[]", $comps[$i]['paper_lay']);
        }
    }
    //pic
    $tpic = $md->media_view($info['picture'],ROOTS,RDIR);
    $content .= "<h1 class='page-title'>แก้ไข$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . $form->show_st_form()
            . "<div class='label-inline'>"
            . "<label for='sig'>ภาพประกอบ</label>"
            . "<div>"
            . $md->show_input("job_pic","job_pic",$tpic)
            . "</div>"
            . "</div><!-- .label-inline -->"
            . $form->show_hidden("ori_media","ori_media",$info['picture'])
            . $form->show_hidden("order_no","order_no",$info['order_no'])
            . $form->show_text("due","due",$info['plan_delivery'],"","กำหนดส่ง","","label-inline")
            . "<div class='form-section'>"
            . "<h4>ผ่ากระดาษก่อนพิมพ์</h4>"
            . $paperhtml
            . "</div><!-- .form-section -->"
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-inline");

    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_order")
            . $form->show_hidden("oid","oid",$oid)
            . $form->show_hidden("qid","qid",$info['qid'])
            . $form->show_hidden("redirect","redirect",$redirect."?oid=$oid");
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#due').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>"
            . "</div><!-- .col-50 -->";
    //show info
    include_once("ud/doc_default.php");
    $content .= show_order($oid,true);
} else {
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $iperpage = 20;
    /* ------------------------------------------------- Graphic view -----------------------------------------------------------------*/
    if(isset($d)){
        $head = array("แก้ไข","ชื่องาน","ลูกค้า","ยอดผลิต","กำหนดส่งงาน","แผนPlate","สถานะ","Plate");
        $all_rec = $tbpdo->view_order_ga($pauth,$op_plan_status_icon,$op_job_status, $status,$s);
        $rec = $tbpdo->view_order_ga($pauth,$op_plan_status_icon, $op_job_status,$status,$s, $page, $iperpage);
        $max = ceil(count($all_rec)/$iperpage);
        $ga_plan = $op_plan_status;
        unset($ga_plan[3]);

        $content .= "<h1 class='page-title'>$pagename</h1>"
                . "<div id='ez-msg'>".  showmsg() ."</div>"
                . "<div class='col-100'>"
                . $tb->show_search(current_url(), "scid", "s","ค้นหารหัสหรือชื่องาน",$s)
                . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
                . $form->show_hidden("redirect","redirect",$redirect)
                . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_ga, $status,"สถานะ")
                . $tb->show_pagenav(current_url(), $page, $max)
                . $tb->show_table($head,$rec,"tb-order")
                . "<div class='tb-legend'>"
                . my_legend($ga_plan, $op_plan_status_icon)
                . "</div>"
                . "</div><!-- .col-100 -->"
                . "<script>"
                . "order_search();"
                . "ga_function();"
                . "</script>";

        $box = "<h4>เปลียนสถานะงาน</h4>"
                . "<div id='box-msg'></div>"
                . $form->show_st_form()
                . $form->show_select("status",$op_job_ga,"label-3070","สภานะงาน",null)
                . "<div class='sel-status-2 sel-status-7'>"
                . $form->show_text("date", "date", "", "", "วันที่", "", "label-3070")
                . "</div>"
                . $form->show_hidden("oid","oid","0")
                . $form->show_submit("submit","กำหนด","but-right")
                . $form->show_hidden("request","request","update_ga_status")
                . $form->show_hidden("redirect","redirect",$redirect."?d=ga");
        $box .= $form->submitscript("check_ga_status(e);")
                ."<script>"
                . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
                . "select_option_byval('status');"
                . "</script>";

        $content .= $form->show_float_box($box,"date-box");

    /* ------------------------------------------------- Production view -----------------------------------------------------------------*/
    } else {
        $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
        //$arrcat = $db->get_quote_kv("cat_id");
        //$arrmm = $db->get_quote_month();

        //view
    $head = array("แก้ไข","ชื่องาน","ลูกค้า","หน้า","ยอดผลิต","กำหนดส่ง","เพลต","กระดาษ","แผนผลิต");
    $rec = $tbpdo->view_order($pauth,$op_plan_status_icon,$status, $s, $page, $iperpage);
    $all_rec = $tbpdo->view_order($pauth,$op_plan_status_icon, $status, $s);
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหารหัสหรือชื่องาน",$s)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_job_status, $status,"สถานะ")
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-order")
            . "<div class='tb-legend'>"
            . my_legend($op_plan_status,$op_plan_status_icon)
            . "</div>"
            . "</div><!-- .col-100 -->"
            . "<script>order_search();</script>";
    }
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;
