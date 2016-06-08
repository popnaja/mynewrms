<?php
class pdo_ac{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function get_job_pdue(){
        try {
            $sql = <<<END_OF_TEXT
SELECT DISTINCT
DATE_FORMAT(DATE_ADD(deli.date, INTERVAL quo.credit DAY),'%Y-%m'),
DATE_FORMAT(DATE_ADD(deli.date, INTERVAL quo.credit DAY),'%Y-%m')
FROM pap_order AS job
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
RIGHT JOIN pap_delivery_dt AS dt ON dt.order_id=job.order_id
RIGHT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
WHERE deli.status<99
ORDER BY DATE_ADD(deli.date, INTERVAL quo.credit DAY) ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_job_deli($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
job.order_no,pq.name,pq.amount,IFNULL(SUM(deli.qty),0) AS deli
FROM pap_order AS job
LEFT JOIN pap_quotation AS pq ON pq.quote_id=job.quote_id
LEFT JOIN pap_temp_dt AS deli ON deli.order_id=job.order_id
WHERE job.order_id=:oid
GROUP BY job.order_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_job_mdeli($jname){
        try {
            $sql = <<<END_OF_TEXT
SELECT
dt.job_name,dt.qty,IFNULL(SUM(deli.qty),0) AS deli
FROM pap_delivery_dt AS dt
LEFT JOIN pap_temp_dt AS deli ON deli.job_name=dt.job_name
WHERE dt.order_id=0 AND dt.job_name=:jname
GROUP BY dt.job_name
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":jname",$jname);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_job_pbill($auth,$due=null,$status=null,$s=null,$s_cus=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE deli.status>=69";
            $filter .= (isset($due)?" AND DATE_FORMAT(DATE_ADD(deli.date, INTERVAL dt.credit DAY),'%Y-%m')='$due'":"");
            $filter .= (isset($status)&&$status>0?" AND deli.status=$status":"");
            $filter .= (isset($s)?" AND dt.job_name LIKE '%$s%'":"");
            $filter .= (isset($s_cus)?" AND CONCAT(cus.customer_code,'-',cus.customer_name) LIKE '%$s_cus%'":"");
            if(is_null($s)&&is_null($status)&&is_null($due)&&is_null($s_cus)){
                $filter .= " AND deli.status<99";
            }
            $sql = <<<END_OF_TEXT
SELECT
deli.no,deli.id AS did,
GROUP_CONCAT(DISTINCT cus.customer_name),
GROUP_CONCAT(dt.order_id) AS aoid,
GROUP_CONCAT(dt.job_name) AS jname,
DATE_FORMAT(deli.date,'%d-%b'),
DATE_FORMAT(MIN(DATE_ADD(deli.date, INTERVAL dt.credit DAY)),'%d-%b'),
bdt.pbill_id,bill.no AS bno,
dt.customer_id,DATE_FORMAT(bill.pay_date,'%d-%b'),
deli.total,meta_value AS taxex
FROM pap_delivery_dt AS dt
LEFT JOIN pap_delivery AS deli ON deli.id=dt.deli_id
LEFT JOIN pap_customer AS cus ON cus.customer_id=dt.customer_id
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=cus.customer_id AND meta_key='tax_exclude'
LEFT JOIN pap_pbill_dt AS bdt ON bdt.deli_id=deli.id
LEFT JOIN pap_pbill AS bill ON bill.id=bdt.pbill_id
$filter
GROUP BY deli.id
ORDER BY DATE_ADD(deli.date, INTERVAL dt.credit DAY) ASC
$lim_sql
END_OF_TEXT;
            $sql1 = <<<END_OF_TEXT
SELECT
invoice_id,iv.no,ivdt.amount,iv.total
FROM pap_invoice_dt AS ivdt
LEFT JOIN pap_invoice AS iv ON iv.id=ivdt.invoice_id
WHERE ivdt.deli_id=:did
END_OF_TEXT;
            $sql2 = <<<END_OF_TEXT
SELECT
rc.id,rc.no,rcdt.amount
FROM pap_rc_dt AS rcdt
LEFT JOIN pap_rc AS rc ON rc.id=rcdt.rc_id
WHERE rcdt.invoice_id=:ivid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bindParam(":did",$did);
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bindParam(":ivid",$ivid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res1 = array();
            foreach($res as $k=>$v){
                //main sql
                $did = $v['did'];
                $addinv = "<a href='ac_bill.php?action=inv&did=$did' title='สร้างใบกำกับ' class='icon-plus-square'></a>";
                $res[$k]['no'] = "<a href='delivery.php?action=print&did=".$did."' title='ใบแจ้งหนี้' target='_blank'>".$v['no']."</a>";
                //view pbill
                if(isset($v['pbill_id'])){
                    $bid = $v['pbill_id'];
                    $bno = $v['bno'];
                    $res[$k]['bno'] = "<a href='ac_bill.php?bid=$bid' title='Edit' class='icon-page-edit'></a><a href='ac_bill.php?action=print&bid=$bid' title='View' target='_blank'>$bno</a>";

                } else {
                    $cid = $v['customer_id'];
                    $res[$k]['bno'] = "<input type='checkbox' name='did[]' value='$did,$cid'/>";
                }
                //check delivery
                $aoid = explode(",",$v['aoid']);
                $jname = explode(",",$v['jname']);
                $jobwdeli = "";
                for($x=0;$x<count($aoid);$x++){
                    $oid = $aoid[$x];
                    if($oid>0){
                        $oinfo = $this->check_job_deli($oid);
                        $rem = $oinfo['amount']-$oinfo['deli'];
                        if($rem==0){
                            $jobwdeli .= "<span class='ez-circle-green'></span>".mb_substr($oinfo['name'],0,10,"utf-8")."<br/>";
                        } else {
                            $jobwdeli .= "<span class='icon-adjust ac-show-rm'><span class='ac-rm'>ค้างส่ง ".number_format($rem,0)."</span></span>"
                                    . mb_substr($oinfo['name'],0,10,"utf-8")."</br>";
                        }
                    } else {
                        //check manual deli
                        $jobn = $jname[$x];
                        $minfo = $this->check_job_mdeli($jobn);
                        $rem = $minfo['qty']-$minfo['deli'];
                        if($rem==0){
                            $jobwdeli .= "<span class='ez-circle-green'></span>".mb_substr($minfo['job_name'],0,10,"utf-8")."<br/>";
                        } else {
                            $jobwdeli .= "<span class='icon-adjust ac-show-rm'><span class='ac-rm'>ค้างส่ง ".number_format($rem,0)."</span></span>"
                                    . mb_substr($minfo['job_name'],0,10,"utf-8")."</br>";
                        }
                    }
                }
                $res[$k]['aoid'] = $jobwdeli;
                unset($res[$k]['did']);
                unset($res[$k]['pbill_id']);
                unset($res[$k]['customer_id']);
                unset($res[$k]['total']);
                unset($res[$k]['taxex']);
                unset($res[$k]['jname']);
                if(!isset($res1[implode(";",$res[$k])])){
                    $res1[implode(";",$res[$k])] = array();
                }
                //invoice sql
                $stmt1->execute();
                if($stmt1->rowCount()>0){
                    $invs = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                    $ttiv = 0;
                    for($i=0;$i<count($invs);$i++){
                        $ttiv += $invs[$i]['amount'];
                        $ivid = $invs[$i]['invoice_id'];
                        $ivno = $invs[$i]['no'];
                        $inv = "<div class='iv-div'>"
                                . "<a href='ac_bill.php?action=editinv&ivid=$ivid' title='Edit Invoice' class='icon-page-edit'></a>"
                                . "<a href='ac_bill.php?action=print&ivid=$ivid' title='Print' target='_blank'>$ivno</a>"
                                . "</div>";

                        //receipt sql
                        $stmt2->execute();
                        $rctt = 0;
                        $rc = "<div class='rc-div'>";
                        if($stmt2->rowCount()>0){
                            $rcs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                            for($j=0;$j<count($rcs);$j++){
                                $rctt += $rcs[$j]['amount'];
                                $rcid = $rcs[$j]['id'];
                                $rcno = $rcs[$j]['no'];
                                $rc .= "<p><a href='ac_bill.php?action=editrc&rcid=$rcid' title='แก้ไขใบเสร็จ' class='icon-page-edit'></a>"
                                    . "<a href='ac_bill.php?action=print&rcid=$rcid' title='Print' target='_blank'>$rcno</a></p>";
                            }
                            //check total invoice vs total receipt
                            if($rctt!=$invs[$i]['total']){
                                $rc .= "<p><a href='ac_bill.php?action=rc&ivid=$ivid' title='สร้างใบเสร็จ' class='icon-plus-square'></a></p>";
                            }
                        } else {
                            $rc .= "<a href='ac_bill.php?action=rc&ivid=$ivid' title='สร้างใบเสร็จ' class='icon-plus-square'></a>";
                        }
                        $rc .= "</div>";
                        array_push($res1[implode(";",$res[$k])],array($inv,$rc));
                    }
                    //check total invoice vs total ใบแจ้งหนี้
                    if($ttiv!=$v['total']){
                        array_push($res1[implode(";",$res[$k])],array($addinv,""));
                    }
                } else {
                    array_push($res1[implode(";",$res[$k])],array($addinv,""));
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_bill_list($adid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
@no:=@no+1 AS no,
deli.id,
deli.no AS delino,
deli.date,
IF(MIN(ddt.credit)=0,"เงินสด",CONCAT("เครดิต ",MIN(ddt.credit),"วัน")),
MIN(DATE_ADD(deli.date, INTERVAL ddt.credit DAY)) AS due,
deli.total AS price,
meta.meta_value AS tax
FROM pap_delivery AS deli
LEFT JOIN pap_delivery_dt AS ddt ON ddt.deli_id=deli.id
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=ddt.customer_id AND meta.meta_key='tax_exclude'
WHERE deli.id IN ($adid)
GROUP BY deli.no
ORDER BY deli.no ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare("SET @no=0");
            $stmt->execute();
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            $price = array();
            foreach($res as $k=>$v){
                $did = $v['id'];
                $res[$k]['date'] = thai_date($v['date'], true);
                $res[$k]['due'] = thai_date($v['due'], true);
                $bprice = $v['tax']==="yes" ? $v['price'] :$v['price']*1.07;
                $tprice = round($bprice,2);
                $res[$k]['price'] = number_format($bprice,2)
                        . "<input type='hidden' name='did[]' value='$did'/><input type='hidden' name='price[]' value='$tprice'/>";
                array_push($price,$bprice);
                unset($res[$k]['tax']);
                unset($res[$k]['id']);
            }
            return array($price,$res);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_bill_check($year,$month){
        try{
            $arr = array(array($year,$month));
            $next = new DateTime($year."-".$month."-01",new DateTimeZone("Asia/Bangkok"));
            $prev = new DateTime($year."-".$month."-01",new DateTimeZone("Asia/Bangkok"));
            $next->add(new DateInterval("P1M"));
            $prev->sub(new DateInterval("P1M"));
            array_push($arr,array($next->format("Y"),$next->format("m")),array($prev->format("Y"),$prev->format("m")));
            $sql = <<<END_OF_TEXT
SELECT
cus.customer_id,customer_code AS code,customer_name AS name,customer_place_bill AS bill,customer_collect_cheque AS cheque,
meta.meta_value AS bill_day,
meta1.meta_value AS bill_weekday,
meta2.meta_value AS bill_week,
meta3.meta_value AS cheque_day,
meta4.meta_value AS cheque_weekday,
meta5.meta_value AS cheque_week
FROM pap_customer AS cus
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=cus.customer_id AND meta.meta_key='bill_day'
LEFT JOIN pap_customer_meta AS meta1 ON meta1.customer_id=cus.customer_id AND meta1.meta_key='bill_weekday'
LEFT JOIN pap_customer_meta AS meta2 ON meta2.customer_id=cus.customer_id AND meta2.meta_key='bill_week'
LEFT JOIN pap_customer_meta AS meta3 ON meta3.customer_id=cus.customer_id AND meta3.meta_key='cheque_day'
LEFT JOIN pap_customer_meta AS meta4 ON meta4.customer_id=cus.customer_id AND meta4.meta_key='cheque_weekday'
LEFT JOIN pap_customer_meta AS meta5 ON meta5.customer_id=cus.customer_id AND meta5.meta_key='cheque_week'
WHERE customer_pay>0
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res1 = array();
            for($x=0;$x<count($arr);$x++){
                $year = $arr[$x][0];
                $month = $arr[$x][1];
                foreach($res as $k=>$v){
                    //bill
                    $aday = array();
                    if($v['bill']=="2"){
                        $aday = daystr_to_array($v['bill_day'],31);
                        foreach($aday as $index=>$val){
                            $aday[$index] = $year.$month.sprintf("%02d",$val);
                        }
                    } else if($v['bill']=="3"){
                        $aday = dofw_to_date($year, $month, $v['bill_weekday'], $v['bill_week']);
                    } else if($v['bill']=="1"){ //last day of month
                        $st = new DateTime("$year-$month-01",new DateTimeZone("Asia/Bangkok"));
                        $t = $st->format("t")-1;
                        $st->add(new DateInterval("P".$t."D"));
                        $aday = array($st->format("Ymd"));
                    }
                    if(count($aday)>0){
                        foreach($aday as $date){
                            $name = "<span class='cd-icon icon-file-text-o'></span>".mb_substr($v['name'],0,15,"UTF8");
                            prep_calendar($res1, $date, $v['customer_id'], $name, 1);
                        }
                    }
                    //cheque
                    $aday = array();
                    if($v['cheque']=="2"){
                        $aday = daystr_to_array($v['cheque_day'],31);
                        foreach($aday as $index=>$val){
                            $aday[$index] = $year.$month.sprintf("%02d",$val);
                        }
                    } else if($v['cheque']=="3"){
                        $aday = dofw_to_date($year, $month, $v['cheque_weekday'], $v['cheque_week']);
                    } else if($v['cheque']=="1"){
                        $st = new DateTime("$year-$month-01",new DateTimeZone("Asia/Bangkok"));
                        $t = $st->format("t")-1;
                        $st->add(new DateInterval("P".$t."D"));
                        $aday = array($st->format("Ymd"));
                    } else {
                        $aday = array(0);
                    }
                    if(count($aday)>0){
                        foreach($aday as $date){
                            $name = "<span class='cd-icon icon-banknote'></span>".mb_substr($v['name'],0,15,"UTF8");
                            prep_calendar($res1, $date, $v['customer_id'], $name, 1);
                        }
                    }
                }
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_inv_remain($did,$inv=null){
        try {
            $exclude = (isset($inv)?" AND invoice_id<>$inv":"");
            $sql = <<<END_OF_TEXT
SELECT
deli.no,
GROUP_CONCAT(DISTINCT dt.job_name SEPARATOR ':') AS job,
deli.total,
iv.inv AS ivamount
FROM pap_delivery AS deli
LEFT JOIN pap_delivery_dt AS dt ON dt.deli_id=deli.id
LEFT JOIN (
	SELECT deli_id,SUM(amount) AS inv FROM pap_invoice_dt WHERE deli_id=:did $exclude GROUP BY deli_id
) AS iv ON iv.deli_id=deli.id
WHERE deli.id=:did
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":did",$did);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_rc_remain($ivid,$rcid=null){
        try {
            $exclude = (isset($rcid)?" AND rcdt.rc_id<>$rcid":"");
            $sql = <<<END_OF_TEXT
SELECT
iv.no,iv.total,IFNULL(SUM(rcdt.amount),0) AS pay,meta_value
FROM pap_invoice AS iv
LEFT JOIN pap_rc_dt AS rcdt ON rcdt.invoice_id=iv.id $exclude
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=iv.customer_id AND meta_key='tax_exclude'
WHERE iv.id=:ivid
GROUP BY iv.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":ivid",$ivid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_job_paid($did){
        try {
            $sql = <<<END_OF_TEXT
SELECT
ddt.order_id AS oid,
ddt.price-ddt.discount AS price,
deli.total,
rc.amount AS paid,
iv.ivdiscount AS ivdiscount
FROM pap_delivery AS deli
JOIN pap_delivery_dt AS ddt ON ddt.deli_id=deli.id
LEFT JOIN (
    SELECT deli_id,SUM(rc.amount) AS amount FROM pap_invoice_dt AS dt
    LEFT JOIN pap_rc_dt AS rc ON rc.invoice_id=dt.invoice_id 
    WHERE deli_id=:did
    GROUP BY deli_id
) AS rc ON rc.deli_id=deli.id
JOIN (
	SELECT 
    deli_id,sum(iv.discount) AS ivdiscount
    FROM pap_invoice_dt AS dt
    LEFT JOIN pap_invoice AS iv ON iv.id=dt.invoice_id
    WHERE deli_id=:did
    GROUP BY deli_id
) AS iv ON iv.deli_id=deli.id
WHERE deli.id=:did
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":did",$did);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_po_due(){
        try {
            $sql = <<<END_OF_TEXT
SELECT
DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m') AS due,
DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m')
FROM pap_mat_po WHERE po_deliveried IS NOT NULL
UNION
SELECT
DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m'),
DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m')
FROM pap_process_po WHERE po_deliveried IS NOT NULL
ORDER BY due DESC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_po_list($auth,$paid=null,$due=null,$s_sup=null,$page=null,$perpage=null){
        try {
            $off = (isset($perpage)?$perpage*($page-1):0);
            $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
            $filter = "WHERE po_deliveried IS NOT NULL";
            $filter .= (isset($due)?" AND DATE_FORMAT(DATE_ADD(po_deliveried, INTERVAL po_payment DAY),'%Y-%m')='$due'":"");
            $filter .= (isset($s_sup)?" AND CONCAT(sup.code,':',sup.name) LIKE '%$s_sup%'":"");
            $filter .= (isset($paid)&&$paid=="true"?" AND po_paid IS NOT NULL":" AND po_paid IS NULL");
            if(is_null($due)&&is_null($s_sup)&&is_null($paid)){
                $filter .= " AND po_paid IS NULL";
            }
            $sql = <<<END_OF_TEXT
SELECT
CONCAT("<a href='paper.php?action=print&poid=",po_id,"' title='View' target='_blank'>",po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
po_deliveried,DATE_ADD(po_deliveried, INTERVAL po_payment DAY) AS due,
IF(po_paid IS NOT NULL,
    CONCAT("<a href='' title='ชำระเงิน' class='edit-po-paid icon-page-edit' poid='",po_id,"' ttable='pap_mat_po' info='",po_paid,",",po_paid_ref,"'></a>",po_paid_ref),
    CONCAT("<a href='' title='ชำระเงิน' class='po-paid icon-plus-square' poid='",po_id,"' ttable='pap_mat_po'></a>")
)
FROM pap_mat_po AS mat
LEFT JOIN pap_supplier AS sup ON sup.id=mat.supplier_id $filter
UNION
SELECT
CONCAT("<a href='outsource.php?action=print&poid=",po_id,"' title='View' target='_blank'>",po_code,"</a>"),
CONCAT(sup.code,":",sup.name),
po_deliveried,DATE_ADD(po_deliveried, INTERVAL po_payment DAY) AS due,
IF(po_paid IS NOT NULL,
    CONCAT("<a href='' title='ชำระเงิน' class='edit-po-paid icon-page-edit' poid='",po_id,"' ttable='pap_mat_po' info='",po_paid,",",po_paid_ref,"'></a>",po_paid_ref),
    CONCAT("<a href='' title='ชำระเงิน' class='po-paid icon-plus-square' poid='",po_id,"' ttable='pap_mat_po'></a>")
)
FROM pap_process_po AS mat
LEFT JOIN pap_supplier AS sup ON sup.id=mat.supplier_id $filter
ORDER BY due ASC
$lim_sql
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            if(isset($perpage)){
                $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
                $stmt->bindParam(":off",$off,PDO::PARAM_INT);
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($res as $k=>$v){
                $res[$k]['po_deliveried'] = thai_date($v['po_deliveried'], true);
                $res[$k]['due'] = thai_date($v['due'], true);
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}
