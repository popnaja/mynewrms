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
    $comps = $db->get_print_comp($qid);
    $cus= $db->get_info("pap_customer","customer_id",$info['customer_id'])+$db->get_meta("pap_customer_meta", "customer_id", $info['customer_id']);
    $product_type = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='product_cat'");
    $ct = $db->get_info("pap_contact", "contact_id", $info['contact_id']);
    $show_date = (is_null($info['approved'])?$info['created']:$info['approved']);

    $head = "<div class='doc-info'>"
        . "<div class='doc-to'>"
        . "<div class='float-left doc-600'>ถึง/To : </div>"
        . print_cus_info($info['customer_id'])
        . "</div><!-- .doc-to -->"
        . "<div class='doc-to'>"
        . "<div class='float-left doc-600'>ติดต่อ : </div>"
        . "<div class='sup-info'>"
        . $ct['contact_name']. " (". $ct['contact_tel'].")"
        . "</div><!-- .sup-info -->"
        . "</div><!-- .doc-to -->"
        . "<div class='doc-date'>"
        . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($show_date)."</div>"
        . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['quote_no']."</div>"
        . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
        . "</div>"
        . "</div>";

    //count list
    $x = 0;
    //list
    $process =  $db->get_keypair("pap_process", "process_id", "process_name");

    if($info['cat_id']==10){
        $page = "<li>เนื้อใน ".$info['page_inside']. " หน้า</li>";
    } else {
        if($info['page_inside']==2){
            $page = "<li>พิพม์ 2 ด้าน</li>";
        } else {
            $page = "<li>พิพม์ 1 ด้าน</li>";
        }
    }
    $bind = ($info['binding_id']>0?$process[$info['binding_id']]:"ไม่มี");
    $bname = "<div class='print-box'>"
            . "<div class='print-list-title'>บริการงานพิมพ์</div>"
            . "<ul class='print-list'>"
            . "<li>ชื่องาน : ".$info['name']."</li>"
            . "<li>ประเภท : ".$product_type[$info['cat_id']]."</li>"
            . "<li>ขนาด : ".$info['size']. "</li>"
            . "<li>เข้าเล่ม : $bind</li>"
            . $page
            . "</ul>"
            . "</div>";
    $x += 5;
    $cno = 1;
    foreach($comps as $k=>$v){
        $post = explode(",",$v['comp_postpress']);
        if($info['cat_id']==10){
            if($v['comp_type']==1){
                $cname = "เนื้อใน ";
                $cname .= (count($comps)>2?"($cno)":"");
                $cno++;
            } else {
                $cname = "ปก";
            }
        } else {
            $cname = "ลักษณะชิ้นงาน";
        }
        $bname .= "<div class='print-box'>"
                . "<div class='print-list-title'>$cname</div>"
                . "<ul class='print-list'>"
                . "<li>".$v['paper']."</li>"
                . "<li>".$v['weight']." แกรม</li>"
                . "<li>".$v['color']."</li>"
                . (isset($v['coating'])?"<li>".$v['coating']."</li>":"");
        $x +=4;
        foreach($post as $p){
            if($p>0){
                $bname .= "<li>".$process[$p]."</li>";
                $x++;
            }
        }
        $bname .= "</ul>"
                . "</div>";
    }
    if($info['prepress']!==""){
        $pp = explode(",",$info['prepress']);
        $bname .= "<div class='print-box'>"
                . "<div class='print-list-title'>การจัดทำต้นฉบับ</div>"
                . "<ul class='print-list'>";
        foreach($pp as $k=>$v){
            if($v>0){
                $bname .= "<li>$process[$v]</li>";
                $x++;
            }
        }
        $bname .= "</ul>"
                . "</div>";
    }
    if(strlen($info['packing'])>0||strlen($info['shipping'])>0){
        $packing = explode(",",$info['packing']);
        $shipping = explode(",",$info['shipping']);
        $bname .= "<div class='print-box'>"
                . "<div class='print-list-title'>ข้อกำหนดอื่นๆ</div>"
            . "<ul class='print-list'>";
        foreach($packing as $v){
            if($v>0){
                $bname .= "<li>$process[$v]</li>";
                $x++;
            }
        }
        foreach($shipping as $v){
            if($v>0){
                $bname .= "<li>$process[$v]</li>";
                $x++;
            }
        }
        $bname .= "</ul>"
                . "</div>";
    }

    //$due = ($info['plan_delivery']>0?"<div class='print-list-title'>กำหนดส่งงาน : ".thai_date($info['plan_delivery'])."</div>":"");
    //multi quote

    $extra = "";
    if(isset($info['multi_quote_info'])&&strlen($info['multi_quote_info'])>3){
        $dt = json_decode($info['multi_quote_info'],true);
        $x += count($dt)+2;
        $qhead = array("ยอดพิมพ์","ราคาหน่วยละ","จำนวนเงิน","หมายเหตุ");
        $qrec = array();
        foreach($dt as $k=>$v){
            if($v['show']>0){
                array_push($qrec,array(number_format($v['amount']),number_format($v['price']/$v['amount'],2),number_format($v['price'],2),$v['remark']));
            }
        }
        if(count($qrec)>0){
            $extra = "<div class='print-extra'>"
                . "<b>เสนอราคาที่ยอดพิมพ์ปริมาณอื่น (ราคายังไม่รวมภาษีมูลค่าเพิ่ม)</b>"
                . $tb->show_table($qhead,$qrec,"tb-multi-q")
                . "</div>";
        }
    }
    //detail
    $tt = (float)$info['q_price'];
    $amount = (int)$info['amount'];
    $peru = $tt/$amount;
    $header = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = array();
    if(isset($info['detail_price'])){
        $dt = json_decode($info['detail_price'],true);
        $sub = 0; //เก็บข้อมูลราคา เอาไปหักออกจาก total เพื่อ show ค่าพิมพ์
        $i = 1;
        foreach($dt as $k=>$v){
            if($v[0]>0){
                array_push($recs,array($i,$v[1],$v[2],$v[3],$v[4]));
                $sub += $v[4];
                $i++;
                $x++;
            }
        }
        array_push($recs,array($i,$bname,$amount,($tt-$sub)/$amount,$tt-$sub));
    } else {
        array_push($recs,array(1,$bname,$amount,$peru,$tt));
    }

    //check row
    if($x>29){
        $page1 = "หน้า 1/2";
        $page2 = "</div><!-- .print-a4-fix -->"
                . "<div class='print-a4-fix'>"
                . print_header("ใบเสนอราคา","หน้า 2/2")
                . $head
                . $extra;
    } else if($x>18){
        $page1 = "หน้า 1/2";
        $page2 = $extra
                . "</div><!-- .print-a4-fix -->"
                . "<div class='print-a4-fix'>"
                . print_header("ใบเสนอราคา","หน้า 2/2")
                . $head;
    } else {
        $page1 = "";
        $page2 = $extra;

    }
    $discount = (int)$info['discount'];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $content = "<div class='print-a4-fix'>"
            . print_header("ใบเสนอราคา",$page1)
            . $head
            . "<div class='doc-dt'>"
            . $tb->show_tb_wtax($header,$recs,"tb-rp",$tax,$discount)
            . "</div><!-- .doc-dt -->";




    //sign
    $pay = ($info['credit']>0?"เครดิต ".$info['credit']." วัน":"ชำระเป็นเงินสด");
    $sign = "";
    $msign = "";
    $date = thai_date($show_date);
    if($info['user_id']>0){
        $sale = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
        $sign = (isset($sale['signature'])?"<img src='".ROOTS.$sale['signature']."' />":"");
    }
    if($info['status']>=2){
        $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
        $msign = "<img src='".ROOTS.$manager['signature']."' />";
    }
    $content .= $page2
            . "<table id='rp-2sign' class='doc-final'>"
    . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='180'>ผู้อนุมัติ</th><th width='180'>เจ้าหน้าที่ฝ่ายขาย</th></tr>"
    . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='70'>$msign</td><td class='doc-sign' height='70'>$sign</td></tr>"
    . "<tr><td>วันที่ : $date</td><td>วันที่ : $date</td></tr>"
    . "</table>";

    $content .= "<table id='rp-cus-sign' class='doc-final'>"
            . "<tr><td rowspan='4'>"
            . "<ul style='padding-left:0.7cm;'>"
            . "<li>บริษัทฯขอสงวนสิทธิในการเปลี่ยนแปลงราคา หากรายละเอียดงานมีการเปลี่ยนแปลงเกิดขึ้นภายหลังการว่าจ้าง</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับฝากสิ่งพิพม์ที่สำเร็จแล้วและถึงกำหนดส่งของ โดยไม่ขอรับผิดชอบในความเสียหายกับสิ่งพิมพ์นั้นๆในทุกกรณี</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับผิดชอบในเพลทที่ผู้ว่าจ้างจัดส่งมาให้ และไม่ทำการรับเพลตคืนหลังจากที่ได้พิมพ์งานแล้วเสร็จ</li>"
            . "<li>บริษัทฯขอสงวนสิทธิในการไม่รับผิดชอบ หากมีการละเมิดลิขสิทธิ์ในข้อเขียน, บทความ, บทปรพันธ์, การออกแบบ ฯลฯ</li>"
            . "</ul>"
            . "</td>"
            . "<th width='360'>สำหรับลูกค้าเพื่อตอบรับและส่งกลับมาที่บริษัทฯ</th>"
            . "</tr>"
            . "<tr><td>ข้าพเจ้ารับทราบเอกสารว่าจ้างและเงื่อนไขงานตามใบเสนอราคา</td></tr>"
            . "<tr><td height='90'></td></tr>"
            . "<tr><td>ประทับตราบริษัท(ถ้ามี)<span class='float-right'>วันที่:____/_____/______</span></td></tr>"
            . "</table";

    $content .= "</div><!-- .print-a4-fix -->";
    return $content;
}
function show_order($oid,$edit=false){
    global $op_paper_div;
    global $rpdb;
    $db = $rpdb;
    __autoload("pdo_report");

    $rp = new reportPDO();
    $info = $rp->rp_order($oid);
    $comp = $rp->rp_order_comp($oid);
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");

    $cno = 1;
    $paper_html = "";
    $plate_html = "";
    $after = "";
    foreach($comp as $k=>$com){
        if(count($comp)>1&&$com['type']==9){
            continue;
        }
        //packing & shipping
        $pack = $rp->rp_order_cpro($com['id'], "(11,12)");
        $packing = "<tr>"
                . "<th colspan='2'>การห่อ</th>"
                . "<td colspan='5'>".(isset($pack[11])?$pack[11]:"")."</td>"
                . "</tr><tr>"
                . "<th colspan='2'>ขนส่ง</th>"
                . "<td colspan='5'>".(isset($pack[12])?$pack[12]:"")."</td>"
                . "</tr>";

        $cname = $com['name'];
        $div = ($com['paper_cut']>1?" (".$op_paper_div[$com['paper_cut']].")":"");
        $paper_html .= "<tr>"
                . "<td align='center'>$cname</td>"
                . "<td colspan='4'>".$com['mat_name'].$div."</td>"
                . "<td align='center' colspan='2'>".number_format($com['rim'],2)."</td>"
                . "</tr>";

        //plate
        $print = $rp->rp_order_cpro($com['id'], "(3)");

        $set = explode(";",$print[3]);
        for($i=0;$i<count($set);$i++){
            $pinfo = explode(",",$set[$i]);
            $sheet = $pinfo[3]-$com['allowance'];
            $tt = ceil($pinfo[0])*$pinfo[3]/($pinfo[0]>=2?2:1);
            $plate_html .= "<tr align='center'>"
                    . "<td>$cname ".$pinfo[2]."</td>"
                    . "<td>".ceil($pinfo[0])." กรอบ<br/>".$pinfo[1]."</td>"
                    . "<td>".($com['paper_cut']>1?$op_paper_div[$com['paper_cut']]."<br/>".$com['print_size']:"-")."</td>"
                    . "<td>".$com['paper_lay']."</td>"
                    . "<td>".number_format($sheet,0)."</td>"
                    . "<td>".number_format($com['allowance'],0)."</td>"
                    . "<td>".number_format($tt,0)."</td>"
                    . "</tr>";
        }

        // post-print
        $post = $rp->rp_order_cpro($com['id'], "(4,5)");
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
    $content = "<div class='print-a4'>"
            . print_header("ใบสั่งซื้อ");

    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ร้านค้า : </div>"
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
            . $tb->show_tb_wtax($head,$rec,"tb-rp",0.07,0)
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
    $content = "<div class='print-a4'>"
            . print_header("ใบสั่งผลิต");

    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ร้านค้า : </div>"
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
            . $tb->show_tb_wtax($head,$rec,"tb-rp",0.07,0)
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
    $ct = $db->get_info("pap_contact", "contact_id", $info['contact']);
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $ct['customer_id']);
    $aoid = explode(",",$info['aoid']);

    $content = "<div class='print-a4'>"
            . print_header("ใบส่งของ/<br/>ใบแจ้งหนี้");
    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ลูกค้า : </div>"
            . print_cus_info($info['customer_id'],$info['address'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ติดต่อ : </div>"
            . "<div class='sup-info'>"
            . $ct['contact_name']. " (". $ct['contact_tel'].")"
            . "</div><!-- .sup-info -->"
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['date'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_deli_dt($did);
    $discount = $recs[1];
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs[0],"tb-rp",$tax,$discount)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";

    $i = 0;
    $pay = "";
    foreach($aoid as $oid){
        $oinfo = $rp->rp_order($oid);
        $pay .= ($i==0?"":"<br/>");
        $pay .= $oinfo['name']." (".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
        $i++;
    }

    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='180'>ผู้รับสินค้า</th><th width='180'>ผู้ส่งสินค้า</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'></td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : </td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-a4 -->";
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

    $ct = $db->get_info("pap_contact", "contact_id", $info['contact']);
    $cus = $db->get_meta("pap_customer_meta", "customer_id", $ct['customer_id']);
    $aoid = explode(",",$info['aoid']);

    $content = "<div class='print-a4'>"
            . print_header("ใบส่งของชั่วคราว");
    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ลูกค้า : </div>"
            . print_cus_info($info['customer_id'],$info['address'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ติดต่อ : </div>"
            . "<div class='sup-info'>"
            . $ct['contact_name']. " (". $ct['contact_tel'].")"
            . "</div><!-- .sup-info -->"
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".  thai_date($info['date'])."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_tdeli_dt($tdid);
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $content .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,0)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";

    $i = 0;
    $pay = "";
    foreach($aoid as $oid){
        $oinfo = $rp->rp_order($oid);
        $pay .= ($i==0?"":"<br/>");
        $pay .= $oinfo['name']." (".($oinfo['credit']>0?"เครดิต ".$oinfo['credit']." วัน":"ชำระเป็นเงินสด").")";
        $i++;
    }

    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>$pay</td><th width='180'>ผู้รับสินค้า</th><th width='180'>ผู้ส่งสินค้า</th></tr>"
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
    $thdate = thai_date($info['date']);
    $ct = $db->get_info("pap_contact", "contact_id", $info['contact']);
    $content = "<div class='print-a4'>"
            . print_header("ใบวางบิล");
    $content .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ลูกค้า : </div>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ติดต่อ : </div>"
            . "<div class='sup-info'>"
            . $ct['contact_name']. " (". $ct['contact_tel'].")"
            . "</div><!-- .sup-info -->"
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
            . $tb->show_tb_bill($head,$recs,"tb-rp")
            . "<span style='font-size:11pt;'>ได้รับบิลไว้ตรวจสอบตามรายการข้างต้นนี้ถูกต้องแล้ว</span>"
            . "</div><!-- .doc-dt -->";
    $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
    $msign = "<img src='".ROOTS.$manager['signature']."' />";

    $content .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>การชำระเงิน :</th><td>".$info['payment']."</td><th width='180'>ผู้รับวางบิล</th><th width='180'>ผู้วางบิล</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'></td><td class='doc-sign' height='100'>$msign</td></tr>"
            . "<tr><td>วันที่ : </td><td>วันที่ : $thdate</td></tr>"
            . "</table>";

    $content .= "</div><!-- .print-a4 -->";
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
    $original = "<div class='print-a4-fix'>"
            . print_header("ต้นฉบับ<br/>ใบกำกับภาษี");
    $copy = "<div class='print-a4-fix'>"
            . print_header("สำเนา<br/>ใบกำกับภาษี");
    $doc = "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ลูกค้า : </div>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-to'>"
            //. "<div class='float-left doc-600'>ติดต่อ : </div>"
            //. "<div class='sup-info'>"
            //. $ct['contact_name']. " (". $ct['contact_tel'].")"
            //. "</div><!-- .sup-info -->"
            . "</div><!-- .doc-to -->"
            . "<div class='doc-date'>"
            . "<div class='doc-600'> <span class='float-left'>วันที่/Date : </span>".$thdate."</div>"
            . "<div class='doc-600'> <span class='float-left'>เลขที่เอกสาร : </span>".  $info['no']."</div>"
            . "<div class='doc-600'> <span class='float-left'>Sale Rep : </span>". $info['user_login']."</div>"
            . "</div>"
            . "</div>";

    $head = array("ลำดับ<br/>No","รายการ<br/>List","จำนวน<br/>Quantity","ราคาหน่วยละ<br/>Unit Price","จำนวนเงิน<br/>Amount(Baht)");
    $recs = $rp->rp_invoice_dt($ivid,$op_type_unit);
    $discount = 0;
    $tax = ($cus['tax_exclude']=="yes"?0:0.07);
    $doc .= "<div class='doc-dt'>"
            . $tb->show_tb_wtax($head,$recs,"tb-rp",$tax,$discount)
            . "<span style='font-size:11pt;'>ได้รับสินค้า และรับทราบข้อตกลงอื่นๆ ตามรายการข้างต้นไว้ถูกต้องเรียบร้อยแล้ว</span>"
            . "</div><!-- .doc-dt -->";

    $user = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
    $usign = "<img src='".ROOTS.$user['signature']."' />";
    $manager = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=4");
    $msign = "<img src='".ROOTS.$manager['signature']."' />";
    $pay = "";

    $doc .= "<table id='rp-2sign' class='doc-final'>"
            . "<tr><th width='110'>ชำระเงินโดย :</th><td>$pay</td><th width='180'>ผู้รับเงิน</th><th width='180'>ผู้จัดการ</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'>$usign</td><td class='doc-sign' height='100'>$msign</td></tr>"
            . "<tr><td>วันที่ : $thdate</td><td>วันที่ : $thdate</td></tr>"
            . "</table>";

    $doc .= "</div><!-- .print-a4 -->";
    return $original.$doc.$copy.$doc;
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

    $doc = "<div class='print-a4-fix'>"
            . print_header("ใบเสร็จรับเงิน");

    $doc .= "<div class='doc-info'>"
            . "<div class='doc-to'>"
            . "<div class='float-left doc-600'>ลูกค้า : </div>"
            . print_cus_info($info['customer_id'])
            . "</div><!-- .doc-to -->"
            . "<div class='doc-to'>"
            //. "<div class='float-left doc-600'>ติดต่อ : </div>"
            //. "<div class='sup-info'>"
            //. $ct['contact_name']. " (". $ct['contact_tel'].")"
            //. "</div><!-- .sup-info -->"
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
            . $tb->show_tb_bill($head,$recs,"tb-rp",$tax,$discount)
            . "<span style='font-size:11pt;'>ใบเสร็จรับเงินฉบับนี่จะใช้ได้ต่อเมื่อสามารถเรียกเก็บเงินได้ตามเช็คแล้วเท่านั้น</span>"
            . "</div><!-- .doc-dt -->";
    //signature
    $user = $db->get_keypair("pap_usermeta", "meta_key", "meta_value","WHERE user_id=".$info['user_id']);
    $usign = "<img src='".ROOTS.$user['signature']."' />";
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
            . "<tr><th width='110'>ชำระเงินโดย :</th><td>$pay</td><th width='180'>ผู้รับเงิน</th><th width='180'>ผู้จัดการ</th></tr>"
            . "<tr><th rowspan='2'>หมายเหตุ : </th><td rowspan='2'>".$info['remark']."</td><td class='doc-sign' height='100'>$usign</td><td class='doc-sign' height='100'>$msign</td></tr>"
            . "<tr><td>วันที่ : $thdate</td><td>วันที่ : $thdate</td></tr>"
            . "</table>";

    $doc .= "</div><!-- .print-a4 -->";
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
    $sup = "<div class='sup-info'>"
        . $sinfo['name']."<br/>"
        . $sinfo['address']."<br/>"
        . "Tel: ".$sinfo['tel']."<br/>"
        . "<span class='c-tax'>Tax ID: ".$sinfo['taxid']."</span>"
        . "</div><!-- .sup-info -->";
    return $sup;
}
function print_cus_info($cid,$aid=0){
    global $rpdb;
    $db = $rpdb;
    $sinfo = $db->get_info("pap_customer", "customer_id", $cid);
    $contact = "";
    if($aid>0){
        $ainfo = $db->get_info("pap_cus_ad", "id", $aid);
        $address = $ainfo['name']."<br/>"
            . $ainfo['address']."<br/>";
    } else {
        $address =  $sinfo['customer_name']."<br/>"
        . $sinfo['customer_address']."<br/>"
        . "Tel: ".$sinfo['customer_tel']."<br/>";
    }
    $sup = "<div class='sup-info'>"
        . $address
        . "<span class='c-tax'>Tax ID: ".$sinfo['customer_taxid']."</span>"
        . $contact
        . "</div><!-- .sup-info -->";
    return $sup;
}
function print_header($docname,$page=null){
    global $rpdb;
    $db = $rpdb;
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $clogo = "<img src='".ROOTS.$cinfo['c_logo']."'/>";
    $header = "<span class='c-name'>".$cinfo['name']."</span><br/>"
            . $cinfo['address']."<br/>"
            . "Tel: ".$cinfo['tel']."<br/>"
            . "<span class='c-tax'>Tax ID: ".$cinfo['tax_id']."</span>";
    $head = "<div class='c-head'>"
            . "<div class='c-logo'>$clogo</div>"
            . "<div class='c-info'>$header</div>"
            . "<div class='doc-name'><span>$docname</span></div>"
            . "<div class='doc-page'>$page</div>"
            . "</div>";
    return $head;
}
