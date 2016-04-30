<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$uid = $_SESSION['upap'][0];
$redirect = $root.basename(__FILE__);
$pagename = "ใบเสนอราคา";
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$qid = filter_input(INPUT_GET,'qid',FILTER_SANITIZE_STRING);
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
            . show_quote_df($qid)
            . "</body>";
    echo $content;
    exit;
}

$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->__autoloadall("media");
$menu->pap_menu();
$menu->pageTitle = "PAP | Quotation";
$menu->ascript[] = $root."js/pap.js";
$menu->ascript[] = AROOTS."js/autocomplete.js";
$menu->astyle[] = $root."css/quotation.css";
$menu->astyle[] = $root."css/status.css";
$menu->astyle[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = AROOTS."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
#print-quote {
        margin-top:10px;
    }
#scid-res li {
    width:100%;
}
</style>
END_OF_TEXT;

$form = new myform("papform","",PAP."request.php");
$ajax = PAP."request_ajax.php";
$content = $menu->showhead();
$content .= $menu->pappanel("เสนอราคา",$pagename);
//prep info
if($action=="add"||isset($qid)){
    $process_keypair = $db->get_process_keypair();
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $job_size = array("0"=>"--ขนาดชิ้นงาน--")+$db->get_keypair("pap_size", "size_id", "CONCAT(size_name,' (',size_height,'x',size_width,')')");
    $paper = array("0"=>"--กระดาษ--")+$db->get_paper_keypair("mat_type");
    $gram = array("0"=>"--แกรม--")+$db->get_paper_keypair("mat_weight");

    $prepress = $process_keypair[1];
    $binding = array("0"=>"--ไม่มี--")+$process_keypair[9];
    $coating = array("0"=>"--ไม่มี--")+$process_keypair[4];
    $print = $process_keypair[3];
    $after =  $process_keypair[5];
    $packing = $process_keypair[11];
    $shipping = $process_keypair[12];
    $fold = $process_keypair[7];
}

if($action=="add"){
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
/*----------------------------------------------------------------- ADD  ---------------------------------------------------------*/
    $content .= "<h1 class='page-title'>สร้าง$pagename</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่องาน","","label-3070")
            . $form->show_text("scid","scid","","ค้นหา 3 ตัวอักษรขึ้นไป","บริษัท","","label-3070")
            . $form->show_hidden("cid","cid","0")
            . "<div id='cus_ct'></div>"
            . $form->show_select("type",$product_type,"label-3070","ประเภทงาน",null)
            . $form->show_text("search_size","search_size","","ค้นหา 2 ตัวอักษรขึ้นไป","ขนาดชิ้นงาน","","label-3070")
            . $form->show_hidden("sid","sid",0)
            . $form->show_num("amount","",1,"","ยอดพิมพ์","","label-3070","min=1")
            . $form->show_hidden("status","status","1");

    $detail = $form->show_checkbox("prepress","prepress",$prepress,"การจัดทำต้นฉบับ","label-3070")
            . $form->show_checkbox("exclude","exclude",$op_quote_ex,"อื่นๆ","label-3070")
            . "<div id='bind-sec'>"
            . $form->show_select("binding",$binding,"label-3070","เข้าเล่ม",null)
            . "</div><!-- #bind-sec -->"
            . $form->show_text("due","due","","yyyy-mm-dd","กำหนดส่ง","","label-3070")
            . $form->show_textarea("remark","",4,10,"","หมายเหตุ","label-3070");

    $pack = $form->show_checkbox("pack","pack",$packing,"การแพ็ค","label-3070")
            . $form->show_checkbox("ship","ship",$shipping,"การขนส่ง","label-3070")
            . $form->show_num("distance","",0.01,"","ระยะทาง(กม)","","label-3070");

    $multi = "";
    for($x=1;$x<11;$x++){
        $multi .= $form->show_num("m_amount_$x","",1,"","ยอด $x","","label-3070","min=0","m_amount[]");
    }

    $content .= $form->show_tabs("q-other",array("เงื่อนไข","แพ็คและขนส่ง","ยอดพิมพ์"),array($detail,$pack,$multi))
            . "</div><!-- .col-50 -->";

    $content .= "<div class='col-50'>"
            . "<div class='sel-type-10 sel-type-69'>"
            . "<div class='form-section'>"
            . "<h4>ปก</h4>"
            . $form->show_hidden("comp_type","comp_type[]","0")
            . $form->show_text("csize","csize","","","กระดาษ","","label-3070 readonly",null,"readonly")
            . "<div class='tg_c_ptype'>"
                . $form->show_select("paper_type",$paper,"label-3070","ชนิด",null,"","paper_type[]")
            . "</div>"
            . "<div class='tg_c_pgram'>"
                . $form->show_select("paper_gram",$gram,"label-3070","แกรม",null,"","paper_gram[]")
            . "</div>"
            . $form->show_select("print",$print,"label-3070","ปกนอก",null,"","print[]")
            . $form->show_select("print2",array("0"=>"--ไม่มี--")+$print,"label-3070","ปกใน",null,"","print2[]")
            . $form->show_select("coating",$coating,"label-3070","เคลือบผิว",null,"","coating[]")
            . $form->show_select("other",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ไดคัท",null,"","other[]")
            . "<div class='sel-other-1'>"
            . $form->show_checkbox("post","post",$after,"ไดคัท และอื่นๆ","label-3070")
            . "</div><!-- .sel-other-1 -->"
            . $form->show_select("cwing",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ปกปีก",null,"","cwing")
            . "<div class='sel-cwing-1'>"
            . $form->show_num("fwing","",0.01,"","ปีกปกหน้า(cm)","","label-3070")
            . $form->show_num("bwing","",0.01,"","ปีกปกหลัง(cm)","","label-3070")
            . "</div><!-- .sel-cwing-1 -->"
            . $form->show_hidden("page","page[]",1)
            . "</div><!-- .form-section -->"
            . "</div><!-- .sel-type-10 -->"
            . "<script>"
            . "select_option('other');"
            . "select_option('cwing');"
            . "</script>";
    for($i=0;$i<5;$i++){
        $hid = ($i==0?"":"form-hide");
        $content .= "<div class='form-section quote-comp $hid'>"
            . "<h4 id='sel-name'>เนื้อใน</h4>"
            . $form->show_hidden("comp_type_$i","comp_type[]","1")
            . $form->show_text("isize_$i","isize[]","","","กระดาษ","","label-3070 readonly",null,"readonly")
            . "<div class='tg_i_ptype'>"
                . $form->show_select("paper_type_$i",$paper,"label-3070 in_ptype","ชนิด",null,"","paper_type[]")
            . "</div>"
            . "<div class='tg_i_pgram'>"
                . $form->show_select("paper_gram_$i",$gram,"label-3070 in_pgram","แกรม",null,"","paper_gram[]")
            . "</div>"
            . $form->show_select("print_$i",$print,"label-3070","สี",null,"","print[]")
            . $form->show_select("coating_$i",$coating,"label-3070","เคลือบผิว",null,"","coating[]")
            . $form->show_select("other_$i",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ไดคัท",null,"","other[]")
            . "<div class='sel-other_$i-1'>"
            . $form->show_checkbox("post_$i","post_$i",$after,"ไดคัท และอื่นๆ","label-3070")
            . "</div>"
            . $form->show_select("folding_$i",$fold,"label-3070 form-hide","พับ",null,"","folding[]")
            . $form->show_num("page_$i","",1,"","จำนวนหน้า","","label-3070","min=0","page[]")
            . "</div><!-- .form-section -->"
            . "<script>select_option('other_$i');</script>";
    }
    $content .= "<input id='view-more-but' type='button' value='เพิ่มเนื้อใน' style='width:100%'/>"
            . "</div><!-- .col-50 -->";

    $content .= $form->show_submit("submit","สร้าง","but-right")
            . $form->show_hidden("request","request","add_quote")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $form->show_hidden("pauth","pauth",$pauth)
            . $form->show_hidden("uid","uid",$uid);
    $form->addformvalidate("ez-msg", array('name',"cusct",'amount'),null,null,array('cid','type','sid'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#due').datepicker({dateFormat: 'yy-mm-dd'});"
            . "view_more_section('quote-comp');"
            . "select_option_byval('type');"
            . "quote_function();"
            . "search_size('$ajax');"
            . "</script>"
            . "</div><!-- .col-100 -->";
} else if(isset($qid)) {
    //check edit
    $info = $db->get_quote_allinfo($qid);
    $salerep = $db->get_info("pap_sale_cus","cus_id",$info['customer_id']);
    if($pauth<=1){
        header("location:$redirect");
        exit();
    } else if($pauth<4&&$uid!=$salerep['user_id']){
        header("location:$redirect");
        exit();
    }
/*----------------------------------------------------------------- EDIT  -------------------------------------------------------------*/
    //load
    __autoload("pdo_tb");
    include_once("quote_formular.php");
    $tb = new mytable();

    $comps = $db->get_comp($qid);
    $layinfo = $db->get_layinfo($info['job_size_id']);

    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$info['customer_id']);
    $cover = $db->get_comp($qid,true);
    $inside = $db->get_comp($qid,false);
    if(isset($cover[0])){
        $cover_post = explode(",",$cover[0]['comp_postpress']);
        $sel_c_pgram = array("0"=>"--แกรม--")+$db->get_paper_keypair("mat_weight",$layinfo['cover_paper'],$cover[0]['comp_paper_type']);
        $show_c_post = ($cover_post[0]>0?1:0);
    } else {
        $cover_post = array();
        $sel_c_pgram = array("0"=>"--แกรม--");
        $show_c_post = 0;
    }
    $prepress_checked = $form->checked_array($prepress, explode(",",$info['prepress']));
    $post_checked = $form->checked_array($after, $cover_post);
    $ex_checked = $form->checked_array($op_quote_ex, explode(",",$info['exclude']));
    $pack_checked = $form->checked_array($packing, (isset($info['packing'])?explode(",",$info['packing']):array()));
    $ship_checked = $form->checked_array($shipping, (isset($info['shipping'])?explode(",",$info['shipping']):array()));
    $sel_c_ptype = array("0"=>"--กระดาษ--")+$db->get_paper_keypair("mat_type",$layinfo['cover_paper']);
    $sel_i_ptype = array("0"=>"--กระดาษ--")+$db->get_paper_keypair("mat_type",$layinfo['inside_paper']);

    //show tb-cost
    if($pauth==4){
        $cinfo = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='cinfo'");
        $margin = $cinfo['margin'];
        $head = array("กลุ่มรายการ","รายการต้นทุน","จำนวน","ต้นทุนต่อหน่วย","ต้นทุนรวม","%margin","ราคารวม");
        $aamount = (isset($info['cal_amount'])&&$info['cal_amount']!=""?explode(",",$info['cal_amount']):array());
        array_unshift($aamount,$info['amount']);
        $tinfo = $info;
        $adata = array();
        $x = 0;
        foreach($aamount AS $am){
            $tinfo['amount'] = $am;
            $res = cal_quote($tinfo, $comps, $layinfo);
            if($x==0){
                $num = 0;
                foreach($res as $k=>$v){
                    foreach($v as $kk=>$vv){
                        $num += (isset($vv[3])?1:0);
                    }
                }
            }
            $adjmargin = array_slice(explode(",",$info['adj_margin']),$x*($num+1),$num+1);
            array_push($adata,$tb->show_quote_tb($head, $res,"tb-cost_$x",array(3),$margin,$adjmargin));
            $x++;
        }
        $cost_adj = "<h4>รายละเอียดต้นทุน</h4>"
            . $form->show_tabs("q-detail",$aamount,$adata,0);
        $del = "<span id='del-quote' class='red-but'>Delete</span>"
                    . "<script>del_quote();</script>";
    } else {
        $cost_adj = "";
        $del = "";
    }

    //ส่วนเปลี่ยนสถานะ
    $status_icon = $op_quote_status_icon[$info['status']];
    $md = new mymedia(PAP."request_ajax.php");
    $tpic = $md->media_view((isset($info['quote_sign_back'])?$info['quote_sign_back']:""),ROOTS,RDIR);
    if(isset($info['print_cost'])){
        $pcost = $info['print_cost'];
        $mar = number_format(($info['q_price']-$pcost)*100/$pcost,2);
    } else {
        $pcost = 0;
        $mar = "*";
    }
    $qstatus = $op_quote_status;
    if($pauth>3){
        $qprice = ""
        . $form->show_num("q_price",$info['q_price'],1,"","ราคา (Margin = <span class='show-margin'>$mar</span>%)","","label-3070 ","min='1' ")
        . $form->show_num("peru",$info['q_price']/$info['amount'],0.01,"","ราคาค่อหน่วย","","label-3070 ","min='0'")
        . $form->show_num("discount",$info['discount'],0.01,"","ส่วนลด","","label-3070 ","min='0' ");
    } else {
        unset($qstatus[2]);
        $qprice = $form->show_hidden("q_price","q_price",$info['q_price'])
                . $form->show_hidden("discount","discount",$info['discount']);
    }
    $file = $md->file_view((isset($info['quote_sign'])?$info['quote_sign']:""),ROOTS,RDIR);
    $ustatus = $form->show_select("status",$qstatus,"label-3070","สถานะ $status_icon",$info['status'])
            . "<div class='sel-status-9'>"
            . "<div class='label-3070'>"
            . "<label for='sig'>เอกสารยืนยัน</label>"
            . "<div>"
            . $md->show_uppdf("sign_doc", "sign_doc",$file)
            . "</div>"
            . "</div><!-- .label-3070 -->"
            . "</div><!-- .sel-status-9 -->"
            . $qprice
            . $form->show_hidden("ttcost","ttcost",$pcost)
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . "<input type='button' class='blue-but' value='Update' style='float:right' onClick='submit2();' />"
            . "<script>select_option_byval('status');</script>";
    //แสดงราคาแบบ แยกส่วน
    $phead = array("แสดง","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $prec = array();
    if(isset($info['detail_price'])){
        $plist = json_decode($info['detail_price'],true);
    } else {
        $plist = array();
    }
    for($i=0;$i<6;$i++){
        $dt = (isset($plist[$i])?$plist[$i]:array());
        $row = array(
            $form->show_select("pshow_$i",array(0=>"No",1=>"Yes"),"label-inline",null,(isset($dt[0])?$dt[0]:null),"","pshow[]"),
            $form->show_text("plist_$i","plist[]",(isset($dt[1])?$dt[1]:""),"",null,"","label-inline"),
            $form->show_num("pqty_$i",(isset($dt[2])?$dt[2]:""),0.01,"",null,"","label-inline","min=0","pqty[]"),
            $form->show_num("pperu_$i",(isset($dt[3])?$dt[3]:""),0.01,"",null,"","label-inline","min=0","pperu[]"),
            $form->show_num("ptt_$i",(isset($dt[4])?$dt[4]:""),0.01,"",null,"","label-inline","min=0","ptt[]")
        );
        array_push($prec,$row);
    }
    $pricetab = $tb->show_table($phead,$prec,"tb-pshow");
    //เสนอราคาหลายยอด
    $qhead = array("แสดง","ยอดพิมพ์","รายละเอียดอื่นๆ","ราคา");
    $qrec = array();
    if(isset($info['multi_quote_info'])){
        $qlist = json_decode($info['multi_quote_info'],true);
    } else {
        $qlist = array();
    }
    for($i=0;$i<10;$i++){
        $dt = (isset($qlist[$i])?$qlist[$i]:array());
        $row = array(
            $form->show_select("qshow_$i",array(0=>"No",1=>"Yes"),"label-inline",null,(isset($dt['show'])?$dt['show']:null),"","qshow[]"),
            $form->show_num("qqty_$i",(isset($dt['amount'])?$dt['amount']:""),1,"",null,"","label-inline","min=0","qqty[]"),
            $form->show_text("qlist_$i","qlist[]",(isset($dt['remark'])?$dt['remark']:""),"",null,"","label-inline"),
            $form->show_num("qtt_$i",(isset($dt['price'])?$dt['price']:""),1,"",null,"","label-inline","min=0","qtt[]")
        );
        array_push($qrec,$row);
    }
    $mquote = $tb->show_table($qhead,$qrec,"tb-qshow");


    //output to html
    $print_q = "<a href='quotation.php?action=print&qid=$qid' title='Print' class='icon-print' target='_blank'></a>";
    $content .= "<h1 class='page-title'>แก้ไข$pagename".$print_q."</h1>"
            . "<div id='ez-msg'>".  showmsg()."</div>"
            . "<div class='col-100'>"
            . $form->show_st_form(null, false, true)
            . $cost_adj
            . $form->show_tabs("view-tab",array("สถานะ","แสดงราคาแยกส่วน","เสนอราคาหลายยอด"),array($ustatus,$pricetab,$mquote),0)
            . "<div class='col-50'>"
            . $form->show_text("qno","qno",$info['quote_no'],"","รหัสใบเสนอราคา","","label-3070 readonly",null,"readonly")
            . $form->show_text("scid","scid",$info['customer_name'],"","บริษัท","","label-3070 readonly",null,"readonly")
            . $form->show_select("cusct", $contacts, "label-3070", "ผู้ติดต่อ", $info['contact_id'])
            . $form->show_text("name","name",$info['name'],"","ชื่องาน","","label-3070")
            . $form->show_select("type",$product_type,"label-3070","ประเภทงาน",$info['cat_id'])
            . $form->show_text("search_size","search_size",$job_size[$info['job_size_id']],"","ขนาดชิ้นงาน","","label-3070 readonly",null,"readonly")
            . $form->show_hidden("sid","sid",$info['job_size_id'])

            . $form->show_num("amount",$info['amount'],1,"","ยอดพิมพ์","","label-3070","min=1");

    $detail = $form->show_checkbox("prepress","prepress",$prepress_checked,"การจัดทำต้นฉบับ","label-3070")
            . $form->show_checkbox("exclude","exclude",$ex_checked,"อื่นๆ","label-3070")
            . "<div id='bind-sec'>"
            . $form->show_select("binding",$binding,"label-3070","เข้าเล่ม",$info['binding_id'])
            . "</div><!-- #bind-sec -->"
            . $form->show_text("due","due",$info['plan_delivery'],"yyyy-mm-dd","กำหนดส่ง","","label-3070")
            . $form->show_num("credit",$info['credit'],1,"","เครดิต(วัน)","","label-3070")
            . $form->show_textarea("remark",$info['remark'],4,10,"","หมายเหตุ","label-3070");
    $pack = $form->show_checkbox("pack","pack",$pack_checked,"การแพ็ค","label-3070")
            . $form->show_checkbox("ship","ship",$ship_checked,"การขนส่ง","label-3070")
            . $form->show_num("distance",(isset($info['distance'])?$info['distance']:""),0.01,"","ระยะทาง(กม)","","label-3070");

    $aamount = (isset($info['cal_amount'])?explode(",",$info['cal_amount']):"");
    $multi = "";
    for($x=1;$x<11;$x++){
        $multi .= $form->show_num("m_amount_$x",(isset($aamount[$x-1])?$aamount[$x-1]:""),1,"","ยอด $x","","label-3070","min=0","m_amount[]");
    }

    $content .= $form->show_tabs("q-other",array("เงื่อนไข","แพ็คและขนส่ง","ยอดพิมพ์"),array($detail,$pack,$multi),0)
            . "</div><!-- .col-50 -->";

    $content .= "<div class='col-50'>"
        . "<div class='sel-type-10 sel-type-69'>"
        . "<div class='form-section'>"
        . "<h4>ปก</h4>"
        . $form->show_hidden("comp_type","comp_type[]","0")
        . $form->show_text("csize","csize",$layinfo['csize'],"","กระดาษ","","label-3070 readonly",null,"readonly")
        . "<div class='tg_c_ptype'>"
        . $form->show_select("paper_type",$sel_c_ptype,"label-3070","ชนิด",(isset($cover[0])?$cover[0]['comp_paper_type']:null),"","paper_type[]")
        . "</div>"
        . "<div class='tg_c_pgram'>"
        . $form->show_select("paper_gram",$sel_c_pgram,"label-3070","แกรม",(isset($cover[0])?$cover[0]['comp_paper_weight']:null),"","paper_gram[]")
        . "</div>"
        . $form->show_num("allowance",(isset($cover[0])?$cover[0]['comp_paper_allowance']:null),1,"","เผื่อกระดาษเสีย(แผ่น)","","label-3070","min=0","allowance[]")
        . $form->show_select("print",$print,"label-3070","ปกนอก",(isset($cover[0])?$cover[0]['comp_print_id']:null),"","print[]")
        . $form->show_select("print2",array("0"=>"--ไม่มี--")+$print,"label-3070","ปกใน",(isset($cover[0])?$cover[0]['comp_print2']:null),"","print2[]")
        . $form->show_select("coating",$coating,"label-3070","เคลือบผิว",(isset($cover[0])?$cover[0]['comp_coating']:null),"","coating[]")
        . $form->show_select("other",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ไดคัท",$show_c_post,"","other[]")
        . "<div class='sel-other-1'>"
        . $form->show_checkbox("post","post",$post_checked,"ไดคัท และอื่นๆ","label-3070")
        . "</div>"
        . $form->show_select("cwing",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ปกปีก",$info['cwing'],"","cwing")
        . "<div class='sel-cwing-1'>"
        . $form->show_num("fwing",(isset($info['fwing'])?$info['fwing']:""),0.01,"","ปีกปกหน้า(cm)","","label-3070")
        . $form->show_num("bwing",(isset($info['bwing'])?$info['bwing']:""),0.01,"","ปีกปกหลัง(cm)","","label-3070")
        . "</div><!-- .sel-cwing-1 -->"
        . $form->show_hidden("page","page[]",(isset($cover[0])?$cover[0]['comp_page']:1))
        . "</div><!-- .form-section -->"
        . "</div><!-- .sel-type-10 -->"
        . "<script>"
        . "select_option('other');"
        . "select_option('cwing');"
        . "</script>";

    $show = count($inside);

    for($i=0;$i<5;$i++){
        $hid = ($i<$show?"":"form-hide");
        if(isset($inside[$i])){
            $ipost = explode(",",$inside[$i]['comp_postpress']);
            $other = ($ipost[0]>0?"1":"0");
            $pi_checked = $form->checked_array($after, $ipost);
        } else {
            $other = "0";
            $pi_checked = $after;
        }
        if(isset($inside[$i])){
            $sel_i_pgram = array("0"=>"--แกรม--")+$db->get_paper_keypair("mat_weight", $layinfo['inside_paper'], $inside[$i]['comp_paper_type']);
        } else {
            $sel_i_pgram = array("0"=>"--แกรม--");
        }
        $content .= "<div class='form-section quote-comp $hid'>"
        . "<h4 id='sel-name'>เนื้อใน</h4>"
        . $form->show_hidden("comp_type_$i","comp_type[]","1")
        . $form->show_text("isize_$i","isize[]",$layinfo['isize'],"","กระดาษ","","label-3070 readonly",null,"readonly")
        . "<div class='tg_i_ptype'>"
            . $form->show_select("paper_type_$i",$sel_i_ptype,"label-3070 in_ptype","ชนิด",(isset($inside[$i])?$inside[$i]['comp_paper_type']:null),"","paper_type[]")
        . "</div>"
        . "<div class='tg_i_pgram'>"
        . $form->show_select("paper_gram_$i",$sel_i_pgram,"label-3070 in_pgram","แกรม",(isset($inside[$i])?$inside[$i]['comp_paper_weight']:null),"","paper_gram[]")
        . "</div>"
        . $form->show_num("allowance_$i",(isset($inside[$i])?$inside[$i]['comp_paper_allowance']:null),1,"","เผื่อกระดาษเสีย(แผ่น)","","label-3070","min=0","allowance[]")
        . $form->show_select("print_$i",$print,"label-3070","สี",(isset($inside[$i])?$inside[$i]['comp_print_id']:null),"","print[]")
        . $form->show_select("coating_$i",$coating,"label-3070","เคลือบผิว",(isset($inside[$i])?$inside[$i]['comp_coating']:null),"","coating[]")
        . $form->show_select("other_$i",array("0"=>"--ไม่มี--","1"=>"มี"),"label-3070","ไดคัท",$other,"","other[]")
        . "<div class='sel-other_$i-1'>"
        . $form->show_checkbox("post_$i","post_$i",$pi_checked,"ไดคัท และอื่นๆ","label-3070")
        . "</div>"
        . $form->show_select("folding_$i",$fold,"label-3070 form-hide","พับ",(isset($info['folding'])?$info['folding']:null),"","folding[]")
        . $form->show_num("page_$i",(isset($inside[$i])?$inside[$i]['comp_page']:""),1,"","จำนวนหน้า","","label-3070","min=0","page[]")
        . "</div><!-- .form-section -->"
        . "<script>select_option('other_$i');</script>";
    }
    $content .= "<input id='view-more-but' type='button' value='เพิ่มเนื้อใน' style='width:100%'/>"
            . "</div><!-- .col-50 -->";

    $content .= $del
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_quote")
            . $form->show_hidden("qid","qid",$qid)
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name',"cusct",'amount'),null,null,array('cid','type','sid'));
    $content .= $form->submitscript("$('#papform').submit();")
            . "<script>"
            . "$('#due').datepicker({dateFormat: 'yy-mm-dd'});"
            . "view_more_section('quote-comp');"
            . "select_option_byval('type');"
            . "quote_function(".$layinfo['cover_paper'].",".$layinfo['inside_paper'].");"
            . "</script>"
            . "</div><!-- .col-100 -->";
} else {
/*----------------------------------------------------------------- VIEW ALL  ---------------------------------------------------------*/
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    $cat = (isset($_GET['fil_cat'])&&$_GET['fil_cat']>0?$_GET['fil_cat']:null);
    $status = (isset($_GET['fil_status'])&&$_GET['fil_status']!=0?$_GET['fil_status']:null);
    $sid = (isset($_GET['sid'])&&$_GET['sid']>0?$_GET['sid']:null);
    $mm = (isset($_GET['fil_mm'])&&$_GET['fil_mm']>0?$_GET['fil_mm']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $arrcat = $db->get_quote_kv("cat_id");
    $arrmm = $db->get_quote_month();
    $iperpage = 20;

    //view
    $head = array("พิมพ์","งาน","ลูกค้า","ราคา","ขนาด","หน้า","ยอดผลิต","วันที่สร้าง","สถานะ");
    $rec = $tbpdo->view_quote($pauth,$op_quote_status_icon, $cat, $status, $mm, ($pauth>3?$sid:$uid),$page, $iperpage);
    $all_rec = $tbpdo->view_quote($pauth,$op_quote_status_icon, $cat, $status, $mm,($pauth>3?$sid:$uid));
    $sale = array("0"=>"ไม่กำหนด")+$db->get_keypair("pap_user", "pap_user.user_id", "user_login", "LEFT JOIN pap_usermeta AS um ON um.user_id=pap_user.user_id AND meta_key='user_auth' WHERE meta_value='17'");
    $max = ceil(count($all_rec)/$iperpage);
    $addhtml = "";
    if($pauth>1){
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
        array_unshift($head, "แก้ไข");
    }

    if($pauth>3){
        $csvlink = $root."csv_download.php?req=quote_csv&month=$mm";
    } else {
        $csvlink = $root."csv_download.php?req=quote_csv&month=$mm&uid=$uid";
    }
    $csv = "<a id='quote-csv' href='$csvlink' title='Download Data'><input type='button' class='blue-but' value='โหลดข้อมูล'/></a>";
    $fil_sale = "";
    if($pauth==4){
        $fil_sale = $tb->show_filter(current_url(), "sid", $sale, $sid,"--Sale--");
        array_push($head,"Sale");
    }

    $content .= "<h1 class='page-title'>$pagename $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $form->show_text("scid","scid","","ค้นหา 3 ตัวอักษรขึ้นไป","ค้นหาจากรหัสหรือชื่องาน","","label-3070")
            . $form->show_hidden("ajax_req","ajax_req",PAP."request_ajax.php")
            . $form->show_hidden("redirect","redirect",$redirect)
            . $tb->show_filter(current_url(), "fil_cat", $arrcat, $cat,"ชนิด")
            . $tb->show_filter(current_url(), "fil_status", array("-1"=>"แสดงทั้งหมด")+$op_quote_status, $status,"สถานะ")
            . $tb->show_filter(current_url(), "fil_mm", $arrmm, $mm,"เดือน")
            . $fil_sale
            . "<div class='tb-clear-filter'><a href='$redirect' title='Clear Filter'><input type='button' value='Clear Filter' /></a></div>"
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-quote")
            . "<div class='tb-legend'>"
            . my_legend($op_quote_status, $op_quote_status_icon)
            . $csv
            . "</div>"
            . "</div><!-- .col-100 -->"
            . "<script>quote_search();</script>";
}

$content .= ($action=="print"?"":$menu->showfooter());
echo $content;
