<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
$uid = $_SESSION['upap'][0];
$pagename = "รับสินค้าจ้างผลิต";

__autoload("papmenu");
__autoload("pappdo");
include_once("pdo/pdo_po.php");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$poid = filter_input(INPUT_GET,'poid',FILTER_SANITIZE_NUMBER_INT);
$dyid = filter_input(INPUT_GET,'dyid',FILTER_SANITIZE_NUMBER_INT);

if($action=="print"){
    include_once("ud/doc_default.php");
    $menu = new PAPmenu("th");
    $menu->__autoloadall("form");
    $menu->__autoloadall("table");
    $menu->pageTitle = "PAP | $pagename";
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
            . show_process_po($poid)
            . "</body>";
    echo $content;
    exit;
}

$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->astyle[] = $root."css/status.css";
$menu->astyle[] = $root."css/doc_default.css";
$menu->ascript[] = $root."js/mat_received.js";
$menu->extrascript = <<<END_OF_TEXT
        <style>
        #tb-receive .label-inline {
            margin-bottom:0;
        }
        #tb-receive th:first-child {
            width:40%;
        }
        #tb-po th:first-child,
        #tb-po th:nth-child(5) {
            width:50px;
        }
        #tb-deli-dt tr th:first-child{
            width:50px;
        }
        </style>
END_OF_TEXT;

$tb = new mytable();
$db = new PAPdb(DB_PAP);
$pdo_po = new pdo_po();
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();
$content = $menu->showhead();
$content .= $menu->pappanel("ฝ่ายคลัง",$pagename);

if(isset($poid)){
    //check
    if($pauth<2){
        header("location:$redirect");
        exit();
    }
    /* -------------------------------------------------------- RECEVING --------------------------------------------------------------*/
    include_once("ud/doc_default.php");

    //load info
    $info = $db->get_info("pap_process_po", "po_id", $poid);
    $head = array("รายการ","จำนวนสั่ง","ยอดรับ","ที่เก็บ");
    $rec = $pdo_po->view_process_rc($poid);

    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_text("pocode","pocode",$info['po_code'],"","รหัส PO","","label-3070 readonly",null,"readonly")
            . $form->show_text("docref","docref","","","รหัสใบส่งของ $req","","label-3070")
            . $form->show_textarea("remark",$info['po_remark'],4,10,"","หมายเหตุ","label-3070")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . $form->show_select("all", array("yes"=>"ใช่","no"=>"มาบางส่วน"), "label-3070", "รับตามยอดใบจ้างผลิต", null)
            . $tb->show_table($head, $rec, "tb-receive")
            . "</div><!-- .col-50 -->";

    $content .= $form->show_submit("submit","รับของเข้า","but-right")
        . $form->show_hidden("request","request","add_process_rc")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("redirect","redirect",$redirect)
        . $form->show_hidden("poid","poid",$poid)
        . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('docref'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "adj_rec();"
            . "</script>"
            . show_process_po($poid);
} else if(isset($dyid)){
    /* --------------------------------------------------   VIEW DELIVERY DT ----------------------------------------------------------*/
    $dyinfo = $pdo_po->view_ppo_deli($dyid);
    $head = array("ลำดับ","รายการ","งาน","จำนวน","ที่เก็บ");
    $dt = $pdo_po->view_ppo_deli_dt($dyid);

    if($pauth>3){
        $del = "<span id='del-process-deli' class='red-but'>Delete</span>"
                    . $form->show_hidden("redirect","redirect",$redirect)
                    . $form->show_hidden("dyid","dyid",$dyid)
                    . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
                    . "<script>del_process_deli();</script>";
    } else {
        $del = "";
    }

    $content .= "<h1 class='page-title'>รายละเอียด $pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-50'>"
            . $tb->show_vtable($dyinfo,"tb-deli-info")
            . "</div><!-- .col-50 -->"
            . "<div class='col-100'>"
            . $tb->show_table($head, $dt, "tb-deli-dt")
            . $del
            . "</div><!-- .col-100 -->";
} else {
    /* --------------------------------------------------   VIEW PO FOR WH ----------------------------------------------------------*/
    //GET
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $iperpage = 20;

    //filter
    $rst = $op_ppo_status;
    unset($rst[1]);
    unset($rst[2]);
    unset($rst[9]);

    //view
    $head = array("รหัส","รหัส Supplier","วันที่สั่ง","สถานะ","ใบรับเข้า");
    $rec = $pdo_po->view_ppo_rc($pauth,$op_po_status_icon, $status, $mm, $s,$page, $iperpage);
    $all_rec = $pdo_po->view_ppo_rc($pauth,$op_po_status_icon, $status, $mm,$s);
    $max = ceil(count($all_rec)/$iperpage);

    $arrmm = $db->get_keypair("pap_process_po", "DATE_FORMAT(po_created,'%m%Y')", "DATE_FORMAT(po_created,'%b-%Y')", "");

    if($pauth>1){
        array_unshift($head, "รับเข้า");
    }

    $content .= "<h1 class='page-title'>$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "scid", "s","ค้นหาจากรหัสใบจ้างผลิต",$s)
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$rst, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_mm", $arrmm, $mm,"เดือน")
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-po")
            . "<div class='tb-legend'>"
            . my_legend($rst,$op_po_status_icon)
            . "</div>"
            . "</div><!-- .col-100 -->"
            . "<script></script>";
}
$content .= ($action=="print"?"":$menu->showfooter());
echo $content;
