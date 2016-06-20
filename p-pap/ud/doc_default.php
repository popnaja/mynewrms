<?php
__autoload("pappdo");
include_once(dirname(dirname(__FILE__))."/p-option.php");
$rpdb = new PAPdb(DB_PAP);
function show_quote_df($qid){
    global $rpdb;
    $db = $rpdb;
    $root = PAP;
    $tb = new mytable();
    
    //load info
    $info = $db->get_quote_info($qid)+$db->get_meta("pap_quote_meta","quote_id",$qid);
    $group = $db->get_info("pap_quote_group", "quote_id", $qid);
    $arr_q = $db->get_mm_arr("pap_quote_group", "quote_id", "group_id", $group['group_id']);
    $cus= $db->get_info("pap_customer","customer_id",$info['customer_id'])+$db->get_meta("pap_customer_meta", "customer_id", $info['customer_id']);
    $ct = $db->get_info("pap_contact", "contact_id", $info['contact_id']);
    $show_date = (is_null($info['approved'])?$info['created']:$info['approved']);

    $head = "<div class='doc-info'>"
        . "<div class='doc-to'>"
        . print_cus_info($info['customer_id'])
        . "</div><!-- .doc-to -->"
        . "<div class='doc-date'>"
        . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($show_date)."</div>"
        . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['quote_no']."</div>"
        . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
        . "</div>"
        . "</div>";
    $x=0;
    $list = 1;
    $rec = array();
    $discount = 0;
    //job detail
    foreach($arr_q as $q){
        $qinfo = $db->get_quote_info($q)+$db->get_meta("pap_quote_meta", "quote_id", $q, "('detail_price','discount')");
        $tt = (float)$qinfo['q_price'];
        $amount = (int)$qinfo['amount'];
        $peru = $tt/$amount;
        $discount += $qinfo['discount'];
        $jdetail = job_detail($q);
        $dttb = job_dttb_ghpp($jdetail);
        $x += ceil((count($jdetail,1)-(count($jdetail)-3))/2);
        $tg = ceil($x/10);
        if(!isset($rec[$tg])){
            $rec[$tg] = array();
        }
        if(isset($qinfo['detail_price'])){
            $dt = json_decode($qinfo['detail_price'],true);
            $sub = 0; //เก็บข้อมูลราคา เอาไปหักออกจาก total เพื่อ show ค่าพิมพ์
            foreach($dt as $k=>$v){
                if($v[0]>0){
                    array_push($rec[$tg],array($list,$v[1],$v[2],$v[3],$v[4]));
                    $sub += $v[4];
                    $list++;
                    $x++;
                }
            }
            array_push($rec[$tg],array($list,$dttb,$amount,($tt-$sub)/$amount,$tt-$sub));
            $list++;
        } else {
            array_push($rec[$tg],array($list,$dttb,$amount,$peru,$tt));
            $list++;
        }
    }
    //$due = ($info['plan_delivery']>0?"<div class='print-list-title'>กำหนดส่งงาน : ".thai_date($info['plan_delivery'])."</div>":"");
    //multi quote
    $extra = "";
    $ex = 0;
    if(count($arr_q)==1){
        if(isset($info['multi_quote_info'])&&strlen($info['multi_quote_info'])>3){
            $dt = json_decode($info['multi_quote_info'],true);
            $qhead = array("ลำดับ","หมายเหตุ","ยอดพิมพ์","ราคาหน่วยละ","จำนวนเงิน");
            $qrec = array();
            $i = 1;
            $ex++;
            foreach($dt as $k=>$v){
                if($v['show']>0){
                    array_push($qrec,array($i,$v['remark'],number_format($v['amount']),number_format($v['price']/$v['amount'],2),number_format($v['price'],2)));
                    $i++;
                    $ex++;
                }
            }
            if(count($qrec)>0){
                $extra = "<div class='print-extra'>"
                    . "<b>เสนอราคาที่ยอดพิมพ์ปริมาณอื่น (ราคายังไม่รวมภาษีมูลค่าเพิ่ม)</b>"
                    . $tb->show_table($qhead,$qrec,"tb-multi-q")
                    . "</div>";
            }
        }
    }
    
    //sign
    $pay = ($info['credit']>0?"เครดิต ".$info['credit']." วัน":"ชำระเป็นเงินสด");
    $sign = "";
    $msign = "";
    $date = thai_date($show_date,true);
    if($info['user_id']>0){
        $sale = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
        $sign = (isset($sale['signature'])&&$sale['signature']!=""?"<img src='".ROOTS.$sale['signature']."' />":"");
    }
    if($info['status']>=2){
        $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
        $msign = "<img src='".ROOTS.$manager['signature']."' />";
    }
    $sig = "<table id='rp-2sign' class='doc-final head-color'>"
    . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='150'>ผู้อนุมัติ</th><th width='150'>เจ้าหน้าที่ฝ่ายขาย</th></tr>"
    . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='50'>$msign</td><td class='doc-sign' height='50'>$sign</td></tr>"
    . "<tr><td>วันที่ : $date</td><td>วันที่ : $date</td></tr>"
    . "</table>";

    $cussign = "<table id='rp-cus-sign' class='doc-final head-color'>"
            . "<tr><td rowspan='4'>"
            . "<ul style='padding-left:0.7cm;'>"
            . "<li>บริษัทฯขอสงวนสิทธิในการเปลี่ยนแปลงราคา หากรายละเอียดงานมีการเปลี่ยนแปลงเกิดขึ้นภายหลังการว่าจ้าง</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับฝากสิ่งพิพม์ที่สำเร็จแล้วและถึงกำหนดส่งของ โดยไม่ขอรับผิดชอบในความเสียหายกับสิ่งพิมพ์นั้นๆในทุกกรณี</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับผิดชอบในเพลทที่ผู้ว่าจ้างจัดส่งมาให้ และไม่ทำการรับเพลตคืนหลังจากที่ได้พิมพ์งานแล้วเสร็จ</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับผิดชอบ หากมีการละเมิดลิขสิทธิ์ในข้อเขียน, บทความ, บทปรพันธ์, การออกแบบ ฯลฯ</li>"
            . "</ul>"
            . "</td>"
            . "<th width='300'>สำหรับลูกค้าเพื่อตอบรับและส่งกลับมาที่บริษัทฯ</th>"
            . "</tr>"
            . "<tr><td style='text-align:center'>รับทราบเอกสารว่าจ้างและเงื่อนไขตามใบเสนอราคา</td></tr>"
            . "<tr><td height='70'></td></tr>"
            . "<tr><td>ประทับตราบริษัท(ถ้ามี)<span class='float-right'>วันที่:____/_____/______</span></td></tr>"
            . "</table>";
    //check row
    $header = array("ลำดับ","รายการ","จำนวน","ราคาหน่วยละ","จำนวนเงิน");
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $content = "";
    $ttpage = count($rec);
    $p = 1;
    $accum = 0;
    if($ttpage>1){
        foreach($rec as $k=>$v){
            if($k==$ttpage){
                $detail =  $tb->show_tb_wtax($header,$v,"tb-rp",$tax,$discount,"",null,null,true,$accum);
            } else {
                $detail =  $tb->show_tb_wtax($header,$v,"tb-rp",$tax,$discount,"",null,null,false);
            }
            //cal accum
            foreach($v as $dt){
                $accum += $dt[4];
            }
            $content .= "<div class='print-a4-fix'>"
                    . print_header("ใบเสนอราคา","หน้า $p/$ttpage","ghpp_font")
                    . $head
                    . "<div class='doc-dt head-color'>"
                    . $detail
                    . "</div><!-- .doc-dt -->"
                    . $sig
                    . $cussign
                    . "</div><!-- .print-a4-fix -->";
            $p++;
        }
    } else {
        if($x+$ex>10){
            $content .= "<div class='print-a4-fix'>"
                    . print_header("ใบเสนอราคา","หน้า 1/2","ghpp_font")
                    . $head
                    . "<div class='doc-dt head-color'>"
                    . $tb->show_tb_wtax($header,$rec[1],"tb-rp",$tax,$discount)
                    . "</div><!-- .doc-dt -->"
                    . $sig
                    . $cussign
                    . "</div><!-- .print-a4-fix -->"
                    . "<div class='print-a4-fix'>"
                    . print_header("ใบเสนอราคา","หน้า 2/2","ghpp_font")
                    . $head
                    . $extra
                    . "</div><!-- .print-a4-fix -->";
        } else {
            $content .= "<div class='print-a4-fix'>"
                    . print_header("ใบเสนอราคา","","ghpp_font")
                    . $head
                    . "<div class='doc-dt head-color'>"
                    . $tb->show_tb_wtax($header,$rec[1],"tb-rp",$tax,$discount)
                    . "</div><!-- .doc-dt -->"
                    . $extra
                    . $sig
                    . $cussign
                    . "</div><!-- .print-a4-fix -->";
        }
    }
    return $content;
}
function show_order($oid,$edit=false){
    global $op_paper_div,$op_unit;
    global $rpdb;
    $db = $rpdb;
    __autoload("pdo_report");

    $rp = new reportPDO();
    $info = $rp->rp_order($oid);
    $comp = $rp->rp_order_comp($oid);
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");

    $paper_html = "";
    $plate_html = "";
    $after = "";
    $packing = "";

    foreach($comp as $k=>$com){
        if($com['type']==9){
            //packing & shipping
            $pack = $rp->rp_order_cpro($op_unit,$com['id'], "(11,12)",true);
            $packing .= "<tr>"
                    . "<th colspan='2'>การห่อ</th>"
                    . "<td colspan='5'>".(isset($pack[11])?$pack[11]:"")."</td>"
                    . "</tr><tr>"
                    . "<th colspan='2'>ขนส่ง</th>"
                    . "<td colspan='5'>".(isset($pack[12])?$pack[12]:"")."</td>"
                    . "</tr>";
            continue;
        }
        if(count($comp)==1){
            //packing & shipping
            $pack = $rp->rp_order_cpro($op_unit,$com['id'], "(11,12)",true);
            $packing .= "<tr>"
                    . "<th colspan='2'>การห่อ</th>"
                    . "<td colspan='5'>".(isset($pack[11])?$pack[11]:"")."</td>"
                    . "</tr><tr>"
                    . "<th colspan='2'>ขนส่ง</th>"
                    . "<td colspan='5'>".(isset($pack[12])?$pack[12]:"")."</td>"
                    . "</tr>";
        }
        $cname = $com['name'];
        $div = ($com['paper_cut']>1?" (".$op_paper_div[$com['paper_cut']].")":"");
        $paper_html .= "<tr>"
                . "<td align='center'>$cname</td>"
                . "<td colspan='4'>".$com['mat_name'].$div."</td>"
                . "<td align='center' colspan='2'>".number_format($com['rim'],2)."</td>"
                . "</tr>";

        //plate
        $print = $rp->rp_order_cpro($op_unit,$com['id'], "(3)");
        $set = explode(";",$print[3]);
        for($i=0;$i<count($set);$i++){
            $pinfo = explode(",",$set[$i]);
            $sheet = $pinfo[3]-$com['allowance'];
            $tt = ceil($pinfo[0])*$pinfo[3]/($pinfo[0]>=2?2:1);
            $plate_html .= "<tr align='center'>"
                    . "<td>$cname ".$pinfo[2]."</td>"
                    . "<td>".$pinfo[0]." กรอบ<br/>".$pinfo[1]."</td>"
                    . "<td>".($com['paper_cut']>1?$op_paper_div[$com['paper_cut']]."<br/>".$com['print_size']:"-")."</td>"
                    . "<td>".$com['paper_lay']."</td>"
                    . "<td>".number_format($sheet,0)."</td>"
                    . "<td>".number_format($com['allowance'],0)."</td>"
                    . "<td>".number_format($tt,0)."</td>"
                    . "</tr>";
        }

        // post-print
        $post = $rp->rp_order_cpro($op_unit,$com['id'], "(4,5)");
        $after .= "<tr align='center'>"
                . "<td colspan='2'>$cname</td>"
                . "<td colspan='3'>".(isset($post[4])?$post[4]:"")."</td>"
                . "<td colspan='2'>".(isset($post[5])?$post[5]:"")."</td>"
                . "</tr>";
    }
    //header
    if($edit){
        $header = "";
    } else {
        $header = "<div class='print-com'>"
            . $cinfo['name']."<br/>"
            . $cinfo['address']."<br/>"
            . "Tel: ".$cinfo['tel']
            . "Tax ID: ".$cinfo['tax_id']."<br/>"
            . "</div><!-- .print-com -->";
    }
    $content = "<div class='print-a4'>"
            . $header
            . "<h2>ใบสั่งงานพิมพ์</h2>";

    $plate = compare_plan($info['plate_plan'], $info['plate_received']);
    $paper = compare_plan($info['paper_plan'], $info['paper_received']);
    $picdir = RDIR.$info['picture'];
    if($info['picture']!=""&&file_exists($picdir)){
        $pic = "<img src='".ROOTS.$info['picture']."' />";
    } else {
        $pic = "";
    }
    $content .= "<table id='pto'>"
            . "<tr><th>ชื่องาน</th><td colspan='6'>".$info['name']."</td></tr>"
            . "<tr>"
            . "<th>รหัสงาน</th><td colspan='2'>".$info['order_no']."</td>"
            . "<th>วันที่ขอรับงาน</th><td colspan='3'>".thai_date($info['plan_delivery'])."</td>"
            . "</tr>"
            . "<tr>"
            . "<th>เพลตเข้า</th><td colspan='2'>".$plate."</td>"
            . "<th>กระดาษเข้า</th><td colspan='3'>".$paper."</td>"
            . "</tr>"
            . "<tr class='space'></tr>"
            . "<tr><th>ลักษณะงาน</th><td colspan='3'>".$info['cat']."</td><td colspan='3' rowspan='5' class='job-pic'>$pic</td></tr>"
            . "<tr><th>ยอดพิมพ์</th><td colspan='3'>".number_format($info['amount'],0)."</td></tr>"
            . "<tr><th>จำนวนหน้า</th><td colspan='3'>".number_format($info['pages'],0)."</td></tr>"
            . "<tr><th>เข้าเล่ม</th><td colspan='3'>".$info['bind']."</td></tr>"
            . "<tr><th>ขนาดเสร็จ</th><td colspan='3'>".$info['size']."</td></tr>"
            . "<tr><th>คำสั่งพิเศษ**</th><td colspan='6' ><b style='color:red;'>".$info['remark']."</b></td></tr>"
            . "<tr class='space'></tr>"
            . "<tr><th colspan='5'>กระดาษ</th><th colspan='2'>จำนวน(ริม)</th></tr>"
            . $paper_html
            . "<tr class='space'></tr>"
            . "<tr><th colspan='3'>เพลท</th><th>Lay</th><th>พิมพ์</th><th>เผื่อ</th><th>รวม</th></tr>"
            . $plate_html
            . "<tr class='space'></tr>"
            . "<tr><th colspan='2'>หลังงานพิมพ์</th><th colspan='3'>เคลือบ</th><th colspan='2'>ปั้ม+ไดตัท</th></tr>"
            . $after
            . "<tr class='space'></tr>"
            . $packing;


    $content .= "</table>";

    $content .= "</div><!-- .print-a4 -->"
            . "</body>";
    return $content;
}
function show_mat_po($poid){
    global $rpdb;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $db->get_info("pap_mat_po", "po_id", $poid);
    $content = "<div class='print-letter'>"
            . print_header("ใบสั่งซื้อ",null,'ghpp_font');

    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_sup_info($info['supplier_id'])
            . "</div>"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['po_created'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['po_code']."</div>"
            . "<div class='doc-600'> <span class='float-left'>กำหนดส่ง : </span>".  thai_date($info['po_delivery_plan'])."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $rec = $rp->rp_mat_po($poid);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$rec,"tb-rp",0.07,0,"",9)
            . "</div><!-- .doc-dt -->";

    $pay = ($info['po_payment']>0?"เครดิต ".$info['po_payment']." วัน":"ชำระเป็นเงินสด");
    if($info['po_status']>1){
        $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
        $sign = "<img src='".ROOTS.$manager['signature']."' />";
        $date = thai_date($info['po_created']);
    } else {
        $sign = "";
        $date = "";
    }
    $content .= "<table class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='200'>ผู้มีอำนาจลงนาม</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['po_remark']."</td><td class='doc-sign' height='100'>$sign</td></tr>"
            . "<tr><td>วันที่ : $date</td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-a4 -->";
    return $content;
}
function show_process_po($poid){
    global $rpdb;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $db->get_info("pap_process_po", "po_id", $poid);
    $content = "<div class='print-letter'>"
            . print_header("ใบสั่งผลิต",null,"ghpp_font");

    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_sup_info($info['supplier_id'])
            . "</div>"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['po_created'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['po_code']."</div>"
            . "<div class='doc-600'> <span class='float-left'>กำหนดส่ง : </span>".  thai_date($info['po_delivery_plan'])."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $rec = $rp->rp_process_po($poid);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$rec,"tb-rp",0.07,0,"",9)
            . "</div><!-- .doc-dt -->";

    $pay = ($info['po_payment']>0?"เครดิต ".$info['po_payment']." วัน":"ชำระเป็นเงินสด");
    if($info['po_status']>1){
        $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
        $sign = "<img src='".ROOTS.$manager['signature']."' />";
        $date = thai_date($info['po_created']);
    } else {
        $sign = "";
        $date = "";
    }
    $content .= "<table class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='200'>ผู้มีอำนาจลงนาม</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['po_remark']."</td><td class='doc-sign' height='100'>$sign</td></tr>"
            . "<tr><td>วันที่ : $date</td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-a4 -->";
    return $content;
}
function show_deli($did){
    global $rpdb;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_deli_info($did);
    if(isset($info['customer_id'])){
        $cid = $info['customer_id'];
    } else {
        $acid = explode(",",$info['acid']);
        $cid = $acid[0];
    }
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $cid);
    $aoid = explode(",",$info['aoid']);

    $docinfo = "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($cid,$info['address'],true,$info['contact'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['date'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_deli_dt($did);
    $row = 0;
    foreach($recs[0] as $k=>$v){
        if(is_numeric($v[1])){
            $qid = $v[1];
            $data = job_detail($qid);
            $recs[0][$k][1] = job_dttb_ghpp($data);
            $row += count($data,1)-count($data);
        } else {
            $jinfo = explode(";",$v[1]);
            $jname = $jinfo[0];
            $meta = $db->get_meta("pap_delidt_meta", "dtid", $jinfo[1]);
            $dt = explode(",",$meta['job_detail']);
            $data["ชื่องาน"] = $jname;
            $data["รายละเอียด"] = $dt;
            $recs[0][$k][1] = job_dttb_ghpp($data);
            $row += count($data,1)-count($data);
        }
    }
    $discount = $recs[1];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    if(ceil($row/2)<11){
        $content = "<div class='print-letter'>"
            . print_header("ใบส่งของ/ใบแจ้งหนี้",null,"ghpp_font")
            . $docinfo
            . "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs[0],"tb-rp",$tax,$discount,"",9-ceil($row/2))
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";
    } else {
        $content = "<div class='print-letter'>"
            . print_header("ใบส่งของ/ใบแจ้งหนี้","หน้า 1/2","ghpp_font")
            . $docinfo
            . "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs[0],"tb-rp",$tax,$discount)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->"
            . "</div><!-- .print-letter -->"
            . "<div class='print-letter'>"
            . print_header("ใบส่งของ/ใบแจ้งหนี้","หน้า 2/2")
            . $docinfo;
    }

    $i = 0;
    $pay = "";
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$info['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        //normal
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= $oinfo['name']." (".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
            $i++;
        }
    }

    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='180'>ผู้ส่งสินค้า</th><th width='180'>ผู้รับสินค้า</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'></td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : </td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-letter -->";
    return $content;
}
function show_tdeli($tdid){
    global $rpdb;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_tdeli_info($tdid);
    if(isset($info['customer_id'])){
        $cid = $info['customer_id'];
    } else {
        $acid = explode(",",$info['acid']);
        $cid = $acid[0];
    }
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $cid);
    $aoid = explode(",",$info['aoid']);

    $content = "<div class='print-letter'>"
            . "<div class='c-head'>"
            . "<div class='doc-name-center'><span class='ghpp_font'>ใบส่งของชั่วคราว</span></div>"
            . "</div>";
    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($cid,$info['address'],false,$info['contact'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['date'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            //. "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_tdeli_dt($tdid);
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,0,"",11)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";

    $i = 0;
    $pay = "";
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$info['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= $oinfo['name']." (".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
            $i++;
        }
    }
    
    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td></td><th width='180'>ผู้ส่งสินค้า</th><th width='180'>ผู้รับสินค้า</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'></td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : </td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-a4 -->";
    return $content;
}
function show_pbill($bid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_pbill_info($bid);
    if(isset($info['customer_id'])){
        $cid = $info['customer_id'];
    } else {
        $acid = explode(",",$info['acid']);
        $cid = $acid[0];
    }
    $thdate = thai_date($info['date']);

    $content = "<div class='print-letter'>"
            . print_header("ใบวางบิล",null,"ghpp_font");
    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($cid,0,true,$info['contact'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>". $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>วันที่นัดชำระ : </span>". thai_date($info['pay_date'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","เลขที่เอกสาร<br/>Document No","วันที่เอกสาร<br/>Date","ครบกำหนดชำระ<br/>Due Date","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_pbill_dt($bid,$op_type_unit);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_bill($head,$recs,"tb-rp",10)
            . "<span style='font-size:11pt;'>ได้รับบิลไว้ตรวจสอบตามรายการข้างต้นนี้ถูกต้องแล้ว</span>"
            . "</div><!-- .doc-dt -->";
    $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
    $msign = "<img src='".ROOTS.$manager['signature']."' />";

    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>".$info['payment']."</td><th width='180'>ผู้รับวางบิล</th><th width='180'>ผู้วางบิล</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'>$msign</td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : $thdate</td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-letter -->";
    return $content;
}
function show_invoice($ivid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_invoice_info($ivid);
    //$ct = $db->get_info("pap_contact", "contact_id", $info['contact']);
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $info['customer_id']);
    $thdate = thai_date($info['date']);
    $original = "<div class='print-letter'>"
            . print_header("ต้นฉบับ<br/>ใบกำกับภาษี");
    $doc = "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_invoice_dt($ivid,$op_type_unit);
    $discount = $info['discount'];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $doc .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,$discount,"",10)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";

    $user = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
    //$usign = "<img src='".ROOTS.$user['signature']."' />";
    $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
    $msign = "<img src='".ROOTS.$manager['signature']."' />";
    $pay = "";

    $doc .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>ชำระเงินโดย :</th><td>$pay</td><th width='180'>ผู้ส่งสินค้า</th><th width='180'>ผู้รับสินค้า</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'></td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : </td></tr>"
            . "</table>";

    $doc .= "</div><!-- .print-letter -->";
    return $original.$doc;
}
function show_receipt($rcid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_receipt_info($rcid);
    //$ct = $db->get_info("pap_contact", "contact_id", $info['contact']);
    $thdate = thai_date($info['date']);

    $doc = "<div class='print-letter'>"
            . print_header("ใบเสร็จรับเงิน");

    $doc .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>อ้างอิงใบกำกับภาษี : </span>".  $info['invoiceno']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_receipt_dt($rcid,$op_type_unit);
    $discount = 0;
    $tax = ($info['tax_exclude']=="yes"?0:0.07);
    $doc .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,$discount,"",9,"ghpp-tb")
            . "<span style='font-size:11pt;'>ใบเสร็จรับเงินฉบับนี่จะใช้ได้ต่อเมื่อสามารถเรียกเก็บเงินได้ตามเช็คแล้วเท่านั้น</span>"
            . "</div><!-- .doc-dt -->";
    //signature
    $user = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
    //$usign = "<img src='".ROOTS.$user['signature']."' />";
    $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
    $msign = "<img src='".ROOTS.$manager['signature']."' />";
    //payment
    if($info['payment']=="รับชำระเป็นเช็ค"){
        $pay = $info['payment']. ": ธนาคาร ".$info['check_bank']
                . " สาขา:".$info['check_bank_branch']
                . " หมายเลข:".$info['check_no']
                . " ลงวันที่:".thai_date($info['check_date']);
    } else if($info['payment']=="รับชำระด้วยวิธีการโอนเงิน"){
        $pay = $info['payment']. ": ธนาคาร ".$info['transfer_bank']." Ref:".$info['transfer_ref'];
    } else {
        $pay = $info['payment']." : ".$info['cash_remark'];
    }
    //show footer
    $doc .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>ชำระเงินโดย :</th><td></td><th width='180'>ผู้ตรวจ</th><th width='180'>ผู้รับเงิน</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'></td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : </td></tr>"
            . "</table>";

    $doc .= "</div><!-- .print-letter -->";
    return $doc;
}
function compare_plan($plan,$act){
    if(is_null($plan)&&is_null($act)){
        $res = "";
    } else if(is_null($act)){
        $res = "<span style='color:#444;background-color:#FDD835;'><i>".thai_date($plan)."</i></span>";
    } else {
        $res = thai_date($act);
    }
    return $res;
}
function print_sup_info($sid){
    global $rpdb;
    $db = $rpdb;
    $sinfo = $db->get_info("pap_supplier", "id", $sid);
    $sup = "<h3>SUPPLIER'S NAME</h3>"
            . "<div class='cus-info-left'>Company : </div>"
            . "<div class='cus-info-right'>".$sinfo['name']."</div>"
            . "<div class='cus-info-left'>Address : </div>"
            . "<div class='cus-info-right'>".$sinfo['address']."<br/>Tel.".$sinfo['tel']."</div>"
            . "<div class='float-left' style='clear:left'>เลขประจำตัวผู้เสียภาษีอากร ".$sinfo['taxid']."</div>";
    return $sup;
}
function print_cus_info($cid,$aid=0,$showtax=true,$ctid=null){
    global $rpdb;
    $db = $rpdb;
    $sinfo = $db->get_info("pap_customer", "customer_id", $cid)+$db->get_meta("pap_customer_meta", "customer_id", $cid);
    $contact = "";
    if(isset($ctid)){
        $ct = $db->get_info("pap_contact", "contact_id", $ctid);
        $contact .= "<div class='cus-info-left'>ติดต่อ : </div>"
                . "<div class='cus-info-right'>".$ct['contact_name']. " (". $ct['contact_tel'].")</div>";
    }
    if($aid>0){
        $ainfo = $db->get_info("pap_cus_ad", "id", $aid);
        $name = $ainfo['name'];
        $address = $ainfo['address'];
    } else {
        $name =  $sinfo['customer_name'];
        $address = $sinfo['customer_address']
        . " Tel. ".$sinfo['customer_tel'];
    }
    $tax = ($showtax?"<div class='float-left' style='clear:left'>เลขประจำตัวผู้เสียภาษีอากร ".$sinfo['customer_taxid']." ".$sinfo['c_branch']."</div>":"");
    $sup = "<h3>CUSTOMER'S NAME</h3>"
            . "<div class='cus-info-left'>Company : </div>"
            . "<div class='cus-info-right'>".$name."</div>"
            . "<div class='cus-info-left'>Address : </div>"
            . "<div class='cus-info-right'>".$address."</div>"
            . $contact
            . $tax;
    return $sup;
}
function print_header($docname,$page=null,$font=null){
    global $rpdb;
    $db = $rpdb;
    $f = (isset($font)?$font:"ghpp-font");
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $clogo = "<img src='".ROOTS.$cinfo['c_logo']."'/>";
    $header = "<span class='c-name'>".$cinfo['name']."</span><br/>"
            . $cinfo['address']
            . "  Tel. ".$cinfo['tel']."  Fax. ".$cinfo['fax']."<br/>"
            . "<span class='c-tax'>เลขประจำตัวผู้เสียภาษีอากร ".$cinfo['tax_id']."</span>";
    $head = "<div class='c-head $f'>"
            . "<div class='c-logo'>$clogo</div>"
            . "<div class='c-info'>$header</div>"
            . "<div class='doc-name'><span>$docname</span></div>"
            . "<div class='doc-page'>$page</div>"
            . "</div>";
    return $head;
}
function job_dttb($data){
    $dttb = "<table class='jdetail'>";
    foreach($data as $k=>$v){
        $rh = count($v);
        $dttb .= "<tr><td rowspan='$rh'>$k</td>";
        for($i=0;$i<$rh;$i++){
            $dttb .= ($i==0?"":"<tr>")
                . "<td>$v[$i]</td></tr>";
        }
    }
    $dttb .= "</table>";
    return $dttb;
}
function job_dttb_ghpp($data){
    $dttb = "<div class='list-job-dt'>";
    foreach($data as $k=>$v){
        $dttb .= "<p><span style='font-weight:600'>$k : </span>";
        if(is_array($v)){
            for($i=0;$i<count($v);$i++){
                $dttb .= ($i==0?"":"<br/>")
                        . $v[$i];
            }
        } else {
            $dttb .= $v;
        }
        $dttb .= "</p>";
    }
    $dttb .= "</div>";
    return $dttb;
}
function job_detail($qid){
    global $rpdb,$op_comp_type;
    $db = $rpdb;
    $info = $db->get_quote_info($qid)+$db->get_meta("pap_quote_meta","quote_id",$qid);
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $process =  $db->get_keypair("pap_process", "process_id", "process_name");
    $comps = $db->get_print_comp($qid);
    //เข้าเล่ม
    $data = array(
        "ชื่องาน" => $info['name'],
        "ประเภท" => $product_type[$info['cat_id']],
        "ขนาด" => $info['size']
    );
    if($info['binding_id']>0){
        $data['เข้าเล่ม'] = $process[$info['binding_id']];
    }
    foreach($op_comp_type AS $k=>$v){
        $run[$k] = 0;
    }
    $coat2 = (isset($info['coat2'])?json_decode($info['coat2'],true):0);
    $coatpage = (isset($info['coatpage'])?json_decode($info['coatpage'],true):0);
    for($i=0;$i<count($comps);$i++){
        $v = $comps[$i];
        $post = explode(",",$v['comp_postpress']);
        //name
        $type = $v['comp_type'];
        $run[$type]++;
        $cname = $op_comp_type[$type].($run[$type]>1?" ($run[$type])":"");
        
        //color
        if($type==2||$type==6){
            $color = $v['color']." ".$v['comp_page']." หน้า";
        } else {
            if($v['color']==$v['color2']){
                $color = $v['color']." สองด้าน";
            } else if($v['color2']==null){
                $color = $v['color']." ด้านเดียว";
            } else {
                $color = $v['color']."/".$v['color2'];
            }
        }
        $data[$cname] = array($v['paper']." ".$v['weight']." แกรม ".$color);
        //coating
        if(isset($v['coating'])){
            array_push($data[$cname],"เคลือบ ".$v['coating'].($type==2||$type==6&&is_array($coatpage)&&$coatpage[$i]>0?" $coatpage[$i] หน้า":""));
        }
        //coat2
        if(is_array($coat2)&&$coat2[$i]>0){
            array_push($data[$cname],"เคลือบด้านใน ".$process[$coat2[$i]]);
        }
        //cwing
        if($info['cwing']==1&&$type==1){
            if($info['fwing']>0||$info['bwing']>0){
                $fwing = ($info['fwing']>0?"ปีกปกหน้า ".$info['fwing']." cm":"");
                $bwing = ($info['bwing']>0?"ปีกปกหลัง ".$info['bwing']." cm":"");
                array_push($data[$cname],$fwing." ".$bwing);
            }
        }
        //แผ่นพับ show พับกี่ส่วน
        if($type==3&&isset($info['folding'])&&$info['folding']>0){
            array_push($data[$cname],$process[$info['folding']]);
        }
        //ไดคัท
        foreach($post as $p){
            $p = explode(";",$p);
            if($p[0]>0){
                array_push($data[$cname],$process[$p[0]]);
            }
        }
    }
    if($info['prepress']!==""){
        $pp = explode(",",$info['prepress']);
        $data["การจัดทำต้นฉบับ"] = array();
        foreach($pp as $k=>$v){
            if($v>0){
                array_push($data["การจัดทำต้นฉบับ"],$process[$v]);
            }
        }
    }
    $other = array();
    if(strlen($info['packing'])>0||strlen($info['shipping'])>0){
        $packing = explode(",",$info['packing']);
        $shipping = explode(",",$info['shipping']);
        foreach($packing as $v){
            $v = explode(";",$v);
            if($v[0]>0){
                array_push($other,$process[$v[0]]);
            }
        }
        foreach($shipping as $v){
            $v = explode(";",$v);
            if($v[0]>0){
                array_push($other,$process[$v[0]]);
            }
        }
    }
    if(isset($info['other_price'])){
        foreach(json_decode($info['other_price'],true) as $i=>$v){
            array_push($other,$v[0]." ($v[3])");
        }
    }
    if(count($other)>0){
        $data["ข้อกำหนดอื่นๆ"] = array(implode(", ",$other));
    }
    return $data;
}
function show_invoice_ghpp1($ivid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;
    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();
    $info = $rp->rp_invoice_info($ivid);
    $deliinfo = $db->get_info("pap_delivery", "id", $info['adid']);
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $info['customer_id']);
    $thdate = thai_date($info['date']);
    //deli info
    $dinfo = $rp->rp_deli_info($deliinfo['id']);
    $aoid = explode(",",$dinfo['aoid']);
    //pay
    $i = 0;
    $pay = "";
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$dinfo['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        //normal
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= "(".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
            $i++;
        }
    }
    $user = (isset($info['user_login'])?$info['user_login']:"-");
    $doc = "<div class='print-letter'>"
            . "<div class='doc-header'></div><!-- .doc-header -->"
            . "<div class='doc-info ghpp-doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date ghpp'>"
            . "<div class='doc-600'> <span class='float-left'>Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>Invoice No. : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Representative : </span>". $user."</div>"
            . "<div class='doc-600'> <span class='float-left'>เงื่อนไขการชำระเงิน : </span>". $pay."</div>"
            . "</div>"
            . "</div>";
    $head = array("&nbsp;","&nbsp;","&nbsp;","&nbsp;","&nbsp;");
    $recs = $rp->rp_invoice_dt($ivid,$op_type_unit);
    $discount = $info['discount'];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $doc .= "<div class='doc-dt ghpp'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp-ghpp",$tax,$discount,"",9,"ghpp-tb")
            . "</div><!-- .doc-dt -->"
            . "</div><!-- .print-letter -->";
    return $doc;
}
function show_rc_ghpp1($rcid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_receipt_info($rcid);
    $thdate = thai_date($info['date']);
    $user = (isset($info['user_login'])?$info['user_login']:"-");
    $company_info = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    
    $dinfo = $rp->rp_deli_info($info['adeli']);
    $aoid = explode(",",$dinfo['aoid']);
    $pay = "";
    $i= 0;
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$dinfo['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        //normal
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= "(".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
            $i++;
        }
    }
    
    $doc = "<div class='print-letter'>"
            . "<div class='doc-header'></div><!-- .doc-header -->"
            . "<div class='doc-info ghpp-doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date ghpp'>"
            . "<div class='doc-600'> <span class='float-left'>Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>REC No. : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Representative: </span>". $user."</div>"
            . "<div class='doc-600'> <span class='float-left'>เงื่อนไขการชำระเงิน : </span>".  $pay."</div>"
            . "</div>"
            . "</div>";
    $ivinfo = $db->get_info("pap_invoice", "id", $info['ivid']);
    $tax = ($info['tax_exclude']=="yes"?1:1.07);
    $head = array("&nbsp;","&nbsp;","&nbsp;","&nbsp;");
    $recs = array(array(1,$info['invoiceno'],  thai_date($ivinfo['date'], true),  $ivinfo['total']*$tax));
    $discount = 0;
 
    $doc .= "<div class='doc-dt ghpp'>"
            . $tb->show_tb_bill_o($head,$recs,"tb-rp-ghpprc",7)
            . "</div><!-- .doc-dt -->";
    $doc .= "</div><!-- .print-letter -->";
    return $doc;
}
function show_invoice_ghpp($ivid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_invoice_info($ivid);
    $deliinfo = $db->get_info("pap_delivery", "id", $info['adid']);
    //$ct = $db->get_info("pap_contact", "contact_id", $deliinfo['contact']);
    //deli info
    $dinfo = $rp->rp_deli_info($deliinfo['id']);
    $aoid = explode(",",$dinfo['aoid']);
    //pay
    $i = 0;
    $pay = "";
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$dinfo['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        //normal
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= $oinfo['name']." (".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
            $i++;
        }
    }

    $cus = $db->get_meta("pap_customer_meta", "customer_id", $info['customer_id']);
    $thdate = thai_date($info['date']);
    $original = "<div class='print-letter'>"
            . print_header("ใบกำกับภาษี",null,"ghpp_font");
    $sinfo = $db->get_info("pap_customer", "customer_id", $info['customer_id']);
    $address = $sinfo['customer_address']."<br/>"
        . "Tel: ".$sinfo['customer_tel']."<br/>";
    $doc = "<div class='doc-info ghpp-doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>Invoice No. : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Representative : </span>". $info['user_login']."</div>"
            . "<div class='doc-600'> <span class='float-left'>เงื่อนไขการชำระเงิน : </span>". $pay."</div>"
            . "</div>"
            . "</div>";

    $head = array("Item","Description","Qty","Price","Total");
    $recs = $rp->rp_invoice_dt($ivid,$op_type_unit);
    $discount = $info['discount'];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $doc .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,$discount,"",9,"ghpp-tb")
            . "<span style='font-size:11pt;'>ได้รับสินค้า, ต้นฉบับใบกำกับภาษี และรับทราบข้อตกลงอื่นๆตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";
    $doc .= "<div class='ghpp-sign'>"
            . "<table>"
            . "<tr><td>"
            . "<p>ผู้รับสินค้า.........................................................</p>"
            . "<p>(............................................................)</p>"
            . "<p>วันที่......./......../........</p>"
            . "</td>"
            . "<td>"
            . "<p>ผู้ส่งสินค้า.........................................................</p>"
            . "<p>(............................................................)</p>"
            . "<p>วันที่......./......../........</p>"
            . "</td>"
            . "</tr>"
            . "</table>"
            . "</div><!-- .ghpp-sign -->";

    $doc .= "</div><!-- .print-letter -->";
    return $original.$doc;
}
function show_rc_ghpp($rcid){
    global $rpdb;
    global $op_type_unit;
    $db = $rpdb;

    __autoload("pdo_report");
    __autoloada("table");
    $rp = new reportPDO();
    $tb = new mytable();

    $info = $rp->rp_receipt_info($rcid);
    $thdate = thai_date($info['date']);
    $user = (isset($info['user_login'])?$info['user_login']:"-");
    $company_info = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    
    $dinfo = $rp->rp_deli_info($info['adeli']);
    $aoid = explode(",",$dinfo['aoid']);
    $pay = "";
    $i= 0;
    if($aoid[0]==0){
        //manual
        $credit = explode(",",$dinfo['credit']);
        $cd = max($credit);
        $pay = ($cd>0?"เครดิต $cd วัน":"ชำระเป็นเงินสด");
    } else {
        //normal
        foreach($aoid as $oid){
            $oinfo = $rp->rp_order($oid);
            $pay .= ($i==0?"":"<br/>");
            $pay .= ($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด");
            $i++;
        }
    }
    
    $doc = "<div class='print-letter'>"
            . print_header("ใบเสร็จรับเงิน",null,"ghpp_font");
    $doc .= "<div class='doc-info ghpp-doc-info'>"
            . "<div class='doc-to'>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>REC No. : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Representative: </span>". $user."</div>"
            . "<div class='doc-600'> <span class='float-left'>เงื่อนไขการชำระเงิน : </span>".  $pay."</div>"
            . "</div>"
            . "</div>";
    $ivinfo = $db->get_info("pap_invoice", "id", $info['ivid']);
    $tax = ($info['tax_exclude']=="yes"?1:1.07);
    $head = array("ลำดับ","เลขที่ใบกำกับ","ลงวันที่","จำนวนเงิน");
    $recs = array(array(1,$info['invoiceno'],  thai_date($ivinfo['date'], true),  $ivinfo['total']*$tax));
    $discount = 0;
 
    $doc .= "<div class='doc-dt'>"
            . $tb->show_tb_bill_o($head,$recs,"tb-rp",6)
            . "</div><!-- .doc-dt -->";
    $dot = str_repeat(".",45);
    $doc .= "<div class='ghpp-pay'>"
            . "<table>"
            . "<tr>"
            . "<td rowspan='4'>การขำระเงิน</td>"
            . "<td><span class='square-check'></span>เงินสด</td>"
            . "</tr>"
            . "<tr><td><span class='square-check'></span>เช็คธนาคาร.$dot.สาขา.$dot.</td></tr>"
            . "<tr><td><span class='square-check blank'></span>เช็คเลขที่.$dot.ลงวันที่.$dot.</td></tr>"
            . "<tr><td><span class='square-check'></span>โอนเงิน จากบัญชีธนาคาร.$dot.เลขที่บัญชี.$dot.</td></tr>"
            . "</table>"
            . "<p style='font-size:11pt;'>โปรดจ่ายเช็คในนาม \"".$company_info['name']."\" ทุกครั้ง</p>"
            . "<p style='font-size:11pt;'>ใบเสร็จรับเงินฉบับนี้จะสมบูรณ์ ต่อเมื่อบริษัทฯได้เรียกเก็บเงินตามเช็คหรือจากบัญชีเรียบร้อยแล้ว และปรากฎลายเซนต์ของผู้รับเงิน</p>"
            . "</div><!-- .ghpp-pay -->";
    $doc .= "<div class='ghpp-sign'>"
            . "<table>"
            . "<tr><td>&nbsp;</td>"
            . "<td>"
            . "<p>ผู้รับเงิน.........................................................</p>"
            . "<p>(............................................................)</p>"
            . "<p>วันที่......./......../........</p>"
            . "</td>"
            . "</tr>"
            . "</table>"
            . "</div><!-- .ghpp-sign -->";

    $doc .= "</div><!-- .print-letter -->";
    return $doc;
}