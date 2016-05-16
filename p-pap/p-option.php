<?php
/*
 * Option for PAP
 */
$op_type_unit = array(
    10 => "เล่ม",
    11 => "ชิ้น",
    12 => "ชิ้น",
    13 => "ชิ้น",
    14 => "ชิ้น",
    69 => "เล่ม",
);
$op_authlevel = array(
    "0"=>"None",
    "1"=>"View",
    "2"=>"Add & Edit",
    "3"=>"Delete",
    "4"=>"Manager"
);
$op_unit = array(
    "allpage" => "หน้า(รวมปก)",
    "page" => "หน้า(ไม่รวมปก)",
    "sheet" => "แผ่น",
    "round" => "รอบ",
    "frame" => "กรอบ",
    "set" => "ยก",
    "piece" => "ชิ้น/เล่ม",
    "km" => "ระยะทาง(กม)",
    "in2" => "ตารางนิ้ว"
);
$op_criteria = $op_unit+array(
    //"area" => "พื้นที่(cm2)"
);
$op_process_source = array(
    "1" => "ผลิตเอง",
    "2" => "สั่งผลิต"
);
$op_payment = array(
    "0" => "เงินสด",
    "1" => "เครดิต"
);
$op_comp_type = array(
    "1" => "ปก",
    "2" => "เนื้อใน",
    "3" => "ชิ้นงาน",
    "4" => "ใบพาด",
    "5" => "แจ็คเก็ด",
);
$op_cover_p = array(
    "1" => "2 หน้า (ปกหน้า+ปกหลัง)",
    "2" => "4 หน้า (พิพม์ปกใน)"
);
$op_paper_costt = array(
    "baht/kg" => "บาท/กิโลกรัม",
    "baht/lot" => "บาท/ห่อ"
);
$op_paper_div = array(
    "1" => "--ไม่ผ่า--",
    "2" => "ผ่าครึ่ง"
);
$op_quote_ex = array(
    "1" => "นำเพลตมาเอง/พิมพ์ซ้ำ",
    "2" => "นำกระดาษมาเอง"
);
$op_print_toplate = array(
    29 => 37,
    30 => 38,
    10 => 26,
    31 => 39
);
$op_print_color = array(
    29 => 1,
    30 => 2,
    10 => 4,
    31 => 5
);
$op_tax_name = array(
    "supplier" => array("ฝ่ายจัดซื้อ","กลุ่มผู้ผลิต"),
    "customer" => array("ลูกค้า","กลุ่มลูกค้า")
);
$op_bill_payment = array(
    "รับชำระเป็นเช็ค" => "รับชำระเป็นเช็ค",
    "รับชำระด้วยวิธีการโอนเงิน" => "รับชำระด้วยวิธีการโอนเงิน",
    "รับชำระเป็นเงินสด" => "รับชำระเป็นเงินสด"
);
/* ----------------------------------------------------------- RUN NUMBER ---------------------------------------------------------------*/
$op_digit = array(
    3=>"3 Digits",
    4=>"4 Digits",
    5=>"5 Digits",
    6=>"6 Digits",
    6=>"7 Digits"
);
$op_run_no = array(
    "QT,%y%m,5," => "QTYYMM 5 Digits"
);
$op_run_order = array(
    "O,%y%m,5," => "OYYMM 5 Digits"
);
$op_run_po = array(
    "PO,%y%m,3," => "POYYMM 3 Digits"
);
$op_run_ppo = array(
    "OS,%y%m,3," => "OSYYMM 3 Digits"
);
$op_run_deli = array(
    "DN,%y%m,4," => "DNYYMM 4 Digits"
);
$op_run_bill = array(
    "B,%y%m,4," => "BYYMM 4 Digits"
);
$op_run_invoice = array(
    "IV,%y%m,4," => "IVYYMM 4 Digits"
);
$op_run_rc = array(
    "RC,%y%m,4," => "RCYYMM 4 Digits"
);
$tax_ex = array("no"=>"ไม่ได้รับการยกเว้นภาษี","yes"=>"ยกเว้นภาษี");

/* ----------------------------------------------------------- MENU ---------------------------------------------------------------*/
$op_type_name = array(
    "product_cat" => array("ผู้ดูแลระบบ","กลุ่มสินค้า"),
    "mat_cat" => array("ผู้ดูแลระบบ","กลุ่มวัสดุ"),
    "paper_type" => array("ผู้ดูแลระบบ","ชนิดกระดาษ"),
    "paper_size" => array("ผู้ดูแลระบบ","ขนาดกระดาษ"),
    "paper_weight" => array("ผู้ดูแลระบบ","แกรมกระดาษ"),
    "paper_allo" => array("ผู้ดูแลระบบ","เผื่อกระดาษเสีย"),
    "customer_cat" => array("ลูกค้า","กลุ่มลูกค้า"),
    "quote_status" => array("ผู้ดูแลระบบ","สถานะใบเสนอราคา")
);
$op_index_menu = array(
    "17" => array(
        "ลูกค้า" => "customer.php",
        "ใบเสนอราคา" => "quotation.php"
    ),
    "51" => array(
        "คำนวณ Lay" => "lay.php",
        "ลูกค้า" => "customer.php",
        "ใบเสนอราคา" => "quotation.php"
    ),
    "1" => array(
        "คำนวณ Lay" => "lay.php",
        "ลูกค้า" => "customer.php",
        "ใบเสนอราคา" => "quotation.php"
    ),
);

/* ----------------------------------------------------------- STATUS ---------------------------------------------------------------*/
$op_cus_status = array(
    "1" => "Inactive",
    "2" => "Active"
);
$op_cus_status_icon = array(
    "1" => "<span class='ez-circle-yellow'></span>",
    "2" => "<span class='ez-circle-green'></span>"
);
$op_quote_status = array(
    1 => "ฉบับร่าง",
    2 => "ตรวจแล้ว",
    3 => "นำเสนอลูกค้าแล้ว",
    4 => "ลูกค้าต่อรอง",
    5 => "ตอบลูกค้าต่อรอง",
    9 => "ลูกค้าตกลง",
    10 => "ลูกค้าปฏิเสธ"
);
$op_quote_status_icon = array(
    1 => "<span class='ez-circle-blank'></span>",
    2 => "<span class='ez-circle-yellow'></span>",
    3 => "<span class='ez-circle-green'></span>",
    4 => "<span class='icon-adjust circle-yellow'></span>",
    5 => "<span class='icon-adjust'></span>",
    9 => "<span class='icon-check'></span>",
    10 => "<span class='icon-remove'></span>"
);
$op_plan_status = array(
    1 => "ยังไม่ใส่แผน",
    2 => "กำหนดแผนแล้ว",
    3 => "เข้าบางส่วน",
    4 => "ทำเสร็จตามแผน",
    5 => "เสร็จช้ากว่าแผน"
);
$op_plan_status_icon = array(
    1 => "<span class='ez-circle-blank'></span>",
    2 => "<span class='ez-circle-yellow'></span>",
    3 => "<span class='icon-adjust'></span>",
    4 => "<span class='ez-circle-green'></span>",
    5 => "<span class='ez-circle-red'></span>",
);
$op_po_status = array(
    1 => "ฉบับร่าง",
    2 => "ตรวจแล้ว",
    3 => "สั่งซื้อแล้ว",
    4 => "สินค้าเข้าบางส่วน",
    5 => "สินค้าเข้าคลัง",
    9 => "ชำระเงินแล้ว",
);
$op_ppo_status = array(
    1 => "ฉบับร่าง",
    2 => "ตรวจแล้ว",
    3 => "สั่งผลิตแล้ว",
    4 => "สินค้าเข้าบางส่วน",
    5 => "สินค้าเข้าคลัง",
    9 => "ชำระเงินแล้ว",
);
$op_po_status_icon = array(
    1 => "<span class='ez-circle-blank'></span>",
    2 => "<span class='ez-circle-yellow'></span>",
    3 => "<span class='ez-circle-hgreen'></span>",
    4 => "<span class='icon-adjust'></span>",
    5 => "<span class='ez-circle-green'></span>",
    9 => "<span class='icon-check'></span>"
);
$op_job_ga = array(
    "1" => "ออกใบสั่งผลิต",
    "2" => "กำหนดแผนส่งเพลต",
    "3" => "Proof on Demand",
    "4" => "Proof Ink Jet",
    "5" => "Proof Plate",
    "7" => "Plate Ready"
);
$op_job_status = array(
    "1" => "ออกใบสั่งผลิต",
    "2" => "กำหนดแผนส่งเพลต",
    "3" => "Proof on Demand",
    "4" => "Proof Ink Jet",
    "5" => "Proof Plate",
    "7" => "Plate Ready",
    "8" => "พร้อมพิมพ์",
    "9" => "พิมพ์",
    "19" => "หลังพิมพ์",
    "69" => "พร้อมส่ง",
    "70" => "ส่งบางส่วน",
    "79" => "ส่งทั้งหมด",
);
$op_job_prod = array(
    "1" => "ออกใบสั่งผลิต",
    "2" => "กำหนดแผนส่งเพลต",
    "3" => "Proof on Demand",
    "4" => "Proof Ink Jet",
    "5" => "Proof Plate",
    "7" => "Plate Ready",
    "8" => "พร้อมพิมพ์",
    "9" => "พิมพ์",
    "19" => "หลังพิมพ์",
    "69" => "พร้อมส่ง",
);
$op_job_code = array(
    "2" => "(PLAN)",
    "3" => "(POD)",
    "4" => "(PIJ)",
    "5" => "(PP)",
    "7" => "(PR)",
);
$op_job_delivery = array(
    "69" => "พร้อมส่ง",
    "70" => "ส่งบางส่วน",
    "79" => "ส่งทั้งหมด",
);
$op_job_delivery_icon = array(
    "69" => "<span class='ez-circle-hgreen'></span>",
    "70" => "<span class='icon-adjust'></span>",
    "79" => "<span class='ez-circle-green'></span>",
    "80" => "ใบแจ้งหนี้",
    "90" => "ออกใบกำกับภาษี",
    "98" => "รับชำระบางส่วน",
    "99" => "รับชำระทั้งหมด"
);
$op_job_account = array(
    "80" => "ใบแจ้งหนี้",
    "90" => "ออกใบกำกับภาษี",
    "98" => "รับชำระบางส่วน",
    "99" => "รับชำระทั้งหมด",
);
$op_date_type = array(
    "eofm" => "ทุกวันสิ้นเดือน",
    "day" => "ระบุวันที่",
    "dofw" => "ระบุวันของสัปดาห์",
);
$op_weekday = array(
    "0" => "อาทิตย์",
    "1" => "จันทร์",
    "2" => "อังคาร",
    "3" => "พุทธ",
    "4" => "พฤหัส",
    "5" => "ศุกร์",
    "6" => "เสาร์"
);
function cal_allo($data,$amount){
    foreach($data as $k=>$v){
        $a = explode(",",$v);
        if($amount>=$a[0]&&$amount<=$a[1]){
            $res = $k;
            break;
        }
    }
    $res = (isset($res)?$res:500);
    return $res;
}
