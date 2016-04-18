<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$pagename = "วงเงินลูกค้า";
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
__autoload("pdo_tb");
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | $pagename";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->ascript[] = $root."js/acc.js";
$menu->extrascript = <<<END_OF_TEXT
END_OF_TEXT;

$tb = new mytable();
$tbpdo = new tbPDO();
$db = new PAPdb(DB_PAP);
$form = new myform("papform","",PAP."request.php");
$req = $form->show_require();

$content = $menu->showhead();
$content .= $menu->pappanel("บัญชี",$pagename);

$cid = filter_input(INPUT_GET,'cid',FILTER_SANITIZE_NUMBER_INT);

if(isset($cid)){
/*----------------------------------------------------- VIEW CUSTOMER -------------------------------------------------------------------*/
    
} else {
/*----------------------------------------------------- VIEW ALL CUSTOMER -------------------------------------------------------------------*/
    $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
    $s_cus = (isset($_GET['s_cus'])&&$_GET['s_cus']!=""?$_GET['s_cus']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;

    //view
    __autoloada("term");
    $termdb = new myterm(DB_PAP);
    $cats = $termdb->get_parent("customer");
    $head = array("ลูกค้า","กลุ่ม","วงเงิน","ระหว่างผลิต","รอชำระ");
    $rec = $tbpdo->view_cus_credit($pauth,$cat,$s_cus,$page,$iperpage);
    $all_rec = $tbpdo->view_cus_credit($pauth,$cat,$s_cus);
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<h1 class='page-title'>$pagename </h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . $tb->show_search(current_url(), "cusid", "s_cus","ค้นหาลูกค้า",$s_cus)
            . $tb->show_filter(current_url(), "fil_cat", $cats, $cat,"กลุ่มลูกค้า")
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-cus-credit");

    $content .= $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect."?action=add")
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "customer_search();"
            . "</script>";
}
$content .= $menu->showfooter();
echo $content;

