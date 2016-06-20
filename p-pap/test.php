<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
include_once(dirname(dirname(__FILE__))."/p-admin/email_function.php");

__autoloada("table");
__autoload("pdo_tb");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$tb = new mytable();
$tbpdo = new tbPDO();
/* update process meta*/
/*
$pid = $db->get_keypair("pap_process", "process_id", "process_unit");
foreach($pid as $k=>$v){
    $meta = $db->get_meta("pap_process_meta", "process_id", $k);
    $cost = json_decode($meta['cost'],true);
    $arrcost = array();
    foreach($cost as $key=>$val){
        $val['vunit'] = $v;
        array_push($arrcost,$val);
    }
    //var_dump($arrcost);
    $db->update_meta("pap_process_meta", "process_id", $k, array("cost"=>json_encode($arrcost)));
    //$db->update_meta("pap_process_meta", "process_id", $v, array("pc_show"=>1));
    //echo $v."<br/>";
}
 * 
 */


/* update customer meta
$cus = $db->get_keypair("pap_customer", "customer_id", "customer_code");
foreach($cus AS $k=>$v){
    $db->update_meta("pap_customer_meta", "customer_id", $k, array("c_branch"=>"สำนักงานใหญ๋"));
}
 * 
 */
/*โจทย์ => มีเครื่องจักรทั้งหมด 5 เครื่อง(A,B,C,D,E) เวลาทำงาน 8 ชั่วโมงต่อวัน
 * มีงานเข้ามาจำนวน 20 งาน จาก array $job โดย id คือรหัสงาน time คือเวลาที่ใช้
 * ตัวอย่าง 
 * array (size=20)
  0 => 
    array (size=2)
      'id' => int 1
      'time' => float 2.5
  1 => 
    array (size=2)
      'id' => int 2
      'time' => float 2.5
  2 => 
    array (size=2)
      'id' => int 3
      'time' => float 2
 * ...
 * 
 * ให้เขียน function เพื่อแบ่งงานลงเครื่องจักรทั้ง 5 เครื่อง โดยคำนึงถึงลำดับง่านก่อนหลังตาม id
 * ให้ผลลัพท์อยู่ในรูป เครื่องจักร = array(jobid=>time)
 * เช่น A = array(1=>1.5,4=>3,5=>2)
 */




function random_job(){
    $res = array();
    for($i=0;$i<20;$i++){
        $time = mt_rand(1,8)*0.5;
        array_push($res,array("id"=>$i+1,"time"=>$time));
    }
    return $res;
}
$job = random_job();

function allocate_job($job){
    /*ฟังชั่นแบงงาน*/
    
    
    
}







/*gamma weight random more gamma give lower result*/
function weightedrand($min, $max, $gamma) {
    $offset= $max-$min+1;
    return floor($min+pow(lcg_value(), $gamma)*$offset);
}
/*weight random function input array(res=>weight) ex array("A"=>20,"B"=>80)*/
function weightedrand2(array $weightedValues) {
    $rand = mt_rand(1, (int) array_sum($weightedValues));
    foreach ($weightedValues as $key => $value) {
        $rand -= $value;
        if ($rand <= 0) {
          return $key;
        }
    }
}
