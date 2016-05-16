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