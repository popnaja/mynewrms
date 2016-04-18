<?php
session_start();
include_once("myfunction.php");
if(!$_POST){
    header("location:".ROOTS);
    exit();
}
$req = filter_input(INPUT_POST,'request',FILTER_SANITIZE_STRING);
if($req == "add_msg"){
    //post contact
    $ct = new myContact();
    date_default_timezone_set("Asia/Bangkok");
    $added = date("Y-m-d H:i:s");
    $id = $ct->add_contact($_POST['name'], "", $_POST['tel'], $_POST['msg'], $added);
    //alert email to admin
    include_once("email_function.php");
    //send email to admin
    //admin_alert_email("",$_POST['name'],$_POST['tel'], $_POST['msg']);
    alert_w_netdesign("", $_POST['name'], $_POST['tel'], $_POST['msg']);
    //show msg
    echo json_encode(array("myOK","Message","Thank you, we will reply back ASAP."));
}

class myContact{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_RMS);
    }
    public function add_contact($name,$email,$tel,$msg,$added){
        try {
            $stmt = $this->conn->prepare("INSERT INTO contact VALUES (null,:name,:email,:tel,:msg,:added)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":email",$email);
            $stmt->bindParam(":tel",$tel);
            $stmt->bindParam(":msg",$msg);
            $stmt->bindParam(":added",$added);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error("add_contact",$ex);
        }
    }
}