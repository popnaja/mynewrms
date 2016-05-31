<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
__autoload("pappdo");
__autoloada("form");
$form = new myform();
if(!$_POST){
    header("location:".ROOTS);
}
$req = filter_input(INPUT_POST,'request',FILTER_SANITIZE_STRING);
$db = new PAPdb(DB_PAP);
if($req == "login"){
    //check username and password
    $uid = $db->check_user($_POST['login'], md5($_POST['z']));
    if($uid==false){
        $_SESSION['error'] = "Username or Password is incorrected.";
        header("location:".$_POST['error_direct']);
    } else {
        $umeta = $db->get_meta("pap_usermeta","user_id",$uid);
        /*
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d");
        if($umeta['user_expired']<$today){
            $_SESSION['error'] = "รหัสผ่านหมดอายุ โปรดติดต่อเจ้าหน้าที่ 061-864-8641";
            header("location:".$_POST['error_direct']);
        } else */
        if(isset($umeta['l_status'])&&$umeta['l_status']!=""){
            $now = date_create(null,timezone_open("Asia/Bangkok"));
            $last = date_create($umeta['l_status'],timezone_open("Asia/Bangkok"));
            $interval = $now->format("U")-$last->format("U");
            if($interval/60<3){
                $_SESSION['error'] = "มีผู้ใช้งานบัญชีนี้อยู่ กรุณาลองใหม่อีกครั้งหลังจาก 3 นาที";
                header("location:".$_POST['error_direct']);
            } else {
                $auth = json_decode($db->get_info("pap_option", "op_id", $umeta['user_auth'])["op_value"],true);
                $_SESSION['upap'] = [$uid,$auth];
                header("location:".$_POST['redirect']);
            }
        } else {
            $auth = json_decode($db->get_info("pap_option", "op_id", $umeta['user_auth'])["op_value"],true);
            $_SESSION['upap'] = [$uid,$auth];
            header("location:".PAP);
        }
    }
} else if($req == "edit_upass"){
    $arrinfo = array(
        "pass" => md5($_POST['repass'])
    );
    $db->update_data("pap_user", "user_id", $_POST['uid'], $arrinfo);
    $_SESSION['message'] = "แก้ไขรหัสผ่านสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "update_u_log"){
    $meta = array(
        "l_status"=>  pap_now()
    );
    $db->update_meta("pap_usermeta","user_id",$_POST['uid'], $meta);
    echo json_encode("");
} else if($req == "pass-reset"){
    include_once(dirname(dirname(__FILE__))."/p-admin/email_function.php");
    $email = strtolower($_POST['email']);
    $np = $db->get_repass($email);
    if(!is_string($np)){
        $_SESSION['error'] = "อีเมลไม่ถูกต้อง";
        header("Location:".$_POST['error_direct']."?f");
    } else {
        $rurl = $_POST['error_direct']."?r=".$np;
        if(php_mailer_ndh(array($email),"contact@resolutems.com","เปลี่ยนรหัสผ่าน resolutems.com",email_ct($rurl))){
            $_SESSION['message'] = "ส่งอีเมลขอเปลียนรหัสผ่านเรียบร้อยแล้ว โปรดเช็คอีเมลของคุณ";
            header("Location:".$_POST['error_direct']);
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            header("Location:".$_POST['error_direct']."?f");
        }
    }
} else if($req == "add_urole"){
    //check name
    if($db->check_optdup("role_auth",$_POST['name'])){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']);
    } else {
        //insert
        $auth = json_encode(array_combine($_POST['page'],$_POST['auth']));
        $roleid = $db->insert_data("pap_option", array(null,"role_auth",$_POST['name'],$auth));
        $_SESSION['message'] = "เพิ่มกลุ่มผู้ใช้สำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_urole"){
    $opid = filter_input(INPUT_POST,'opid',FILTER_SANITIZE_NUMBER_INT);
    //check name
    if($db->check_optdup("role_auth",$_POST['name'],$opid)){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?opid=$opid");
    } else {
        //update
        $auth = json_encode(array_combine($_POST['page'],$_POST['auth']));
        $arrinfo = array(
            "op_name" => $_POST['name'],
            "op_value" => $auth
        );
        $db->update_data("pap_option", "op_id", $opid, $arrinfo);
        $_SESSION['message'] = "แก้ไขกลุ่มผู้ใช้สำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "add_user"){
    //check name
    if($db->check_dup("pap_user", "user_login", $_POST['uname'])){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else if($db->check_dup("pap_user","user_email",$_POST['email'])){
        $_SESSION['error'] = "Email ซ้ำโปรดลองใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else {
        //insert
        $uid = $db->insert_data("pap_user", array(null,$_POST['uname'],$_POST['email'],md5($_POST['repass']),pap_now()));
        //move signature
        if(isset($_POST['media'])){
            __autoloada("media");
            $md = new mymedia();
            $file = $_POST['media'];
            $des = dirname(__FILE__)."/image/user/sig_$uid".".".pathinfo($file,PATHINFO_EXTENSION);
            $md->move_file(RDIR.$file, $des);
            $signature = "/p-pap/image/user/sig_$uid".".".pathinfo($file,PATHINFO_EXTENSION);
        } else {
            $signature = "";
        }
        $meta = array(
            "user_auth" => $_POST['urole'],
            "signature" => $signature
        );
        $db->update_meta("pap_usermeta", "user_id", $uid, $meta);
        $_SESSION['message'] = "เพิ่มผู้ใช้สำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_user"){
    //check name
    $uid = filter_input(INPUT_POST,"uid",FILTER_SANITIZE_NUMBER_INT);
    if($db->check_dup("pap_user", "user_login", $_POST['uname'],"user_id<>$uid")){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?uid=$uid");
    } else if($db->check_dup("pap_user","user_email",$_POST['email'],"user_id<>$uid")){
        $_SESSION['error'] = "Email ซ้ำโปรดลองใหม่";
        header("Location:".$_POST['redirect']."?uid=$uid");
    } else {
        //update
        $arrinfo = array(
            "user_login" => $_POST['uname'],
            "user_email" => $_POST['email']
        );
        //uppass
        if($_POST['repass']<>""){
            $arrinfo['user_pass'] = md5($_POST['repass']);
        }
        $db->update_data("pap_user", "user_id", $uid, $arrinfo);
        //user auth
        if(isset($_POST['urole'])){
            $meta['user_auth'] = $_POST['urole'];
        }
        //user signature
        if(isset($_POST['media'])){
            if($_POST['ori_media']!=$_POST['media']){
                __autoloada("media");
                $md = new mymedia();
                $file = $_POST['media'];
                $des = dirname(__FILE__)."/image/user/sig_$uid".".".pathinfo($file,PATHINFO_EXTENSION);
                $md->move_file(RDIR.$file, $des);
                $meta['signature'] = "/p-pap/image/user/sig_$uid".".".pathinfo($file,PATHINFO_EXTENSION);
            }
        } else {
            $meta['signature'] = "";
        }

        $db->update_meta("pap_usermeta", "user_id", $uid, $meta);
        $_SESSION['message'] = "แก้ไขผู้ใช้สำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "update_setting"){
    //prep paper allowance
    $pallow = array();
    for($i=0;$i<count($_POST['pallo']);$i++){
        if($_POST['pallo'][$i]>0){
            array_push($pallow,array(
                "print" => $_POST['print'][$i],
                "from" => $_POST['from'][$i],
                "to" => $_POST['to'][$i],
                "pallo" => $_POST['pallo'][$i],
                "unit" => $_POST['unit'][$i]
                )
            );
        }
    }
    $arrinfo = array(
        "name" => $_POST['name'],
        "address" => $_POST['address'],
        "email" => $_POST['email'],
        "tel" => $_POST['tel'],
        "fax" => $_POST['fax'],
        "tax_id" => $_POST['tax_id'],
        "c_digit" => $_POST['cdigit'],
        "s_digit" => $_POST['s_digit'],
        "rno_quote" => $_POST['rno_quote'],
        "rno_order" => $_POST['rno_order'],
        "rno_matpo" => $_POST['rno_matpo'],
        "rno_prodpo" => $_POST['rno_prodpo'],
        "rno_deli" => $_POST['rno_deli'],
        "rno_bill" => $_POST['rno_bill'],
        "rno_rc" => $_POST['rno_rc'],
        "rno_invoice" => $_POST['rno_invoice'],
        "grip_size" => $_POST['grip'],
        "bleed_size" => $_POST['bleed'],
        "margin" => $_POST['margin'],
        "paper_allo" => json_encode($pallow)
    );
    //move logo
    if(isset($_POST['media'])){
        $file = $_POST['media'];
        if($_POST['ori_media']!=$file){
            __autoloada("media");
            $md = new mymedia();
            $des = dirname(__FILE__)."/image/company_logo".".".pathinfo($file,PATHINFO_EXTENSION);
            $md->move_file(RDIR.$file, $des);
            $arrinfo['c_logo'] = "/p-pap/image/company_logo".".".pathinfo($file,PATHINFO_EXTENSION);
        }
    }
    $db->update_option("cinfo", $arrinfo);
    $_SESSION['message'] = "ปรับปรุงข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_process_cat"){
    $db->insert_data("pap_process_cat", array(null,$_POST['name']));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "edit_process_cat"){
    $db->update_data("pap_process_cat", "id", $_POST['pcid'], array("name"=>$_POST["name"]));
    $_SESSION['message'] = "ปรับปรุงข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_process"){
    //check name
    if($db->check_dup("pap_process", "process_name", $_POST['name'])){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else {
        //insert
        $pid = $db->insert_data("pap_process", array(null,$_POST['name'],$_POST['cat'],$_POST['unit'],$_POST['source'],$_POST['setup_min'],$_POST['capacity'],$_POST['std_lt']));
        //update meta
        $n = count($_POST['cost']);
        $arrcost = array();
        for($i=0;$i<$n;$i++){
            if($_POST['cost'][$i]>0||$_POST['fcost'][$i]>0||$_POST['min'][$i]>0){
                $cost_ele = array(
                    "fcost" => $_POST['fcost'][$i],
                    "vunit" => $_POST['vunit'][$i],
                    "cost" => $_POST['cost'][$i],
                    "min" => $_POST['min'][$i],
                    "formular" => $_POST['for'][$i],
                    "cond" => $_POST['cond'][$i],
                    "btw" => $_POST['btw'][$i],
                    "to" => $_POST['to'][$i]
                );
                array_push($arrcost,$cost_ele);
            }
        }
        /*
        $m = count($_POST['mat']);
        $arrmat = array();
        for($i=0;$i<$m;$i++){
            if($_POST['mat'][$i]>0){
                array_push($arrmat,array($_POST['mat'][$i],$_POST['usage'][$i]));
            }
        }
         * 
         */
        $meta = array(
            "cost" => json_encode($arrcost),
            //"detail_cost" => $_POST['usedetail'],
            //"detail_labor" => $_POST['labor'],
            //"detail_mat" =>  json_encode($arrmat),
            "pc_show" => $_POST['show']
        );
        $db->update_meta("pap_process_meta", "process_id", $pid, $meta);

        $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_process"){
    $pid = filter_input(INPUT_POST,"pid",FILTER_SANITIZE_NUMBER_INT);
    if($db->check_dup("pap_process", "process_name", $_POST['name'],"process_id<>$pid")){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?pid=$pid");
    } else {
        //update
        $source = filter_input(INPUT_POST,'source',FILTER_SANITIZE_NUMBER_INT);
        $arrinfo = array(
            "process_name" => $_POST['name'],
            "process_cat_id" => $_POST['cat'],
            "process_unit" => $_POST['unit'],
            "process_source" => $_POST['source'],
            "process_setup_min" => ($source==1?$_POST['setup_min']:0),
            "process_cap" => ($source==1?$_POST['capacity']:0),
            "process_std_leadtime_hour" => ($source==2?$_POST['std_lt']:0)
        );
        $db->update_data("pap_process", "process_id", $pid, $arrinfo);
        //update meta
        $n = count($_POST['cost']);
        $arrcost = array();
        for($i=0;$i<$n;$i++){
            if($_POST['cost'][$i]>0||$_POST['fcost'][$i]>0||$_POST['min'][$i]>0){
                $cost_ele = array(
                    "fcost" => $_POST['fcost'][$i],
                    "vunit" => $_POST['vunit'][$i],
                    "cost" => $_POST['cost'][$i],
                    "min" => $_POST['min'][$i],
                    "formular" => $_POST['for'][$i],
                    "cond" => $_POST['cond'][$i],
                    "btw" => $_POST['btw'][$i],
                    "to" => $_POST['to'][$i]
                );
                array_push($arrcost,$cost_ele);
            }
        }
        /*
        $m = count($_POST['mat']);
        $arrmat = array();
        for($i=0;$i<$m;$i++){
            if($_POST['mat'][$i]>0){
                array_push($arrmat,array($_POST['mat'][$i],$_POST['usage'][$i]));
            }
        }
         * 
         */
        $meta = array(
            "cost" => json_encode($arrcost),
            //"detail_cost" => $_POST['usedetail'],
            //"detail_labor" => $_POST['labor'],
            //"detail_mat" =>  json_encode($arrmat),
            "pc_show" => $_POST['show']
        );
        $db->update_meta("pap_process_meta", "process_id", $pid, $meta);

        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "add_process_set"){
    $db->insert_data("pap_process_cat", array(null,$_POST['name']));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_option"){
    //check name
    if($db->check_optdup($_POST['type'],$_POST['name'])){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']);
    } else {
        //insert
        if($_POST['type']=="paper_size"){
            $val = json_encode(array("width"=>$_POST['width'],"length"=>$_POST['length']));
        } else if($_POST['type']=="paper_allo"){
            $val = $_POST['from'].",".$_POST['to'];
        } else if($_POST['type']=="product_cat"){
            $proc = array_filter($_POST['proc'],function($val){
                return $val>0;
            });
            $val = implode(",",$proc);
        } else {
            $val = $_POST['value'];
        }
        $db->insert_data("pap_option", array(null,$_POST['type'],$_POST['name'],$val));
        $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_option"){
    $opid = filter_input(INPUT_POST,'opid',FILTER_SANITIZE_NUMBER_INT);
    //check name
    if($db->check_optdup($_POST['type'],$_POST['name'],$opid)){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."&opid=$opid");
    } else {
        //update
        if($_POST['type']=="paper_size"){
            $val = json_encode(array("width"=>$_POST['width'],"length"=>$_POST['length']));
        } else if($_POST['type']=="paper_allo"){
            $val = $_POST['from'].",".$_POST['to'];
        } else if($_POST['type']=="product_cat"){
            $proc = array_filter($_POST['proc'],function($val){
                return $val>0;
            });
            $val = implode(",",$proc);
        } else {
            $val = $_POST['value'];
        }
        $arrinfo = array(
            "op_name" => $_POST['name'],
            "op_value" => $val
        );
        $db->update_data("pap_option", "op_id", $opid, $arrinfo);
        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "add_job_size"){
    //check name
    if($db->check_dup("pap_size", "size_name", $_POST['name'])){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else {
        //insert
        $data = array(
            null,
            $_POST['name'],
            $_POST['height'],
            $_POST['width'],
            $_POST['cover_paper'],
            $_POST['cover_lay'],
            $_POST['inside_paper'],
            $_POST['inside_lay'],
            $_POST['cover_thick'],
            $_POST['cover_div'],
            $_POST['inside_div']
        );
        $sid = $db->insert_data("pap_size", $data);
        //custom lay
        $clay = array();
        for($i=0;$i<count($_POST['clay']);$i++){
            if($_POST['clay'][$i]>0){
                $clay[$i] = array($_POST['ctype'][$i],$_POST['cpaper'][$i],$_POST['cdiv'][$i],$_POST['clay'][$i]);
            }
        }
        //meta
        $meta = array(
            "grip1" => $_POST['grip1'],
            "grip2" => $_POST['grip2'],
            "custom_lay" => json_encode($clay)
        );
        
        $db->update_meta("pap_size_meta", "size_id", $sid, $meta);
        $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_job_size"){
    $sid = filter_input(INPUT_POST,"sid",FILTER_SANITIZE_NUMBER_INT);
    if($db->check_dup("pap_size", "size_name", $_POST['name'],"size_id<>$sid")){
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("Location:".$_POST['redirect']."?sid=$sid");
    } else {
        //update
        $arrinfo = array(
            "size_name" => $_POST['name'],
            "size_height" => $_POST['height'],
            "size_width" => $_POST['width'],
            "cover_paper" => $_POST['cover_paper'],
            "cover_lay" => $_POST['cover_lay'],
            "inside_paper" => $_POST['inside_paper'],
            "inside_lay" => $_POST['inside_lay'],
            "cover_thick" => $_POST['cover_thick'],
            "cover_div" => $_POST['cover_div'],
            "inside_div" => $_POST['inside_div']
        );
        $db->update_data("pap_size", "size_id", $sid, $arrinfo);
        //custom lay
        $clay = array();
        for($i=0;$i<count($_POST['clay']);$i++){
            if($_POST['clay'][$i]>0){
                $clay[$i] = array($_POST['ctype'][$i],$_POST['cpaper'][$i],$_POST['cdiv'][$i],$_POST['clay'][$i]);
            }
        }
        //meta
        $meta = array(
            "grip1" => $_POST['grip1'],
            "grip2" => $_POST['grip2'],
            "custom_lay" => json_encode($clay)
        );
        $db->update_meta("pap_size_meta", "size_id", $sid, $meta);
        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req =="add_customer"){
    $code = $db->check_cus_code($_POST['cat']);
    //insert
    $cid = $db->insert_data("pap_customer", array(null,$code,$_POST['name'],$_POST['taxid'],$_POST['address'],$_POST['url'],$_POST['email'],$_POST['tel'],$_POST['fax'],$_POST['pay'],$_POST['credit_day'],$_POST['credit'],$_POST['bill'],$_POST['cheque'],pap_now(),$_POST['status']));
    
    //meta
    $meta = array(
        "tax_exclude" => $_POST['ntax'],
        "c_branch" => $_POST['branch'],
        "remark" => $_POST['remark'],
        "bill_remark" => $_POST['bill_remark'],
        "cheque_remark" => $_POST['cheque_remark']
    );

    //image
    if(isset($_POST['media'])){
        __autoloada("media");
        $md = new mymedia();
        $pic = array();
        for($i=0;$i<count($_POST['media']);$i++){
            $file = $_POST['media'][$i];
            $fname = pathinfo($file,PATHINFO_BASENAME);
            $des = dirname(__FILE__)."/image/customer/$code-$fname";
            $md->move_file(RDIR.$file, $des);
            array_push($pic,"/p-pap/image/customer/$code-$fname");
        }
        $meta["picture"] = implode(",",$pic);
    } else {
        $meta['picture'] = "";
    }
    
    if($_POST['bill']==2){
        $meta['bill_day'] = $_POST['bill_day'];
    } else if($_POST['bill']==3){
        $meta['bill_week'] = $_POST['bill_week'];
        $meta['bill_weekday']  = $_POST['bill_weekday'];
    }
    if($_POST['cheque']==2){
        $meta['cheque_day'] = $_POST['cheque_day'];
    } else if($_POST['cheque']==3){
        $meta['cheque_week'] = $_POST['cheque_week'];
        $meta['cheque_weekday']  = $_POST['cheque_weekday'];
    }
    $db->update_meta("pap_customer_meta", "customer_id", $cid, $meta);

    //add cat
    $db->insert_data("pap_customer_cat", array($_POST['cat'],$cid));

    //add contact
    $n = count($_POST['cname']);
    for($i=0;$i<$n;$i++){
        if($_POST['cname'][$i]!=""){
            $db->insert_data("pap_contact",array(null,$cid,1,$_POST['cname'][$i],$_POST['cemail'][$i],$_POST['ctel'][$i],$_POST['cetc'][$i]));
        }
    }

    //add sale rep
    if(isset($_POST['sale'])&&$_POST['sale']>0){
        $db->insert_data("pap_sale_cus",array($_POST['sale'],$cid));
    }

    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_customer"){
    $cid = filter_input(INPUT_POST,"cid",FILTER_SANITIZE_NUMBER_INT);
    //update
    $arrinfo = array(
        "customer_name" => $_POST['name'],
        "customer_taxid" => $_POST['taxid'],
        "customer_status" => $_POST['status'],
        "customer_address" => $_POST['address'],
        "customer_url" => $_POST['url'],
        "customer_email" => $_POST['email'],
        "customer_tel" => $_POST['tel'],
        "customer_fax" => $_POST['fax'],
        "customer_pay" => $_POST['pay']
    );
    if($_POST['pay']==0){
        $arrinfo += array(
            "customer_credit_day" => 0,
            "customer_credit_amount" => 0,
            "customer_place_bill" => "",
            "customer_collect_cheque" => ""
        );
    } else {
        $arrinfo += array(
            "customer_credit_day" => $_POST['credit_day'],
            "customer_credit_amount" => $_POST['credit'],
            "customer_place_bill" => $_POST['bill'],
            "customer_collect_cheque" => $_POST['cheque']
        );
    }
    //update cat
    $db->delete_data("pap_customer_cat", "customer_id", $cid);
    $db->insert_data("pap_customer_cat", array($_POST['cat'],$cid));

    //meta
    $meta = array(
        "tax_exclude" => $_POST['ntax'],
        "c_branch" => $_POST['branch'],
        "remark" => $_POST['remark'],
        "bill_remark" => $_POST['bill_remark'],
        "cheque_remark" => $_POST['cheque_remark']
    );
    
    //image
    $ori_media = explode(",",$_POST['ori_media']);
    $code = $_POST['code'];
    if(isset($_POST['media'])){
        __autoloada("media");
        $md = new mymedia();
        $pic = array();
        for($i=0;$i<count($_POST['media']);$i++){
            $file = $_POST['media'][$i];
            if(!in_array($file,$ori_media)){
                $fname = pathinfo($file,PATHINFO_BASENAME);
                $des = dirname(__FILE__)."/image/customer/$code-$fname";
                $md->move_file(RDIR.$file, $des);
                array_push($pic,"/p-pap/image/customer/$code-$fname");
            } else {
                array_push($pic,$file);
            }
        }
        $meta["picture"] = implode(",",$pic);
    } else {
        $meta['picture'] = "";
    }
    
    if($_POST['bill']==2){
        $meta['bill_day'] = $_POST['bill_day'];
    } else if($_POST['bill']==3){
        $meta['bill_week'] = $_POST['bill_week'];
        $meta['bill_weekday']  = $_POST['bill_weekday'];
    }
    if($_POST['cheque']==2){
        $meta['cheque_day'] = $_POST['cheque_day'];
    } else if($_POST['cheque']==3){
        $meta['cheque_week'] = $_POST['cheque_week'];
        $meta['cheque_weekday']  = $_POST['cheque_weekday'];
    }
    $db->update_meta("pap_customer_meta", "customer_id", $cid, $meta);
    /*
    //update code
    if($_POST['ori_cat']<>$_POST['cat']){
        $arrinfo['customer_code'] = $db->check_cus_code($_POST['cat']);
    }
     *
     */

    //update sale rep
    if(isset($_POST['sale'])){
        if($_POST['sale']>0){
            $db->delete_data("pap_sale_cus", "cus_id", $cid);
            $db->insert_data("pap_sale_cus", array($_POST['sale'],$cid));
        } else {
            //del
            $db->delete_data("pap_sale_cus", "cus_id", $cid);
        }
    }
    $db->update_data("pap_customer", "customer_id", $cid, $arrinfo);
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_cus_ad"){
    if(isset($_POST['media'])){
        __autoloada("media");
        $md = new mymedia();
        $file = $_POST['media'];
        $des = dirname(__FILE__)."/image/customer/map/".pathinfo($file,PATHINFO_BASENAME);
        $ndes = check_exist($des);
        $md->move_file(RDIR.$file, $ndes);
        $map = "/p-pap/image/customer/map/".pathinfo($ndes,PATHINFO_BASENAME);
    } else {
        $map = "";
    }
    $db->insert_data("pap_cus_ad", array(null,$_POST['cid'],$_POST['name'],$_POST['address'],$map));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_cus_ad"){
    $arrinfo = array(
        "name"=>$_POST['name'],
        "address"=>$_POST['address']
    );
    if(isset($_POST['media'])){
        $file = $_POST['media'];
        if($_POST['ori_media']!=$file){
            __autoloada("media");
            $md = new mymedia();
            $des = dirname(__FILE__)."/image/customer/map/".pathinfo($file,PATHINFO_BASENAME);
            $ndes = check_exist($des);
            $md->move_file(RDIR.$file, $ndes);
            $arrinfo['map'] = "/p-pap/image/customer/map/".pathinfo($ndes,PATHINFO_BASENAME);
        }
    }
    $db->update_data("pap_cus_ad", "id", $_POST['adid'], $arrinfo);
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_quote"){
    $qno = $db->check_quote_no();
    $prepress = (isset($_POST['prepress'])?implode(",",$_POST['prepress']):"");
    $exclude = (isset($_POST['exclude'])?implode(",",$_POST['exclude']):"");
    $tc = $db->get_info("pap_customer","customer_id",$_POST['cid']);
    $credit = $tc['customer_credit_day'];
    $data = array(
        null,$qno,$_SESSION['upap'][0],
        $_POST['cid'], $_POST['type'],
        $_POST['name'], $_POST['sid'],
        $prepress,$_POST['binding'],
        $_POST['amount'],0,
        $credit, $_POST['due'],
        $_POST['status'], pap_now(),
        null, null);

    $qid = $db->insert_data("pap_quotation",$data);
    $n = count($_POST['page']);
    $page_cover = 0;
    $page_inside = 0;
    //cal allowance
    $adata = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='cinfo'");
    $ainfo = json_decode($adata['paper_allo'],true);
    $meta['cwing'] = 0;
    $coat2 = array();
    $coatpage = array();
    for($i=0;$i<$n;$i++){
        if($_POST['paper_size'][$i]>0&&$_POST['paper_type'][$i]>0&&$_POST['paper_gram'][$i]>0){
            $type = $_POST['comp_type'][$i];
            $print2 = $_POST['print2'][$i];
            if($type=="1"){ //ปก
                $page = $page_cover = 1;
                //ปกปีก
                $meta['cwing'] = $_POST['cwing'][$i];
                if($_POST['cwing'][$i]=="1"){
                    $meta['fwing'] = $_POST['fwing'][$i];
                    $meta['bwing'] = $_POST['bwing'][$i];
                }
            } else if($type==2||$type==6){
                $page_inside += $page = $_POST['page'][$i];
                $print2 = $_POST['print'][$i];
            } else {
                $page = $_POST['page'][$i];
            }
            //แผ่นพับ
            if($type==3){
                $meta['folding'] = $_POST['folding'][$i];
            }
            //ไดคัท
            if($_POST['other'][$i]=="1"){
                $post = (isset($_POST["post_$i"])?implode(",",$_POST["post_$i"]):"");
            } else {
                $post = "";
            }
            //cal allowance
            $allowance = cal_allo($ainfo, $_POST['amount'],$_POST['print'][$i],$print2);
            //add comp
            $paper_id = $db->get_paper($_POST['paper_type'][$i], $_POST['paper_size'][$i], $_POST['paper_gram'][$i]);
            $db->insert_data("pap_quote_comp",array($qid,$_POST['comp_type'][$i],$page,$paper_id['mat_id'],$_POST['paper_lay'][$i],$_POST['paper_cut'][$i],$allowance,$_POST['coating'][$i],$_POST['print'][$i],$print2,$post));
            //coat2
            array_push($coat2,($_POST['print2'][$i]>0?$_POST['coating2'][$i]:0));
            //coat pages
            array_push($coatpage,($type=="2"||$type=="6"?$_POST['coatpage'][$i]:0));
        }
    }
    //คำนวนหลายยอดพิมพ์
    $arramount = array();
    for($i=0;$i<count($_POST['m_amount']);$i++){
        $amount = $_POST['m_amount'][$i];
        if($amount>0){
            array_push($arramount,$amount);
        }
    }
    //อื่นๆ
    $other = array();
    for($i=0;$i<count($_POST['olist']);$i++){
        if($_POST['olist'][$i]!=""){
            array_push($other,array($_POST['olist'][$i],$_POST['ocost'][$i]));
        }
    }
    //update meta
    $meta += array(
        "remark" => $_POST['remark'],
        "exclude" => $exclude,
        "packing" => (isset($_POST['pack'])?implode(",",$_POST['pack']):""),
        "shipping" => (isset($_POST['ship'])?implode(",",$_POST['ship']):""),
        "discount" => 0,
        "adj_margin" => "",
        "adj_cost" => "",
        "contact_id" => $_POST['cusct'],
        "page_cover" => $page_cover,
        "page_inside" => $page_inside,
        "cal_amount" => implode(",",$arramount),
        "other_price" => json_encode($other),
        "coat2" => json_encode($coat2),
        "coatpage" => json_encode($coatpage)
    );
    $db->update_meta("pap_quote_meta", "quote_id", $qid, $meta);

    //calculate cost and detail price
    include_once("quote_formular.php");
    $info = $db->get_quote_allinfo($qid);
    $comps = $db->get_comp($qid);
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='cinfo'");
    $margin = $cinfo['margin'];

    $res = cal_quote($info, $comps);
    $total_cost = 0;
    $pricelist = array();
    foreach($res as $k=>$v){
        foreach($v as $key=>$val){
            $total_cost += $val[4];
            if($k=="ทำเพลต"||$k=="กระดาษ"){
                array_push($pricelist,array_merge(array(0),$val));
            }
        }
    }

    //calculate several amount
    $calinfo = array();
    foreach($arramount as $namount){
        $info['amount'] = $namount;
        $res = cal_quote($info, $comps);
        $tt = 0;
        foreach($res as $k=>$v){
            foreach($v as $key=>$val){
                $tt += $val[4];
            }
        }
        $ainfo = array(
            "show" => 1,
            "amount" => $namount,
            "remark" => "",
            "price" => round($tt*(100+$margin)/100)
        );
        array_push($calinfo,$ainfo);
    }
    $exmeta = array(
        "multi_quote_info" => json_encode($calinfo),
        "detail_price" => json_encode($pricelist),
        "print_cost" => $total_cost
    );
    $db->update_meta("pap_quote_meta", "quote_id", $qid, $exmeta);

    //update quote price
    $price = round($total_cost*(100+$margin)/100);
    $db->update_data("pap_quotation", "quote_id", $qid, array("q_price"=>$price));

    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']."?qid=".$qid);
} else if($req == "edit_quote"){
    $qid = filter_input(INPUT_POST,"qid",FILTER_SANITIZE_NUMBER_INT);
    $status = (int)filter_input(INPUT_POST,'status',FILTER_SANITIZE_NUMBER_INT);
    $prepress = (isset($_POST['prepress'])?implode(",",$_POST['prepress']):"");
    $exclude = (isset($_POST['exclude'])?implode(",",$_POST['exclude']):"");
    //update
    $arrinfo = array(
        "cat_id" => $_POST['type'],
        "name" => $_POST['name'],
        "prepress" => $prepress,
        "binding_id" => $_POST['binding'],
        "amount" => $_POST['amount'],
        "q_price" => $_POST['q_price'],
        "credit" => $_POST['credit'],
        "plan_delivery" => $_POST['due'],
        "status" => $status,
    );
    // 39 == approved, 40=sent ,41=ok 42=reject
    if($status==1){
        $arrinfo['finished'] = null;
    } else if($status==2){
        $arrinfo['approved'] = pap_now();
        $arrinfo['finished'] = null;
    } else if($status==10){
        $arrinfo['finished'] = pap_now();
    } else if($status==9){
        $arrinfo['finished'] = pap_now();
    }
    $db->update_data("pap_quotation", "quote_id", $qid, $arrinfo);

    //del old comp
    $db->delete_data("pap_quote_comp", "quote_id", $qid);

    //add new comp
    $n = count($_POST['page']);
    $page_cover = 0;
    $page_inside = 0;
    //cal allowance
    $adata = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='cinfo'");
    $ainfo = json_decode($adata['paper_allo'],true);
    $meta['cwing'] = 0;
    $coat2 = array();
    $coatpage = array();
    for($i=0;$i<$n;$i++){
        if($_POST['paper_size'][$i]>0&&$_POST['paper_type'][$i]>0&&$_POST['paper_gram'][$i]>0){
            $type = $_POST['comp_type'][$i];
            $print2 = $_POST['print2'][$i];
            if($type=="1"){ //ปก
                $page = $page_cover = 1;
                //ปกปีก
                $meta['cwing'] = $_POST['cwing'][$i];
                if($_POST['cwing'][$i]=="1"){
                    $meta['fwing'] = $_POST['fwing'][$i];
                    $meta['bwing'] = $_POST['bwing'][$i];
                }
            } else if($type==2||$type==6){
                $page_inside += $page = $_POST['page'][$i];
                $print2 = $_POST['print'][$i];
            } else {
                $page = $_POST['page'][$i];
            }
            //แผ่นพับ
            if($type==3){
                $meta['folding'] = $_POST['folding'][$i];
            }
            //ไดคัท
            if($_POST['other'][$i]=="1"){
                $post = (isset($_POST["post_$i"])?implode(",",$_POST["post_$i"]):"");
            } else {
                $post = "";
            }
            //add comp
            //cal allowance
            $allowance = cal_allo($ainfo, $_POST['amount'],$_POST['print'][$i],$print2);
            $allo = (isset($_POST['allowance'][$i])?$_POST['allowance'][$i]:$allowance);
            $paper_id = $db->get_paper($_POST['paper_type'][$i], $_POST['paper_size'][$i], $_POST['paper_gram'][$i]);
            $db->insert_data("pap_quote_comp",array($qid,$_POST['comp_type'][$i],$page,$paper_id['mat_id'],$_POST['paper_lay'][$i],$_POST['paper_cut'][$i],$allo,$_POST['coating'][$i],$_POST['print'][$i],$print2,$post));
            //coat2
            array_push($coat2,($_POST['print2'][$i]>0?$_POST['coating2'][$i]:0));
            //coat pages
            array_push($coatpage,($type=="2"||$type=="6"?$_POST['coatpage'][$i]:0));
        }
    }
    if($_POST['pauth']>=3){
        //adjust margin
        $meta['adj_margin'] = implode(",",$_POST['adj_margin']);
        $meta['adj_cost'] = implode(",",$_POST['adj_cost']);
    }
    if($_POST['pauth']>=3||$status>1){
        //คำนวนหลายยอดพิมพ์
        $arramount = array();
        for($i=0;$i<count($_POST['m_amount']);$i++){
            $amount = $_POST['m_amount'][$i];
            if($amount>0){
                array_push($arramount,$amount);
            }
        }
        $mquote = array();
        for($i=0;$i<count($_POST['qtt']);$i++){
            if($_POST['qtt'][$i]>0){
                $arrinfo = array(
                    "show" => $_POST['qshow'][$i],
                    "amount" => $_POST['qqty'][$i],
                    "remark" => $_POST['qlist'][$i],
                    "price" => $_POST['qtt'][$i]
                );
              array_push($mquote,$arrinfo);
            }
        }
        //แสดงราคาแยกส่วน
        $detail_price = array();
        for($i=0;$i<count($_POST['ptt']);$i++){
            if($_POST['ptt'][$i]>0){
        array_push($detail_price,array($_POST['pshow'][$i],$_POST['plist'][$i],$_POST['pqty'][$i],$_POST['pperu'][$i],$_POST['ptt'][$i]));
            }
        }
        $meta += array(
            "cal_amount" => implode(",",$arramount),
            "detail_price" => json_encode($detail_price),
            "multi_quote_info" => json_encode($mquote)
        );
    }
    //อื่นๆ
    $other = array();
    for($i=0;$i<count($_POST['olist']);$i++){
        if($_POST['olist'][$i]!=""){
            array_push($other,array($_POST['olist'][$i],$_POST['ocost'][$i]));
        }
    }
     //update meta
    $meta += array(
        "remark" => $_POST['remark'],
        "exclude" => $exclude,
        "packing" => (isset($_POST['pack'])?implode(",",$_POST['pack']):""),
        "shipping" => (isset($_POST['ship'])?implode(",",$_POST['ship']):""),
        "discount" => $_POST['discount'],
        "contact_id" => (isset($_POST['cusct'])?$_POST['cusct']:0),
        "page_cover" => $page_cover,
        "page_inside" => $page_inside,
        "other_price" => json_encode($other),
        "coat2" => json_encode($coat2),
        "coatpage" => json_encode($coatpage)
    );

    //ต่อรองราคา
    if($status==4){
        $meta['n_price'] = $_POST['n_price'];
    }
    $db->update_meta("pap_quote_meta", "quote_id", $qid, $meta);

    //calculate cost
    include_once("quote_formular.php");
    $info = $db->get_quote_allinfo($qid);
    $comps = $db->get_comp($qid);
    $res = cal_quote($info, $comps);
    $total_cost = 0;
    foreach($res as $k=>$v){
        foreach($v as $key=>$val){
            $total_cost += $val[4];
        }
    }
    $exmeta = array(
        "print_cost" => $total_cost
    );
    //upload file
    if(isset($_POST['mdfile'])){
        if($_POST['mdfile']!=$_POST['o_file']){
            __autoloada("media");
            $md = new mymedia();
            $file = $_POST['mdfile'];
            $ono = $info['quote_no'];
            $des = dirname(__FILE__)."/image/quote_sign/$ono".".".pathinfo($file,PATHINFO_EXTENSION);
            $md->move_file(RDIR.$file, $des);
            $exmeta['quote_sign'] = "/p-pap/image/quote_sign/$ono".".".pathinfo($file,PATHINFO_EXTENSION);
        }
    }
    $db->update_meta("pap_quote_meta", "quote_id", $qid, $exmeta);

    if($status==9){
        //ok create order
        include_once("prep_order.php");
        $create_order = prep_order($qid);
        if(!$create_order){
            $_SESSION['error'] = "ใบเสนอราคานี้อยู่ในแผนผลิตแล้ว";
        } else {
            $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
        }
    } else {
        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    }
    //header("Location:".$_POST['redirect'].($status==1?"?qid=$qid":""));
} else if ($req=="update_qprice"){
    $db->update_data("pap_quotation", "quote_id", $_POST['qid'], array("q_price"=>$_POST['q_price']));
    echo json_encode("ok");
} else if ($req=="add_mat"){
    //check name
    $cat = (int)filter_input(INPUT_POST,'cat',FILTER_SANITIZE_NUMBER_INT);
    $field = ($cat==8?"CONCAT(mat_type,mat_size,mat_weight)":"mat_name");
    $val = ($cat==8?$_POST['ptype'].$_POST['size'].$_POST['weight']:$_POST['name']);
    if($db->check_dup("pap_mat", $field, $val)){
        $_SESSION['error'] = "มีข้อมูลวัสดุ ".$_POST['matname']." แล้วโปรดลองใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else {
        if($cat==8){
            //mat paper
            if($_POST['pcost_t']=="baht/lot"){
                $cost_per_u = round($_POST['pcost']/$_POST['lot'],5);
            } else {
                $tpsize = $db->get_info("pap_option", "op_id", $_POST['size']);
                $psize = json_decode($tpsize['op_value'],true);
                $pweight = $db->get_info("pap_option","op_id",$_POST['weight']);
                $cost_per_u = round($psize['width']*$psize['length']*$pweight['op_name']*$_POST['pcost']/(3100*500),5);
            }
            $mid = $db->insert_data("pap_mat", array(null,"",$_POST['matname'],"แผ่น",$cat,$_POST['ptype'],$_POST['size'],$_POST['weight'],$_POST['lot'],$cost_per_u,$_POST['lt']));
            //mat meta
            $meta = array(
                "paper_cost_base" => $_POST['pcost_t'],
                "paper_cost" => $_POST['pcost']
            );
            $db->update_meta("pap_matmeta", "mat_id", $mid, $meta);
        } else {
            //mat others
            $db->insert_data("pap_mat", array(null,"",$_POST['name'],($_POST['unit']==""?"หน่วย":$_POST['unit']),$cat,"","","",$_POST['lot'],$_POST['cost'],$_POST['lt']));
        }
        $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "edit_mat"){
    //check name
    $cat = (int)filter_input(INPUT_POST,'cat',FILTER_SANITIZE_NUMBER_INT);
    $mid = (int)filter_input(INPUT_POST,'mid',FILTER_SANITIZE_NUMBER_INT);
    $field = ($cat==8?"CONCAT(mat_type,mat_size,mat_weight)":"mat_name");
    $val = ($cat==8?$_POST['ptype'].$_POST['size'].$_POST['weight']:$_POST['name']);
    if($db->check_dup("pap_mat", $field,$val,"mat_id<>$mid")){
        $_SESSION['error'] = "มีข้อมูลวัสดุ ".$_POST['matname']." แล้วโปรดลองใหม่";
        header("Location:".$_POST['redirect']."?action=add");
    } else {
        //update
        if($cat==8){
            //mat paper
            if($_POST['pcost_t']=="baht/lot"){
                $cost_per_u = round($_POST['pcost']/$_POST['lot'],5);
            } else {
                $tpsize = $db->get_info("pap_option", "op_id", $_POST['size']);
                $psize = json_decode($tpsize['op_value'],true);
                $pweight = $db->get_info("pap_option","op_id",$_POST['weight']);
                $cost_per_u = round($psize['width']*$psize['length']*$pweight['op_name']*$_POST['pcost']/(3100*500),5);
            }
        } else {
            $cost_per_u = $_POST['cost'];
        }
        $arrinfo = array(
            "mat_name" => $_POST['matname'],
            "mat_unit" => ($cat==8?"แผ่น":$_POST['unit']),
            "mat_cat_id" => $_POST['cat'],
            "mat_type" => ($cat==8?$_POST['ptype']:""),
            "mat_size" => ($cat==8?$_POST['size']:""),
            "mat_weight" => ($cat==8?$_POST['weight']:""),
            "mat_order_lot_size" => $_POST['lot'],
            "mat_std_cost" => $cost_per_u,
            "mat_std_leadtime" => $_POST['lt']
        );
        $db->update_data("pap_mat", "mat_id", $mid, $arrinfo);

        //mat meta
        $meta = array(
            "paper_cost_base" => $_POST['pcost_t'],
            "paper_cost" => $_POST['pcost']
        );
        $db->update_meta("pap_matmeta", "mat_id", $mid, $meta);

        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
        header("Location:".$_POST['redirect']);
    }
} else if($req == "add_cus_ct"){
    if($_POST['ctid']>0){
        //edit
        $arrinfo = array(
            "contact_name" => $_POST['cname'],
            "contact_email" => $_POST['cemail'],
            "contact_tel" => $_POST['ctel'],
            "contact_remark" => $_POST['cetc']
        );
        $db->update_data("pap_contact","contact_id",$_POST['ctid'],$arrinfo);
    } else {
        //add
        $db->insert_data("pap_contact",array(null,$_POST['cid'],$_POST['ct_cat'],$_POST['cname'],$_POST['cemail'],$_POST['ctel'],$_POST['cetc']));
        $_SESSION['message'] = "เพิ่มผู้ติดต่อสำเร็จ";
    }
    echo json_encode(array("redirect",$_POST['redirect']."?cid=".$_POST['cid']));
} else if($req == "add_note"){
    $db->insert_data("pap_crm", array(null,$_POST['uid'],$_POST['cid'],$_POST['note'],$_POST['date']));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "edit_note"){
    $arrinfo = array(
        "crm_date" => $_POST['date'],
        "crm_detail" => $_POST['note']
    );
    $db->update_data("pap_crm", "crm_id", $_POST['nid'], $arrinfo);
    $_SESSION['message'] = "แก้ข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "find_cid"){
    $res = $db->find_customer($_POST['f'],($_POST['pauth']>3?null:$_POST['uid']));
    echo json_encode($res);
} else if($req == "filter_paper"){
    $res = $db->filter_paper($_POST['sid'],$_POST['index']);
    echo json_encode($res);
} else if($req == "filter_papern"){
    $i = $_POST['index'];
    $c_ptype = array("0"=>"--กระดาษ--") + $db->get_paper_keypair("mat_type", $_POST['size']);
    $html = $form->show_select("paper_type_$i",$c_ptype,"label-3070","กระดาษ",null,"","paper_type[]");
    echo json_encode($html);
} else if($req == "filter_gram"){
    $gram = array("0"=>"--แกรม--")+$db->get_paper_keypair("mat_weight", $_POST['size'], $_POST['type']);
    $res = $form->show_select("paper_gram_".$_POST['index'],$gram,"label-3070","แกรม",null,"","paper_gram[]");
    echo json_encode($res);
} else if($req == "get_selcontact"){
    $contacts = $db->get_keypair("pap_contact","contact_id","contact_name","WHERE customer_id=".$_POST['cid']);
    $res = $form->show_select("cusct", $contacts, "label-3070", "ผู้ติดต่อ", null);
    echo json_encode(array("html_replace","cus_ct",$res));
} else if($req=="add_term"){
    //add term
    $tid = $db->insert_data("pap_term", array(null,$_POST['name'],$_POST['slug'],$_POST['des']));
    //meta
    if(isset($_POST['margin'])){
        $meta['margin'] = $_POST['margin'];
        $db->update_meta("pap_term_meta", "term_id", $tid, $meta);
    }
    //add tax
    $parent = $_POST['parent'];
    if($parent>0){
        $pinfo = $db->get_info("pap_term_tax","term_id",$parent);
        $lineage = $pinfo['lineage']."-$tid";
        $deep = (int)$pinfo['deep']+1;
        $db->insert_data("pap_term_tax", array(null,$tid,$_POST['tax'],$parent,$lineage,$deep,0));
    } else {
        $db->insert_data("pap_term_tax", array(null,$tid,$_POST['tax'],$parent,$tid,0,0));
    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req =="edit_term"){
    __autoloada("term");
    $termdb = new myterm(DB_PAP);
    $tid = $_POST['tid'];
    $parent = $_POST['parent'];
    //update term
    $arrinfo = array(
        "name" => $_POST['name'],
        "slug" => $_POST['slug'],
        "des" => $_POST['des']
    );
    $db->update_data("pap_term", "id", $tid, $arrinfo);
    //meta
    if(isset($_POST['margin'])){
        $meta['margin'] = $_POST['margin'];
        $db->update_meta("pap_term_meta", "term_id", $tid, $meta);
    }
    //update term_tax
    if($_POST['oparent']!=$parent){
        $termdb->update_parent($_POST['tax'],$tid,$_POST['oparent'],$parent);
    }
    $_SESSION['message'] = "ปรับข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_supplier"){
    $code = $db->check_supplier_code($_POST['cat']);
    //insert
    $sid = $db->insert_data("pap_supplier", array(null,$code,$_POST['name'],$_POST['taxid'],$_POST['address'],$_POST['url'],$_POST['email'],$_POST['tel'],$_POST['fax'],$_POST['pay'],$_POST['credit_day'],$_POST['credit'],pap_now()));

    //add cat
    $db->insert_data("pap_supplier_cat", array($_POST['cat'],$sid));

    //add contact
    $n = count($_POST['cname']);
    for($i=0;$i<$n;$i++){
        if($_POST['cname'][$i]!=""){
            $db->insert_data("pap_supplier_ct",array(null,$sid,$_POST['cname'][$i],$_POST['cemail'][$i],$_POST['ctel'][$i],$_POST['cetc'][$i]));
        }
    }
    //add meta
    $meta = array(
        "branch" => $_POST['branch']
    );
    $db->update_meta("pap_supplier_meta", "supplier_id", $sid, $meta);
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "edit_supplier"){
    $sid = filter_input(INPUT_POST,"sid",FILTER_SANITIZE_NUMBER_INT);
    //update
    $arrinfo = array(
        "name" => $_POST['name'],
        "taxid" => $_POST['taxid'],
        "address" => $_POST['address'],
        "url" => $_POST['url'],
        "email" => $_POST['email'],
        "tel" => $_POST['tel'],
        "fax" => $_POST['fax'],
        "payment" => $_POST['pay'],
        "credit_day" => ($_POST['pay']==0?0:$_POST['credit_day']),
        "credit_amount" => ($_POST['pay']==0?0:$_POST['credit'])
    );
    $db->update_data("pap_supplier", "id", $sid, $arrinfo);
    /*update code
    if($_POST['ori_cat']<>$_POST['cat']){
        $arrinfo['code'] = $db->check_supplier_code($_POST['cat']);
    }
     *
     */
    //update cat
    $db->delete_data("pap_supplier_cat", "supplier_id", $sid);
    $db->insert_data("pap_supplier_cat", array($_POST['cat'],$sid));
    
    //update meta
    $meta = array(
        "branch" => $_POST['branch']
    );
    $db->update_meta("pap_supplier_meta", "supplier_id", $sid, $meta);
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_sup_ct"){
    if($_POST['ctid']>0){
        //edit
        $arrinfo = array(
            "name" => $_POST['cname'],
            "email" => $_POST['cemail'],
            "tel" => $_POST['ctel'],
            "remark" => $_POST['cetc']
        );
        $db->update_data("pap_supplier_ct","id",$_POST['ctid'],$arrinfo);
    } else {
        //add
        $db->insert_data("pap_supplier_ct",array(null,$_POST['sid'],$_POST['cname'],$_POST['cemail'],$_POST['ctel'],$_POST['cetc']));
        $_SESSION['message'] = "เพิ่มผู้ติดต่อสำเร็จ";
    }
    echo json_encode(array("redirect",$_POST['redirect']."?sid=".$_POST['sid']));
} else if($req=="add_machine"){
    $mid = $db->insert_data("pap_machine", array(null,$_POST['sel_process'],$_POST['name'],$_POST['capacity'],$_POST['setup_min']));
    if(isset($_POST['operator'])){
        foreach($_POST['operator'] AS $k=>$v){
            $db->insert_data("pap_mach_user",array($v,$mid));
        }
    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_machine"){
    $mid = filter_input(INPUT_POST,'mid',FILTER_SANITIZE_NUMBER_INT);
    $arrdata = array(
        "process_id" => $_POST['sel_process'],
        "name" => $_POST['name'],
        "cap" => $_POST['capacity'],
        "setup_min" => $_POST['setup_min']
    );
    $db->update_data("pap_machine", "id", $mid, $arrdata);
    //del user
    $db->delete_data("pap_mach_user", "mach_id", $mid);
    //add user
    if(isset($_POST['operator'])){
        foreach($_POST['operator'] AS $k=>$v){
            $db->insert_data("pap_mach_user",array($v,$mid));
        }
    }
    $_SESSION['message'] = "ปรับปรุงข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "add_mat_po"){
    $sinfo = $db->get_info("pap_supplier", "id", $_POST['supplier']);
    //add mat_po
    $data = array(
        null,
        $db->check_matpo_no(),
        $_POST['uid'],
        $_POST['supplier'],
        $_POST['sup_ct'],
        0,
        $_POST['due'],
        $_POST['status'],
        ($sinfo['payment']==1?$sinfo['credit_day']:0),
        $_POST['remark'],
        pap_now(),
        null,
        null,
        null
    );
    $poid = $db->insert_data("pap_mat_po", $data);

    //add detail
    $tt = 0;
    for($i=0;$i<count($_POST['mid']);$i++){
        $tt += $_POST['cost'][$i]*$_POST['vol'][$i];
        $oid = $_POST['oid'][$i];
        $db->insert_data("pap_mat_po_detail", array(null,$poid,$_POST['mid'][$i],$_POST['cost'][$i],$_POST['vol'][$i],$oid));

        //check order vs po if order all => put plan delivery
        $db->check_req_vs_po($oid);
    }
    //update total po
    $db->update_data("pap_mat_po", "po_id", $poid, array("po_cost"=>round($tt,2)));

    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']."?action=viewpo");
} else if($req == "edit_mat_po"){
    $poid = filter_input(INPUT_POST,'poid',FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST,'status',FILTER_SANITIZE_NUMBER_INT);
    //update
    $arrdata = array(
        "supplier_id" => $_POST['supplier'],
        "ct_id" => $_POST['sup_ct'],
        "po_cost" => 0,
        "po_delivery_plan" => $_POST['due'],
        "po_status" => $status,
        "po_payment" => $_POST['payment'],
        "po_remark" => $_POST['remark']
    );
    $db->update_data("pap_mat_po", "po_id", $poid, $arrdata);

    //delete old po detail
    $db->delete_data("pap_mat_po_detail", "po_id", $poid);
    //add detail
    $tt = 0;
    for($i=0;$i<count($_POST['mid']);$i++){
        $tt += $_POST['cost'][$i]*$_POST['vol'][$i];
        $oid = $_POST['oid'][$i];
        $db->insert_data("pap_mat_po_detail", array(null,$poid,$_POST['mid'][$i],$_POST['cost'][$i],$_POST['vol'][$i],$oid));

        //check order vs po if order all => put plan delivery
        $db->check_req_vs_po($oid);
    }
    //update total po
    $db->update_data("pap_mat_po", "po_id", $poid, array("po_cost"=>round($tt,2)));
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']."?action=viewpo");
} else if($req == "add_process_po"){
    $sinfo = $db->get_info("pap_supplier", "id", $_POST['supplier']);
    //add process_po
    $data = array(
        null,
        $db->check_processpo_no(),
        $_POST['uid'],
        $_POST['supplier'],
        $_POST['sup_ct'],
        0,
        $_POST['due'],
        $_POST['status'],
        ($sinfo['payment']==1?$sinfo['credit_day']:0),
        $_POST['remark'],
        pap_now(),
        null,
        null,
        null
    );
    $poid = $db->insert_data("pap_process_po", $data);

    //add detail
    $tt = 0;
    for($i=0;$i<count($_POST['pid']);$i++){
        $tt += $_POST['cost'][$i]*$_POST['vol'][$i];
        $cproid = $_POST['cproid'][$i];
        $db->insert_data("pap_pro_po_dt", array(null,$poid,$_POST['pid'][$i],$_POST['unit'][$i],$_POST['cost'][$i],$_POST['vol'][$i],$cproid));
    }
    //update total po
    $db->update_data("pap_process_po", "po_id", $poid, array("po_cost"=>round($tt,2)));

    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']."?action=viewpo");
} else if($req == "edit_process_po"){
    $poid = filter_input(INPUT_POST,'poid',FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST,'status',FILTER_SANITIZE_NUMBER_INT);
    //update
    $arrdata = array(
        "supplier_id" => $_POST['supplier'],
        "ct_id" => $_POST['sup_ct'],
        "po_cost" => 0,
        "po_delivery_plan" => $_POST['due'],
        "po_status" => $status,
        "po_payment" => $_POST['payment'],
        "po_remark" => $_POST['remark']
    );
    $db->update_data("pap_process_po", "po_id", $poid, $arrdata);

    //delete old po detail
    $db->delete_data("pap_pro_po_dt", "po_id", $poid);
    //add detail
    $tt = 0;
    for($i=0;$i<count($_POST['pid']);$i++){
        $tt += $_POST['cost'][$i]*$_POST['vol'][$i];
        $cproid = $_POST['cproid'][$i];
        $db->insert_data("pap_pro_po_dt", array(null,$poid,$_POST['pid'][$i],$_POST['unit'][$i],$_POST['cost'][$i],$_POST['vol'][$i],$cproid));
    }
    //update total po
    $db->update_data("pap_process_po", "po_id", $poid, array("po_cost"=>round($tt,2)));
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']."?action=viewpo");
} else if($req == "update_ga_status"){
    $status = $_POST['status'];
    $arrinfo["status"] = $status;
    if($status=="2"){
        $arrinfo["plate_plan"] = $_POST['date'];
    } else if($status =="7"){
        $arrinfo["plate_received"] = $_POST['date'];
        //check paper received
        $info = $db->get_info("pap_order", "order_id", $_POST['oid']);
        if(!is_null($info['paper_received'])){
            $arrinfo["status"] = 8;
        }
    }
    $db->update_data("pap_order", "order_id", $_POST['oid'], $arrinfo);
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req == "edit_order"){
    //move picture
    $orderno = $_POST['order_no'];
    if(isset($_POST['media'])){
        $file = $_POST['media'];
        if($_POST['ori_media']!=$file){
            __autoloada("media");
            $md = new mymedia();
            $des = dirname(__FILE__)."/image/job/$orderno".".".pathinfo($file,PATHINFO_EXTENSION);
            $md->move_file(RDIR.$file, $des);
            $arrinfo['picture'] = "/p-pap/image/job/$orderno".".".pathinfo($file,PATHINFO_EXTENSION);
        }
    }
    //update order
    $arrinfo['remark'] = $_POST['remark'];
    $db->update_data("pap_order", "order_id", $_POST['oid'],$arrinfo);
    //update quote
    $db->update_data("pap_quotation","quote_id",$_POST['qid'],array("plan_delivery"=>$_POST['due']));
    //paper cut check with original
    for($i=0;$i<count($_POST['comp']);$i++){
        if($_POST['comp'][$i]!=$_POST['compo'][$i]){
            //update comp
            $mult = $_POST['compo'][$i]/$_POST['comp'][$i];
            $arrdata = array(
                "paper_lay" => $_POST['clay'][$i]*$mult,
                "paper_cut" => $_POST['comp'][$i],
                "print_size" => $_POST['printsize'][$i],
                "allowance" => $_POST['allo'][$i]
            );
            $db->update_data("pap_order_comp", "id", $_POST['compid'][$i],$arrdata);

            //update comp process
            include_once("prep_order.php");
            recal_process($_POST['compid'][$i]);
        }
    }


    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="po_paper"){
    $_SESSION['upap']['po'] = $_POST['po_paper'];
    header("Location:".$_POST['redirect']);
} else if($req=="outsource"){
    $_SESSION['upap']['outsource'] = $_POST['process'];
    header("Location:".$_POST['redirect']);
} else if($req=="add_mat_received"){
    //add receive
    $did = $db->insert_data("pap_mat_delivery", array(null,$_POST['poid'],$_POST['uid'],$_POST['docref'],  pap_now(),$_POST['remark']));

    //add receive detail
    for($i=0;$i<count($_POST['receive']);$i++){
        $oid = $_POST['oid'][$i];
        $dtid = $_POST['dtid'][$i];
        $db->insert_data("pap_mat_delivery_dt", array($did,$dtid,$_POST['receive'][$i],$_POST['loc'][$i]));

        //check order vs delivery if order all deliveried => put delivery date
        $db->check_req_vs_delivery($oid);
    }
    //check po vs delivery if deliveried all => change po status;
    $db->check_po_vs_delivery($_POST['poid']);
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_process_rc"){
    //add receive
    $did = $db->insert_data("pap_wip_delivery", array(null,$_POST['poid'],$_POST['uid'],$_POST['docref'],  pap_now(),$_POST['remark']));

    //add receive detail
    for($i=0;$i<count($_POST['receive']);$i++){
        $dtid = $_POST['dtid'][$i];
        $db->insert_data("pap_wip_delivery_dt", array($did,$dtid,$_POST['receive'][$i],$_POST['loc'][$i]));

        //check if received = remain update comp status
        if($_POST['rem'][$i]==$_POST['receive'][$i]){
            $proinfo = $db->get_info("pap_process", "process_id", $_POST['pid'][$i]);
            $cat = $proinfo['process_cat_id'];
            $db->update_data("pap_order_comp", "id", $_POST['compid'][$i], array("status"=>$cat));
            //update job status
            update_job_status($_POST['oid'][$i]);
        }
        //update comp process result
        $dtinfo = $db->get_info("pap_pro_po_dt", "id", $dtid);
        $poinfo = $db->get_info("pap_process_po","po_id",$dtinfo['po_id']);
        $cproid = $dtinfo['cpro_id'];
        $cproinfo = $db->get_info("pap_comp_process", "id", $cproid);
        $result = $cproinfo['result']+$_POST['receive'][$i];
        $db->update_data("pap_comp_process", "id", $cproid, array("result"=>$result,"start"=>$poinfo['po_created'],"end"=>  pap_now()));
    }
    //check po vs delivery if deliveried all => change po status;
    $db->check_ppo_vs_delivery($_POST['poid']);
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_mjob_deli"){
    //create deli
    $code = $db->check_deli_code($_POST['date']);
    $did = $db->insert_data("pap_delivery", array(null,$code,$_POST['deli_ct'],$_POST['address'],$_POST['remark'],$_POST['date'],69,0));
    //add deli detail
    $tt = 0;
    for($i=0;$i<count($_POST['name']);$i++){
        if($_POST['amount'][$i]>0){
            $totalp = $_POST['price'][$i]*$_POST['amount'][$i];
            $tt += $totalp-$_POST['discount'][$i];
            $dtid = $db->insert_data("pap_delivery_dt", array(null,$did,"",$_POST['amount'][$i],$totalp,$_POST['discount'][$i],$_POST['name'][$i],$_POST['credit'],$_POST['cid'],$_POST['type'][$i]));
            //add mdeli meta
            $db->update_meta("pap_delidt_meta", "dtid", $dtid, array("job_detail"=>$_POST['job_detail'][$i]));
        }
    }
    
    //update deli total
    $db->update_data("pap_delivery", "id", $did, array("total"=>$tt));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_mjob_deli"){
    //update deli
    $arrinfo = array(
        "contact" => $_POST['deli_ct'],
        "address" => $_POST['address'],
        "remark" => $_POST['remark'],
        "date" => $_POST['date']
    );
    $db->update_data("pap_delivery", "id", $_POST['did'], $arrinfo);
    //delete deli detail
    $db->delete_data("pap_delivery_dt", "deli_id", $_POST['did']);
    //add deli detail
    $tt = 0;
    for($i=0;$i<count($_POST['name']);$i++){
        if($_POST['amount'][$i]>0){
            $totalp = $_POST['price'][$i]*$_POST['amount'][$i];
            $tt += $totalp-$_POST['discount'][$i];
            $dtid = $db->insert_data("pap_delivery_dt", array(null,$_POST['did'],"",$_POST['amount'][$i],$totalp,$_POST['discount'][$i],$_POST['name'][$i],$_POST['credit'],$_POST['cid'],$_POST['type'][$i]));
            //add mdeli meta
            $db->update_meta("pap_delidt_meta", "dtid", $dtid, array("job_detail"=>$_POST['job_detail'][$i]));
        }
    }
    //update deli total
    $db->update_data("pap_delivery", "id", $_POST['did'], array("total"=>$tt));
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_mjob_tdeli"){
    //check เคยส่ง
    $check = $db->get_info("pap_temp_deli", "deli_id", $_POST['did']);
    //check if deli=amount
    $amount_vs_deli = 0;
    $remain_vs_deli = 0;
    for($i=0;$i<count($_POST['name']);$i++){
        $amount_vs_deli += $_POST['amount'][$i]-$_POST['deli'][$i];
        $remain_vs_deli += $_POST['remain'][$i]-$_POST['deli'][$i];
    }
    if(is_array($check)){
        //เคยส่งแล้ว
        $temp = $db->check_next_tdeli_code(0, $_POST['name'][0]);
        $tno = $temp[1];
    } else {
        //ยังไม่เคยส่ง
        //deli = amount
        $tno = ($amount_vs_deli==0?$_POST['dno']:$_POST['dno']."-1");
    }
    $tdid = $db->insert_data("pap_temp_deli", array(null,$_POST['did'],$tno,$_POST['deli_ct'],$_POST['address'],$_POST['remark'],$_POST['date']));
    //add temp detail
    for($i=0;$i<count($_POST['name']);$i++){
        $db->insert_data("pap_temp_dt", array($tdid,0,$_POST['deli'][$i],$_POST['name'][$i]));
    }
    //update deli status
    $status = ($remain_vs_deli>0?70:79);
    $db->update_data("pap_delivery", "id", $_POST['did'], array("status"=>$status));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_mjob_tdeli"){
    $arrinfo = array(
        "contact" => $_POST['deli_ct'],
        "address" => $_POST['address'],
        "remark" => $_POST['remark'],
        "date" => $_POST['date']
    );
    $db->update_data("pap_temp_deli", "id", $_POST['tdid'], $arrinfo);
    //del temp detail
    $db->delete_data("pap_temp_dt", "temp_deli_id", $_POST['tdid']);
    //add temp detail
    $rem = 0;
    for($i=0;$i<count($_POST['name']);$i++){
        $db->insert_data("pap_temp_dt", array($_POST['tdid'],0,$_POST['deli'][$i],$_POST['name'][$i]));
        $rem += $_POST['remain'][$i]-$_POST['deli'][$i];
    }
    //update deli status
    $status = ($rem>0?70:79);
    $db->update_data("pap_delivery", "id", $_POST['did'], array("status"=>$status));
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_job_deli"){
    //check any oid in temp deli
    if($db->check_job_in_deli($_POST['oid'])){
        //had job in early deli
        //find last code
        $tcode = $db->check_next_tdeli_code($_POST['oid'][0]);
        $did = $tcode[0];
        //create temp deli
        $tdid = $db->insert_data("pap_temp_deli", array(null,$did,$tcode[1],$_POST['deli_ct'],$_POST['address'],$_POST['remark'],$_POST['date']));
        //add temp deli detail
        for($i=0;$i<count($_POST['deli']);$i++){
            if($_POST['deli'][$i]>0){
                $db->insert_data("pap_temp_dt", array($tdid,$_POST['oid'][$i],$_POST['deli'][$i],""));
            }
            //check deli==remain?
            if($_POST['rem'][$i]==$_POST['deli'][$i]){
                //update job status
                $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>79)); //79 = ส่งทั้งหมด
            } else {
                $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>70)); //70 = ส่งบางส่วน
            }
        }
    } else {
        //no job in any deli before
        //create deli
        $code = $db->check_deli_code($_POST['date']);
        $did = $db->insert_data("pap_delivery", array(null,$code,$_POST['deli_ct'],$_POST['address'],$_POST['remark'],$_POST['date'],80,0));
        //add deli detail
        $tt = 0;
        for($i=0;$i<count($_POST['deli']);$i++){
            if($_POST['deli'][$i]>0){
                $oid = $_POST['oid'][$i];
                $jprice = $db->get_job_price($oid);
                $tt += $jprice['q_price']-$jprice['discount'];
                $db->insert_data("pap_delivery_dt", array(null,$did,$oid,$_POST['amount'][$i],$jprice['q_price'],$jprice['discount'],$jprice['jname'],$jprice['credit'],$jprice['customer_id'],$jprice['cat_id']));
            }
        }
        //update deli total
        $db->update_data("pap_delivery", "id", $did, array("total"=>$tt));

        //create temp deli
        // if amount = deli
        if(array_sum($_POST['amount'])==array_sum($_POST['deli'])){
            $tcode = $code;
        } else {
            $tcode = $code."-1";
        }
        $tdid = $db->insert_data("pap_temp_deli", array(null,$did,$tcode,$_POST['deli_ct'],$_POST['address'],$_POST['remark'],$_POST['date']));
        //add temp deli detail
        for($i=0;$i<count($_POST['deli']);$i++){
            if($_POST['deli'][$i]>0){
                $db->insert_data("pap_temp_dt", array($tdid,$_POST['oid'][$i],$_POST['deli'][$i],""));
            }
            //check deli==amount?
            if($_POST['amount'][$i]==$_POST['deli'][$i]){
                //update job status
                $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>79,"delivery"=>$did));  //79 = ส่งทั้งหมด
            } else {
                $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>70,"delivery"=>$did)); //70 = ส่งบางส่วน
            }
        }
    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_job_deli"){
    $arrdata = array(
        "date" => $_POST['date'],
        "contact" => $_POST['deli_ct'],
        "address" => $_POST['address'],
        "remark" => $_POST['remark']
    );
    $db->update_data("pap_delivery", "id", $_POST['did'],$arrdata);
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_job_tdeli"){
    $arrdata = array(
        "date" => $_POST['date'],
        "contact" => $_POST['deli_ct'],
        "address" => $_POST['address'],
        "remark" => $_POST['remark']
    );
    $db->update_data("pap_temp_deli", "id", $_POST['tdid'],$arrdata);

    //del old temp deli detail
    $db->delete_data("pap_temp_dt", "temp_deli_id", $_POST['tdid']);
    //add temp deli detail
    for($i=0;$i<count($_POST['deli']);$i++){
        if($_POST['deli'][$i]>0){
            $db->insert_data("pap_temp_dt", array($_POST['tdid'],$_POST['oid'][$i],$_POST['deli'][$i],""));
        }
        //check deli==remain?
        if($_POST['rem'][$i]==$_POST['deli'][$i]){
            //update job status
            $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>79)); //79 = ส่งทั้งหมด
        } else {
            $db->update_data("pap_order", "order_id", $_POST['oid'][$i], array("status"=>70)); //70 = ส่งบางส่วน
        }
    }
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_job_plan"){
    $start = $_POST['stdate']." ".$_POST['timeh'].":".$_POST['timem'].":00";
    $std = new DateTime($start,new DateTimeZone("Asia/Bangkok"));
    $min = round($_POST['prodtime']*60);
    $std->add(new DateInterval("PT".$min."M"));
    $end = $std->format("Y-m-d H:i:s");
    //check conflic
    if($_POST['mcid']>0&&!$db->check_schedule($_POST['mcid'], $start, $end, $_POST['cpid'])){
        //overlap same machine
        $_SESSION['error'] = "ช่วงเวลาที่เลือกทับกับแผนที่มีอยู่แล้วในเครื่องจักร";

    } else if(!$db->check_comp_schedule($_POST['cpid'],$start,$end)){
        //overlap in component
        $_SESSION['error'] = "ช่วงเวลาที่เลือกไม่สอดคล้องกับขั้นตอนการผลิตก่อนหน้า หรือหลัง";

    } else if(!$db->check_mcomp_schedule($_POST['oid'],$start,$end,$_POST['type'])){
        //overlap with main component
        $_SESSION['error'] = "ช่วงเวลาที่เลือกไม่สอดคล้องกับส่วนประกอบก่อนหน้า หรือหลัง";
    } else {
        //update
        $arrinfo = array(
            "volume" => $_POST['amount'],
            "est_time_hour" => $_POST['prodtime'],
            "machine_id" => $_POST['mcid'],
            "plan_start" => $_POST['stdate']." ".$_POST['timeh'].":".$_POST['timem'].":00",
            "plan_end" => $end
        );
        $db->update_data("pap_comp_process", "id", $_POST['cpid'], $arrinfo);

        //update job plan
        $db->update_data("pap_order", "order_id", $_POST['oid'], array("prod_plan"=>  pap_now()));

        $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    }
    header("Location:".$_POST['redirect']);
} else if($req=="update_comp_status"){
    if($_POST['compid']>0){
      //update comp status
      $db->update_data("pap_order_comp", "id", $_POST['compid'], array("status"=>$_POST['status']));
      //update job status
      update_job_status($_POST['oid']);
    } else {
        //update main status
        if($_POST['status']==69){
            $finished = pap_now();
        } else {
            $finished = null;
        }
        $db->update_data("pap_order", "order_id", $_POST['oid'], array("status"=>$_POST['status'],"prod_finished"=>$finished));
    }
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_billed"){
    $_SESSION['upap']['did'] = array();
    $_SESSION['upap']['cid'] = array();
    for($i=0;$i<count($_POST['did']);$i++){
        $temp = explode(",",$_POST['did'][$i]);
        if(in_array($temp[0],$_SESSION['upap']['did'])){
            continue;
        } else {
            array_push($_SESSION['upap']['did'],$temp[0]);
            array_push($_SESSION['upap']['cid'],$temp[1]);
        }
    }
    header("Location:".$_POST['redirect']);
} else if($req=="add_p_bill"){
    //add bill
    $bcode = $db->check_bill_code($_POST['date']);
    $bid = $db->insert_data("pap_pbill", array(null,$bcode,$_POST['cid'],$_POST['pbill_ct'],$_POST['payment'],$_POST['date'],$_POST['paydate'],$_POST['remark']));
    //add detail
    for($i=0;$i<count($_POST['did']);$i++){
        //add pbill dt
        $db->insert_data("pap_pbill_dt", array($bid,$_POST['did'][$i],$_POST['price'][$i]));

        //update job
        $db->update_data("pap_order", "delivery", $_POST['did'][$i], array("billed"=>$bid));
    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_p_bill"){
    $arrinfo = array(
        "contact" => $_POST['pbill_ct'],
        "payment" => $_POST['payment'],
        "date" => $_POST['date'],
        "pay_date" => $_POST['paydate'],
        "remark" => $_POST['remark']
    );
    $db->update_data("pap_pbill", "id", $_POST['bid'], $arrinfo);
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_invoice"){
    //add invoice
    $ivno = $db->check_inv_code($_POST['date']);
    $total = array_sum($_POST['inv'])-$_POST['discount'];
    $ivid = $db->insert_data("pap_invoice", array(null,$ivno,$_POST['cid'],$_POST['uid'],$_POST['date'],$_POST['remark'],$_POST['discount'],$total));
    //add_detail
    for($i=0;$i<count($_POST['inv']);$i++){
        //add inv dt
        $db->insert_data("pap_invoice_dt", array($ivid,$_POST['did'][$i],$_POST['inv'][$i]));
        //update delivery status
        if($_POST['inv'][$i]==$_POST['rem'][$i]){
            $db->update_data("pap_delivery", "id", $_POST['did'][$i], array("status"=>90)); //90 = ออก invoice
            //update job
            $db->update_data("pap_order", "delivery", $_POST['did'][$i], array("invoiced"=>$_POST['date']));
        } else {
            $db->update_data("pap_delivery", "id", $_POST['did'][$i], array("status"=>80)); //80 = มีใบแจ้งหนี้
        }

    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_invoice"){
    $ivid = filter_input(INPUT_POST,'ivid',FILTER_SANITIZE_NUMBER_INT);
    $total = array_sum($_POST['inv'])-$_POST['discount'];
    $arrinfo = array(
        "customer_id" => $_POST['cid'],
        "date" => $_POST['date'],
        "remark" => $_POST['remark'],
        "discount" => $_POST['discount'],
        "total" => $total
    );
    //update invoice
    $db->update_data("pap_invoice", "id", $ivid, $arrinfo);
    //del old detail
    $db->delete_data("pap_invoice_dt", "invoice_id", $ivid);
    //add_detail
    for($i=0;$i<count($_POST['inv']);$i++){
        //add inv dt
        $db->insert_data("pap_invoice_dt", array($ivid,$_POST['did'][$i],$_POST['inv'][$i]));
        //update delivery status
        if($_POST['inv'][$i]==$_POST['rem'][$i]){
            $db->update_data("pap_delivery", "id", $_POST['did'][$i], array("status"=>90)); //90 = ออก invoice
            //update job
            $db->update_data("pap_order", "delivery", $_POST['did'][$i], array("invoiced"=>$_POST['date']));
        } else {
            $db->update_data("pap_delivery", "id", $_POST['did'][$i], array("status"=>80)); //80 = มีใบแจ้งหนี้
        }
    }
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_receipt"){
    //add receipt
    $rcno = $db->check_rc_code($_POST['date']);
    $arrdata = array(null,$rcno,$_POST['uid'],$_POST['date'],$_POST['payment']);
    if($_POST['payment']=="รับชำระเป็นเช็ค"){
        array_push($arrdata,$_POST['cbank'],$_POST['branch'],$_POST['cno'],$_POST['cdate'],"","","",$_POST['remark']);
    } else if($_POST['payment']=="รับชำระด้วยวิธีการโอนเงิน"){
        array_push($arrdata,"","","","","",$_POST['tbank'],$_POST['tref'],$_POST['remark']);
    } else {
        array_push($arrdata,"","","","",$_POST['cash'],"","",$_POST['remark']);
    }
    $rcid = $db->insert_data("pap_rc", $arrdata);
    //add_detail
    for($i=0;$i<count($_POST['pay']);$i++){
        //add rc dt
        $ivid = $_POST['ivid'][$i];
        $paid = $_POST['pay'][$i];
        $db->insert_data("pap_rc_dt", array($rcid,$ivid,$paid));
        //update job paid
        $adid = $db->get_mm_arr("pap_invoice_dt", "deli_id", "invoice_id", $ivid);
        update_job_paid($adid);
    }
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_receipt"){
    $rcid = filter_input(INPUT_POST,'rcid',FILTER_SANITIZE_NUMBER_INT);
    $arrinfo = array(
        "user_id" => $_POST['uid'],
        "date" => $_POST['date'],
        "payment" => $_POST['payment'],
        "check_bank" => $_POST['cbank'],
        "check_bank_branch" => $_POST['branch'],
        "check_no" => $_POST['cno'],
        "check_date" => $_POST['cdate'],
        "cash_remark" => $_POST['cash'],
        "transfer_bank" => $_POST['tbank'],
        "transfer_ref" => $_POST['tref'],
        "remark" => $_POST['remark']
    );
    $db->update_data("pap_rc", "id", $rcid, $arrinfo);
    //del old detail
    $db->delete_data("pap_rc_dt", "rc_id", $rcid);
    //add_detail
    for($i=0;$i<count($_POST['pay']);$i++){
        //add rc dt
        $ivid = $_POST['ivid'][$i];
        $paid = $_POST['pay'][$i];
        $db->insert_data("pap_rc_dt", array($rcid,$ivid,$paid));
        //update job paid
        $adid = $db->get_mm_arr("pap_invoice_dt", "deli_id", "invoice_id", $ivid);
        update_job_paid($adid);
    }
    $_SESSION['message'] = "แก้ไขข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="update_po_paid"){
    $db->update_data($_POST['table'], "po_id", $_POST['poid'], array("po_paid"=>$_POST['date'],"po_paid_ref"=>$_POST['ref']));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="add_cpro"){
    $arrinfo = array(
        "process_id" => $_POST['process'],
        "name" => $_POST['name'],
        "volume" => $_POST['amount'],
        "est_time_hour" => $_POST['prodtime']
    );
    $db->insert_data("pap_comp_process", array(null,$_POST['compid'],$_POST['process'],$_POST['name'],$_POST['amount'],$_POST['prodtime'],null,null,null,null,null,null,null));
    $_SESSION['message'] = "เพิ่มข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_cpro"){
    if($_POST['leveling']>0){
        $lv = $_POST['leveling'];
        $info = $db->get_info("pap_comp_process", "id", $_POST['cproid']);
        //insert new process
        $aname = explode(",",$info['name']);
        if($aname[0]>0){
            $aname[0] = $aname[0]/$lv;
            $nname = implode(",",$aname);
        } else {
            $nname = $aname[0];
        }
        for($i=0;$i<$lv;$i++){
            $db->insert_data("pap_comp_process", array(null,$info['comp_id'],$info['process_id'],$nname,$info['volume']/$lv,$info['est_time_hour']/$lv,null,null,null,null,null,null,null));
        }
        //del ori
        $db->delete_data("pap_comp_process", "id", $_POST['cproid']);
    } else {
        $arrinfo = array(
            "process_id" => $_POST['process'],
            "name" => $_POST['name'],
            "volume" => $_POST['amount'],
            "est_time_hour" => $_POST['prodtime']
        );
        $db->update_data("pap_comp_process", "id", $_POST['cproid'], $arrinfo);
    }
    $_SESSION['message'] = "ปรับข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
} else if($req=="edit_job_result"){
    if($_POST['type']=="start"){
        $arrinfo = array(
            "start" => $_POST['stdate']." ".$_POST['timeh'].":".$_POST['timem'].":00",
        );
    } else {
        $arrinfo = array(
            "end" => $_POST['endate']." ".$_POST['timeh'].":".$_POST['timem'].":00",
            "result" => $_POST['result'],
            "remark" => $_POST['remark']
        );
        //update comp status
        $info = $db->get_info("pap_comp_process", "id", $_POST['cproid']);
        $compid = $info['comp_id'];
        $pinfo = $db->get_info("pap_process","process_id",$info['process_id']);
        $comp_status = $pinfo['process_cat_id'];
        $db->update_data("pap_order_comp", "id", $compid, array("status"=>$comp_status));
        //update job status
        $cinfo = $db->get_info("pap_order_comp", "id", $compid);
        update_job_status($cinfo['order_id']);
    }
    $db->update_data("pap_comp_process", "id", $_POST['cproid'], $arrinfo);
    $_SESSION['message'] = "ปรับข้อมูลสำเร็จ";
    header("Location:".$_POST['redirect']);
}
function update_job_status($oid){
    global $db;
    $max = $db->get_max_comps_status($oid);
    $last = $db->get_last_comps_status($oid);
    if($max==$last||$max>=11){
        $jstatus = 69;
        $finished = pap_now();
    } else if($max>3){
        $jstatus = 19;
        $finished = null;
    } else {
        $jstatus = 9;
        $finished = null;
    }
    $db->update_data("pap_order", "order_id", $oid, array("status"=>$jstatus,"prod_finished"=>$finished));
}
function update_job_paid($adid){
    global $db;
    include_once("pdo/pdo_ac.php");
    $pdo_ac = new pdo_ac();
    foreach($adid as $key=>$did){
        $info = $pdo_ac->check_job_paid($did);
        foreach($info as $k=>$v){
            $paid_before_tax = $v['paid'];
            $opaid = $paid_before_tax*$v['price']/$v['total'];
            $db->update_data("pap_order", "order_id", $v['oid'], array("paid"=>$opaid));
            $paid = $paid_before_tax;
            $tt = $v['total']-$v['ivdiscount'];
        }
        if($paid==$tt){
            //update deli status
            $db->update_data("pap_delivery", "id", $did, array("status"=>99)); //99 = ชำระครบ
        } else {
            $db->update_data("pap_delivery", "id", $did, array("status"=>98)); //99 = ชำระบางส่วน
        }
    }
}
