<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$db = new csvPDO();
if(!$_GET){
    header("location:".PAP);
}
$req = filter_input(INPUT_GET,'req',FILTER_SANITIZE_STRING);

if($req == "quote_csv"){
    //prepare data
    $month = filter_input(INPUT_GET,'month',FILTER_UNSAFE_RAW);
    $uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_NUMBER_INT);
    $head[] = array("รหัส","ชื่อ","รหัสลูกค้า","ชื่อลูกค้า","ขนาด","หน้า","ยอดผลิต","ราคา","วันที่สร้าง","วันที่ตรวจ","วันที่สมบูรณ์","สถานะ","Sale");
    $rec = $db->get_quote_csv($op_quote_status,$month,$uid);
    $tt = array_merge($head,$rec);
    //var_dump($tt);
    $filename = "quote.csv";
} else if($req == "ac_buy"){
    $due = filter_input(INPUT_GET,'due',FILTER_UNSAFE_RAW);
    $head[] = array("ใบสั่งซื้อ","ผู้ผลิต","วันที่รับสินค้า","กำหนดชำระ","วันที่ชำระเงิน","เอกสารการชำระ");
    $rec = $db->get_acbuy_csv($due);
    $tt = array_merge($head,$rec);
    $filename = "acc_buy.csv";
} else if($req == "acc"){
    $due = filter_input(INPUT_GET,'due',FILTER_UNSAFE_RAW);
    $head[] = array("ใบส่งของ","ลูกค้า","งาน","วันที่ส่ง","กำหนดชำระเงิน","ยอดก่อนVat","Vat 7%","หัก3%","ยอดหลังหัก3%","ใบวางบิล","วันนัดชำระ","ใบกำกับ","วันที่ใบกำกับ","ยอดใบกำกับ","ใบเสร็จ","วันที่ใบเสร็จ","ยอดใบเสร็จ");
    $rec = $db->get_acc_csv($due);
    $tt = array_merge($head,$rec);
    $filename = "acc.csv";
} else if($req == "deli_csv"){
    $month = filter_input(INPUT_GET,'month',FILTER_UNSAFE_RAW);
    $head[] = array("ชื่องาน","ลูกค้า","กำหนดส่ง","ยอดผลิต","สร้าง","ใบแจ้งหนี้","ใบส่งของ","สถานะ");
    $rec = $db->get_deli_csv($op_job_status+$op_job_account,$month);
    $tt = array_merge($head,$rec);
    //var_dump($tt);
    $filename = "deli.csv";
} else if($req == "mdeli_csv"){
    $month = filter_input(INPUT_GET,'month',FILTER_UNSAFE_RAW);
    $head[] = array("ชื่องาน","ลูกค้า","ยอดผลิต","ยอดแจ้งหนี้","สร้าง","ใบแจ้งหนี้","ใบส่งของ","สถานะ");
    $rec = $db->get_mdeli_csv($op_job_status+$op_job_account,$month);
    $tt = array_merge($head,$rec);
    //var_dump($tt);
    $filename = "mdeli.csv";
} else {
    header("location:".PAP);
    exit();
}
if(isset($filename)){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=$filename;");
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $output = fopen("php://output", "w");
    fputs( $output, "\xEF\xBB\xBF" );
    foreach($tt as $k=>$v){
        fputcsv($output,$v);
    }
    exit();
}
class csvPDO{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function get_quote_csv($op,$month=null,$uid=null){
        try {
            $filter = "WHERE quo.quote_id>0";
            $filter .= (isset($month)&&$month!=""?" AND DATE_FORMAT(created,'%m%Y')='$month'":"");
            $filter .= (isset($uid)&&$uid>0?" AND sale.user_id=$uid":"");
            $sql = <<<END_OF_TEXT
SELECT 
quote_no,
name,
cus.customer_code,
cus.customer_name,
CONCAT(size_name,' (',size_height,'x',size_width,')') AS size,
meta.meta_value AS page,amount,q_price,
created,approved,finished,status,user.user_login
FROM pap_quotation AS quo
LEFT JOIN pap_option AS op ON op.op_id=cat_id
LEFT JOIN pap_size ON size_id=job_size_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=quo.quote_id AND meta.meta_key='page_inside'
LEFT JOIN pap_customer AS cus ON cus.customer_id=quo.customer_id
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=cus.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
$filter
ORDER BY created ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $res[$k]['status'] = $op[$v['status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_acbuy_csv($due=null){
        try {
            $filter = (isset($due)&&$due!=""?"WHERE DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m')='$due'":"");
            $sql = <<<END_OF_TEXT
SELECT
po_code,
CONCAT(sup.code,":",sup.name),
po_deliveried,DATE_ADD(po_deliveried, INTERVAL po_payment DAY) AS due,
po_paid,po_paid_ref
FROM pap_mat_po AS mat
LEFT JOIN pap_supplier AS sup ON sup.id=mat.supplier_id $filter
UNION
SELECT
po_code,
CONCAT(sup.code,":",sup.name),
po_deliveried,DATE_ADD(po_deliveried, INTERVAL po_payment DAY) AS due,
po_paid,po_paid_ref
FROM pap_process_po AS mat
LEFT JOIN pap_supplier AS sup ON sup.id=mat.supplier_id $filter
ORDER BY due ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_acc_csv($due=null){
        try {
            $filter = (isset($due)&&$due!=""?"WHERE DATE_FORMAT(DATE_ADD(deli.date, INTERVAL dt.credit DAY),'%Y-%m')='$due'":"");
            $sql = <<<END_OF_TEXT
SELECT 
deli.no,
cus.customer_name,
job.name,
deli.date AS delidate,
DATE_ADD(deli.date, INTERVAL job.credit DAY),
deli.total,
deli.total*IF(meta_value='yes',0,0.07) AS vat,
deli.total*0.03 AS tax,
deli.total*IF(meta_value='yes',0.97,1.04) AS aftertax,
bill.no AS bno,
bill.pay_date,
iv.no AS ivno,iv.date AS ivdate,ivdt.amount*IF(meta_value='yes',1,1.07) AS ivamount,
rc.no AS rcno,rc.date AS rcdate,rcdt.amount
FROM pap_delivery AS deli
LEFT JOIN (
	SELECT deli_id,GROUP_CONCAT(job_name) AS name,MIN(credit) AS credit
    FROM pap_delivery_dt
    GROUP BY deli_id
) AS job ON job.deli_id=deli.id
LEFT JOIN pap_contact AS ct ON ct.contact_id=deli.contact
LEFT JOIN pap_customer AS cus ON cus.customer_id=ct.customer_id
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=cus.customer_id AND meta_key='tax_exclude'
LEFT JOIN pap_pbill_dt AS bdt ON bdt.deli_id=deli.id
LEFT JOIN pap_pbill AS bill ON bill.id=bdt.pbill_id
LEFT JOIN pap_invoice_dt AS ivdt ON ivdt.deli_id=deli.id
LEFT JOIN pap_invoice AS iv ON iv.id=ivdt.invoice_id
LEFT JOIN pap_rc_dt AS rcdt ON rcdt.invoice_id=iv.id
LEFT JOIN pap_rc AS rc ON rc.id=rcdt.rc_id
$filter
ORDER BY DATE_ADD(deli.date, INTERVAL job.credit DAY) ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_deli_csv($op,$month=null){
        try {
            $filter = "WHERE po.status>=69";
            $filter .= (isset($month)&&$month!=""?" AND DATE_FORMAT(deli.date,'%y%m')='$month'":"");
            $sql = <<<END_OF_TEXT
SELECT
CONCAT(order_no,":",quo.name),
CONCAT(cus.customer_code,":",cus.customer_name),
quo.plan_delivery AS due,
quo.amount,
deli.date,
deli.no,
GROUP_CONCAT(tdeli.no ORDER BY tdeli.no) AS gtno,
deli.status
FROM pap_order AS po
LEFT JOIN pap_quotation AS quo on quo.quote_id=po.quote_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=quo.customer_id
LEFT JOIN pap_delivery_dt AS dt ON dt.order_id=po.order_id
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_temp_deli AS tdeli ON tdeli.deli_id=deli.id
$filter
GROUP BY po.order_id
ORDER BY quo.plan_delivery ASC, tdeli.id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $res[$k]['status'] = $op[$v['status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mdeli_csv($op,$month=null){
        try {
            $filter = "WHERE dt.order_id=''";
            $filter .= (isset($month)&&$month!=""?" AND DATE_FORMAT(deli.date,'%y%m')='$month'":"");
            $sql = <<<END_OF_TEXT
SELECT
dt.job_name,cus.customer_name,
FORMAT(dt.qty,0) AS qty,FORMAT(dt.price,2) AS price,
deli.date,
deli.no AS delino,
GROUP_CONCAT(CONCAT(tdeli.no,"(",tdt.qty,")") ORDER BY tdeli.no ASC) AS tdeli,
deli.status
FROM pap_delivery_dt AS dt
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=dt.customer_id
LEFT JOIN pap_temp_deli AS tdeli ON tdeli.deli_id=deli.id
LEFT JOIN pap_temp_dt AS tdt ON tdt.job_name=dt.job_name AND temp_deli_id=tdeli.id
$filter
GROUP BY dt.job_name
ORDER BY deli.date ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $res[$k]['status'] = $op[$v['status']];
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}
    





