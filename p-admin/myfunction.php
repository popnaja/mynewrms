<?php
include_once("p-config.php");
define("ADMIN_PATH",dirname(__FILE__));
function __autoload($class_name) {
    include_once ('class.'.$class_name .'.php');
}
function __autoloada($class_name) {
    include_once(ADMIN_PATH."/class.".$class_name.".php");
}
function dbConnect($dbname) {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".$dbname,DB_USER,DB_PASSWORD);
    //set pdo error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $sql = "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'";
    $conn->exec($sql);
    return $conn;
}
function db_error($func,$ex){
    echo "ERROR: $func ". $ex->getMessage();
}
function pap_now(){
    return date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d H:i:s");
}
function pap_nmonth(){
    return date_format(date_create(null,timezone_open("Asia/Bangkok")),"Ym");
}
function showmsg(){
    if(isset($_SESSION['message'])){
        $html = newstatus($_SESSION['message'],true);
        unset($_SESSION['message']);
    } else if (isset($_SESSION['error'])){
        $html = newstatus($_SESSION['error'],false);
        unset($_SESSION['error']);
    } else {
        $html = "";
    }
    return $html;
}
function check_auth($pauth){
    $_SESSION['referurl'] = current_url();
    if(!isset($_SESSION['upap'])||$pauth<1){
        header("location:".PAP."login.php");
        exit;
    }
}
function page_auth($file){
    $res = 0;
    if(isset($_SESSION['upap'][1])){
        $auth = $_SESSION['upap'][1];
        //var_dump($auth);
        if(is_numeric(strpos($file,"?"))){
            foreach($auth as $k=>$v){
                if(is_numeric(strpos($file,$k))){
                    $res = $v;
                }
            }
        } else {
            $res = (isset($auth[$file])?$auth[$file]:0);
        }
    }
    return $res;
}
function showstatus($message=null,$ok=1){
    if($message != ""){
        $class = ($ok==1?"ok":"ng");
        $html = "<div id='ez-msg-wrap'><div id='ez-message' class='$class'>\n"
                . "<button id='closemsg' type='button' class='icon-remove'>\n"
                . "</button>"
                . "<p>$message</p>\n"
                . "</div>\n"
                . "<script>\n"
                . "$(document).ready(function(){"
                . "$('#closemsg').click(function(){"
                . "$('#ez-msg-wrap').remove();"
                . "});"
                . "});"
                . "</script>\n"
                . "</div>\n";
        if(isset($_SESSION['message'])){
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
    } else {
        return "";
    }
    return $html;
}
function newstatus($message=null,$ok=true){
    if($message != ""){
        $class = ($ok?"ok-msg":"ng-msg");
        $icon = ($ok?"icon-check-mark-circle":"icon-alert");
        $html = "<div id='pg-msg-wrap'><div id='pg-message' class='$class'>"
                . "<span class='icon-remove close-msg'></span>"
                . "<span class='pg-msg-icon $icon'></span>"
                . "<p>$message</p>\n"
                . "</div>\n"
                . "<script>\n"
                . "$(document).ready(function(){"
                . "$('.close-msg').click(function(){"
                . "$(this).parent().parent().remove();"
                . "});"
                . "});"
                . "</script>\n"
                . "</div>\n";
        if(isset($_SESSION['message'])){
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
    } else {
        return "";
    }
    return $html;
}
function is_thai($string){
    preg_match("/[ก-ฮ]+/",$string,$matches);
    if(sizeof($matches,0)>0){
        return true;
    } else {
        return false;
    }
}
/*+1 to file's name if file alerady exists */
function check_exist($target){
    $ext = ".".pathinfo($target, PATHINFO_EXTENSION);
    if(file_exists($target)){
        $i=0;
        do {
            $i++;
            $ntarget = str_replace($ext,"-".$i.$ext,$target);
        } while (file_exists($ntarget));
        return $ntarget;
    } else {
        return $target;
    }
}
function name_to_url($name,$pid=null){
    function toLower($matches){
        return strtolower($matches[0]);
    }
    $rep = "/^\\s+|\\s+$/"; //clear space,tab before and after
    $rep1 = "/(\\.)+|( )+/"; //replace dot and space with -
    $rep2 = "/[!@#$%^&*()+=\\|\\/\\[\\]\\{\\};:'\",<>\\?\\\]/"; //replace special char with blank
    $rep3 = "/[A-Z]{1}/"; //replace capital with lowercase
    $nname = preg_replace($rep,"",$name);
    $nname = preg_replace($rep1,"-",$nname);
    $nname = preg_replace($rep2,"",$nname);
    $nname = preg_replace_callback($rep3,"toLower",$nname);
    __autoload("pdo_get");
    $gpdo = new pdo_get();
    $n = 2;
    while(!$gpdo->check_post_val("post_slug",$nname,$pid)){
        $nname = $nname."-".$n;
        $n++;
    }
    return $nname;
}
function ifnull($obj,$return=""){
    return (isset($obj)?$obj:$return);
}
function gen_sql($arr,$connect,$n){
    $i=0;
    $res = "";
    $narr = array();
    foreach($arr AS $k=>$v){
        $res .= ($i==0?"":$connect);
        $res .= "$k=:$n$i";
        $narr += [$n.$i=>$v];
        $i++;
    }
    return [$res,$narr];
}
function current_url(){
    return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
function thai_date($fulldate,$short=false){
    $thday = array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");
    $thmonth = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    $thm = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $date = date_create($fulldate,timezone_open("Asia/Bangkok"));
    $day = date_format($date,"j");
    $mm = date_format($date,"n")-1;
    $year = date_format($date,"Y")+543;
    if($short){
        return "$day ".$thm[$mm]." ".substr($year,2);
    } else {
        return "$day ".$thmonth[$mm]." $year";
    }
}
function thai_dt($fulldate,$showy=false){
    $thm = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $date = date_create($fulldate,timezone_open("Asia/Bangkok"));
    $day = date_format($date,"j");
    $mm = date_format($date,"n")-1;
    $year = date_format($date,"Y")+543;
    $sy = ($showy?substr($year,2):"");
    $time = date_format($date,"H:i");
    return "$day ".$thm[$mm]." $sy ($time)";
}
function my_legend($st,$icon){
    $x = 0;
    $html = "";
    foreach($st as $k=>$v){
        $html .= ($x==0?"":" , ")
                . $icon[$k]
                . "<span class='def'> = "
                . "$v</span>";
        $x++;
    }
    return $html;
}
function time_hour(){
    $hour = array();
    for($i=0;$i<24;$i++){
        $key = sprintf("%02d",$i);
        $hour[$key] = $key;
    }
    return $hour;
}
function time_min(){
    $hour = array();
    for($i=0;$i<60;$i++){
        $key = sprintf("%02d",$i);
        $hour[$key] = $key;
    }
    return $hour;
}