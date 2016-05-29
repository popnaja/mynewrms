<?php
class reportPDO{
    private $conn;
    private $months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    public function __construct() {
        $this->conn = dbConnect(DB_PAP);
    }
    public function report_quote($cid,$op,$month){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
status,COUNT(quote_id),FORMAT(SUM(q_price),0)
FROM pap_quotation
WHERE customer_id=:cid AND DATE_FORMAT(created,"%Y%m")=:month
GROUP BY status ORDER BY status
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->bindParam(":month",$month);
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
    public function rp_monthly_order($cid,$year){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
DATE_FORMAT(od.created,"%b") AS month,SUM(q_price) AS amount
FROM pap_order AS od
LEFT JOIN pap_quotation AS pq ON pq.quote_id=od.quote_id
WHERE customer_id=:cid AND DATE_FORMAT(od.created,"%Y")=:year
GROUP BY DATE_FORMAT(od.created,"%Y%m") ORDER BY DATE_FORMAT(od.created,"%Y%m") ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->bindParam(":year",$year);
            $stmt->execute();
            $res = array(array("Month","Sales"));
            $order = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            //var_dump($order);
            foreach($this->months AS $value){
                if(isset($order[$value])){
                    array_push($res,array($value,(int)$order[$value]));
                } else {
                    array_push($res,array($value,0));
                }
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_order($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
order_id,po.quote_id,order_no,pq.name,pq.plan_delivery,plate_plan,plate_received,paper_plan,paper_received,picture,remark,
op.op_name AS cat,pq.amount,po.quote_id AS qid,
meta.meta_value AS pages,pq.plan_delivery,
pro.process_name AS bind,CONCAT(size_height,'x',size_width,' cm') AS size,pq.credit
FROM pap_order AS po
LEFT JOIN pap_quotation AS pq ON pq.quote_id=po.quote_id
LEFT JOIN pap_option AS op ON op.op_id=pq.cat_id
LEFT JOIN pap_quote_meta AS meta ON meta.quote_id=po.quote_id AND meta.meta_key='page_inside'
LEFT JOIN pap_size ON size_id=job_size_id
LEFT JOIN pap_process AS pro ON pro.process_id=binding_id
WHERE order_id=:oid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_order_comp($oid){
        try {
            $sql = <<<END_OF_TEXT
SELECT
id,type,paper_id,page,mat_name,paper_cut,paper_use AS rim,name,paper_lay,print_size,
allowance,mat_order_lot_size AS lot
FROM pap_order_comp
LEFT JOIN pap_mat AS mat ON mat_id=paper_id
WHERE order_id=:oid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":oid",$oid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_order_cpro($cid,$processcat){
        try {
            $sql = <<<END_OF_TEXT
SELECT
pro.process_cat_id,
GROUP_CONCAT(cpro.name SEPARATOR ';')
FROM pap_comp_process AS cpro
LEFT JOIn pap_process AS pro ON pro.process_id=cpro.process_id
WHERE comp_id=:cid AND pro.process_cat_id IN $processcat
GROUP by pro.process_cat_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_mat_po($poid){
        try{
            $sql = <<<END_OF_TEXT
SELECT
IF(dt.order_ref=0,
	mat.mat_name,
    CONCAT(mat.mat_name,"<br/>(",od.order_no," : ",quo.name,")")
),
mat_qty,mat_cost
FROM pap_mat_po_detail AS dt
LEFT JOIN pap_mat AS mat ON mat.mat_id=dt.mat_id
LEFT JOIN pap_order AS od ON od.order_id=dt.order_ref
LEFT JOIN pap_quotation AS quo ON quo.quote_id=od.quote_id
WHERE dt.po_id=:poid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_NUM);
            $i = 1;
            foreach($res as $k=>$v){
                array_push($res[$k],$v[1]*$v[2]);
                array_unshift($res[$k],$i);
                $i++;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_process_po($poid){
        try{
            $sql = <<<END_OF_TEXT
SELECT
CONCAT(comp.name,":",cpro.name," (หน่วย ",unit,")","<br/>(",od.order_no," : ",quo.name,")"),qty,cost_per_u
FROM pap_pro_po_dt AS dt
LEFT JOIN pap_comp_process AS cpro ON cpro.id=dt.cpro_id
LEFT JOIN pap_order_comp AS comp ON comp.id=cpro.comp_id
LEFT JOIN pap_order AS od ON od.order_id=comp.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=od.quote_id
WHERE dt.po_id=:poid
ORDER BY cpro.id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":poid",$poid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_NUM);
            $i = 1;
            foreach($res as $k=>$v){
                array_push($res[$k],$v[1]*$v[2]);
                array_unshift($res[$k],$i);
                $i++;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_deli_info($did){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
deli.*,ct.customer_id,user.user_login,
GROUP_CONCAT(dt.order_id) AS aoid,
GROUP_CONCAT(dt.credit) AS credit,
GROUP_CONCAT(dt.customer_id) AS acid
FROM pap_delivery AS deli
LEFT JOIN pap_contact AS ct ON ct.contact_id=deli.contact
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=ct.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
LEFT JOIN pap_delivery_dt AS dt ON dt.deli_id=deli.id
WHERE deli.id=:did
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":did",$did);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_tdeli_info($tdid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
deli.*,ct.customer_id,user.user_login,
GROUP_CONCAT(dt.order_id) AS aoid,
GROUP_CONCAT(ddt.credit) AS credit,
GROUP_CONCAT(ddt.customer_id) AS acid
FROM pap_temp_deli AS deli
LEFT JOIN pap_contact AS ct ON ct.contact_id=deli.contact
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=ct.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
LEFT JOIN pap_temp_dt AS dt ON dt.temp_deli_id=deli.id
LEFT JOIN pap_delivery_dt AS ddt ON ddt.job_name=dt.job_name OR ddt.order_id=dt.order_id
WHERE deli.id=:tdid
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":tdid",$tdid);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_deli_dt($did){
        try{
            $sql = <<<END_OF_TEXT
SELECT
IFNULL(quote_id,CONCAT(job_name,";",id)),qty,price/qty,discount
FROM pap_delivery_dt AS dt
LEFT JOIN pap_order AS job ON job.order_id=dt.order_id
WHERE deli_id=:did
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":did",$did);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_NUM);
            $i = 1;
            $discount = 0;
            foreach($res as $k=>$v){
                //sum discount
                $discount += $v[3];
                unset($res[$k][3]);
                array_push($res[$k],$v[1]*$v[2]);
                array_unshift($res[$k],$i);
                $i++;
            }
            return array($res,$discount);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_tdeli_dt($tdid){
        try{
            $sql = <<<END_OF_TEXT
SELECT
IF(dt.order_id=0,dt.job_name,CONCAT(job.order_no," : ",quo.name)),dt.qty,0
FROM pap_temp_dt AS dt
LEFT JOIN pap_order AS job ON job.order_id=dt.order_id
LEFT JOIN pap_quotation AS quo ON quo.quote_id=job.quote_id
WHERE dt.temp_deli_id=:tdid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":tdid",$tdid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_NUM);
            $i = 1;

            foreach($res as $k=>$v){
                array_push($res[$k],$v[1]*$v[2]);
                array_unshift($res[$k],$i);
                $i++;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_pbill_info($bid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
pb.*,ct.customer_id,user.user_login,GROUP_CONCAT(ddt.customer_id) AS acid
FROM pap_pbill AS pb
LEFT JOIN pap_contact AS ct ON ct.contact_id=pb.contact
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=ct.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
LEFT JOIN pap_pbill_dt AS bdt ON bdt.pbill_id=pb.id
LEFT JOIN pap_delivery_dt AS ddt ON ddt.deli_id=bdt.deli_id
WHERE pb.id=:bid
GROUP BY pb.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":bid",$bid);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_pbill_dt($bid,$op){
        try{
            $sql = <<<END_OF_TEXT
SELECT
deli.no,
bdt.amount,
deli.date,
GROUP_CONCAT(ddt.job_name) AS jname,
GROUP_CONCAT(ddt.qty) AS qty,
GROUP_CONCAT(ddt.type) AS type,
SUM((ddt.price-ddt.discount)*IF(meta.meta_value='no',1.07,1)) AS price,
MIN(DATE_ADD(deli.date, INTERVAL ddt.credit DAY)) AS due
FROM pap_pbill_dt AS bdt
LEFT JOIN pap_delivery_dt AS ddt ON ddt.deli_id=bdt.deli_id
LEFT JOIN pap_delivery AS deli ON deli.id=bdt.deli_id
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=ddt.customer_id AND meta.meta_key='tax_exclude'
WHERE bdt.pbill_id=:bid
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":bid",$bid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
            foreach($res as $k=>$v){
                $jname = explode(",",$v['jname']);
                $qty = explode(",",$v['qty']);
                $type = explode(",",$v['type']);
                $date = thai_date($v['date'],true);
                $due = thai_date($v['due'], true);
                $job = "<ul class='job-list'>";
                for($j=0;$j<count($jname);$j++){
                    $unit = $op[$type[$j]];
                    $job .= "<li>$jname[$j] จำนวน $qty[$j] $unit</li>";
                }
                $job .= "</ul>";
                $res1[$k] = array($i,"<p>ค่าบริการงานพิมพ์ ตามใบแจ้งหนี้ ".$v['no']." : </p>$job",$date,$due,$v['amount']);
                $i++;
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_invoice_info($ivid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
iv.*,user_login,discount,
GROUP_CONCAT(dt.deli_id) AS adid,
GROUP_CONCAT(dt.amount) AS aamount       
FROM pap_invoice AS iv
LEFT JOIN pap_invoice_dt AS dt ON dt.invoice_id=iv.id
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=iv.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
WHERE iv.id=:ivid
GROUP BY iv.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":ivid",$ivid);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_invoice_dt($ivid,$op){
        try{
            $sql = <<<END_OF_TEXT
SELECT
deli.no,
ivdt.amount,
GROUP_CONCAT(ddt.job_name) AS jname,
GROUP_CONCAT(ddt.qty) AS qty,
GROUP_CONCAT(ddt.type) AS type,
deli.total AS price
FROM pap_invoice_dt AS ivdt
LEFT JOIN pap_delivery_dt AS ddt ON ddt.deli_id=ivdt.deli_id
LEFT JOIN pap_delivery AS deli ON deli.id=ivdt.deli_id
WHERE ivdt.invoice_id=:ivid
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":ivid",$ivid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
            $res1 = array();
            foreach($res as $k=>$v){
                $jname = explode(",",$v['jname']);
                $qty = explode(",",$v['qty']);
                $type = explode(",",$v['type']);
                array_push($res1,array($i,"<p>ค่าบริการงานพิมพ์ ตามใบแจ้งหนี้ ".$v['no']." : </p>",1,$v['amount'],$v['amount']));

                for($j=0;$j<count($jname);$j++){
                    $unit = $op[$type[$j]];
                    $amount = number_format($qty[$j],0);
                    $job = "<p class='job-list'>$jname[$j] จำนวน $amount $unit</p>";
                    array_push($res1,array("",$job,"","",""));
                }
                $i++;
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_receipt_info($rcid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
rc.*,iv.customer_id,user_login,meta_value AS tax_exclude,iv.no AS invoiceno,iv.id AS ivid,
GROUP_CONCAT(ivdt.deli_id) AS adeli
FROM pap_rc AS rc
LEFT JOIN pap_rc_dt AS rcdt ON rcdt.rc_id=rc.id
LEFT JOIN pap_invoice AS iv ON iv.id=rcdt.invoice_id
LEFT JOIN pap_invoice_dt AS ivdt ON ivdt.invoice_id=iv.id
LEFT JOIN pap_sale_cus AS sale ON sale.cus_id=iv.customer_id
LEFT JOIN pap_user AS user ON user.user_id=sale.user_id
LEFT JOIN pap_customer_meta AS meta ON meta.customer_id=iv.customer_id AND meta_key='tax_exclude'
WHERE rc.id=:rcid
GROUP BY rc.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":rcid",$rcid);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function rp_receipt_dt($rcid,$op){
        try{
            $sql = <<<END_OF_TEXT
SELECT
deli.no,
rcdt.amount AS paid,
GROUP_CONCAT(ddt.job_name) AS jname,
GROUP_CONCAT(ddt.qty) AS qty,
GROUP_CONCAT(ddt.type) AS type,
deli.total AS price
FROM pap_rc_dt AS rcdt
LEFT JOIN pap_invoice AS iv ON iv.id=rcdt.invoice_id
LEFT JOIN pap_invoice_dt AS ivdt ON ivdt.invoice_id=rcdt.invoice_id
LEFT JOIN pap_delivery_dt AS ddt ON ddt.deli_id=ivdt.deli_id
LEFT JOIN pap_delivery AS deli ON deli.id=ivdt.deli_id
WHERE rcdt.rc_id=:rcid
GROUP BY deli.id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":rcid",$rcid);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
            foreach($res as $k=>$v){
                $jname = explode(",",$v['jname']);
                $qty = explode(",",$v['qty']);
                $type = explode(",",$v['type']);
                $job = "<ul class='list-inlist'>";
                for($j=0;$j<count($jname);$j++){
                    $unit = $op[$type[$j]];
                    $job .= "<li>$jname[$j] จำนวน $qty[$j] $unit</li>";
                }
                $job .= "</ul>";
                $paid = $v['paid'];
                $percent = round($paid*100/$v['price'],2);
                $per = ($percent==100?"":"(".number_format($percent,2)."%)");
                $res1[$k] = array($i,"<p>ค่าบริการงานพิมพ์ ตามใบแจ้งหนี้ ".$v['no']." : </p>$job",1,$v['paid'],$v['paid']);
                $i++;
            }
            return $res1;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}