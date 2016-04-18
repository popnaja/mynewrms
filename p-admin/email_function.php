<?php
function phpmail($address,$name,$url){
    $myemail = "contact@smartgreeny.com";
    $mysite = SITE;
    $subject = "$mysite | ขอเปลี่ยนรหัสผ่าน";
    $header = "From: ".$myemail."\r\n"
            . "Reply-To: ".$myemail."\r\n"
            . "MIME-Version: 1.0"."\r\n"
            . "Content-Type: text/html; charset=UTF-8"."\r\n";
    $mail = mail($address,$subject,email_ct($url),$header);
    if($mail){
        return true;
    } else {
        return false;
    }
}
function admin_alert_email($customeremail,$name,$tel,$msg){
    $myemail = "contact@smartgreeny.com";
    $admin = "resolute.ms@gmail.com";
    $subject = "$name contact ".SITE;
    $header = "From: ".$myemail."\r\n"
            . "Reply-To: ".$customeremail."\r\n"
            . "MIME-Version: 1.0"."\r\n"
            . "Content-Type: text/html; charset=UTF-8"."\r\n";
    $mail = mail($admin,$subject,alert_email($name,$customeremail,$tel,$msg),$header);
    if($mail){
        return true;
    } else {
        return false;
    }
}
function alert_w_netdesign($customeremail,$name,$tel,$msg){
    $admin = "resolute.ms@gmail.com";
    return php_mailer_netdesign($admin, alert_email($name,$customeremail,$tel,$msg),"มีลูกค้าติดต่อ");
}
function php_mailer_netdesign($address,$msg,$subject=null){
    require_once 'PHPMailer-master/PHPMailerAutoload.php';
    $myemail = "contact@resolutems.com";
    $body           = $msg;
    $mail           = new PHPMailer(true);
    $mail->CharSet = "UTF-8";
    $mail->IsSMTP();            // telling the class to us SMTP
    try {
        $mail->Host         = "mail.resolutems.com"; //SMTP server
        $mail->SMTPDebug    = 0;                    //enables SMTP debug information 0,1,2
        
        $mail->SMTPAuth     = true;                 // enable SMTP authentication
        $mail->Port         = 25;                   // set the SMTP port
        $mail->Username     = $myemail;             //Gmail username
        $mail->Password     = "Manage@emct007";         //Gmail password
        $mail->AddReplyTo($myemail,'contact');
        $mail->AddAddress($address,"admin");
        //$mail->AddCC("pop.pongsak@gmail.com");
        $mail->setFrom($myemail,'ResoluteMs.com');
        $mail->Subject      = "ResoluteMs.com | ".(isset($subject)?$subject:"");
        $mail->Altbody      = "To view the message, please use and HTML compatible email viewer.";
        $mail->MsgHTML($body);
        $mail->send();
        //echo "Mail sent OK.";
        return true;
    } catch (phpmailerException $e){
        //echo $e->getMessage();
        return false;
    } catch (Exception $ex) {
        //echo $ex->getMessage();
        return false;
    }
}
function php_mailer_ndh($to,$sender,$subject,$body){;
    require_once 'PHPMailer-master/PHPMailerAutoload.php';
    $myemail = "contact@resolutems.com";
    $mail           = new PHPMailer(true);
    $mail->CharSet = "UTF-8";
    $mail->IsSMTP();            // telling the class to us SMTP
    try {
        $mail->Host         = "mail.resolutems.com"; //SMTP server
        $mail->SMTPDebug    = 0;                    //enables SMTP debug information 0,1,2
        
        $mail->SMTPAuth     = true;                 // enable SMTP authentication
        $mail->Port         = 25;                   // set the SMTP port
        $mail->Username     = $myemail;             //Gmail username
        $mail->Password     = "Manage@emct007";         //Gmail password
        $mail->AddReplyTo($sender,'contact');
        foreach($to as $k=>$v){
            $mail->AddAddress($v,"");
        }
        //$mail->AddCC("pop.pongsak@gmail.com");
        $mail->setFrom($myemail,'resolutems.com');
        $mail->Subject      = $subject;
        $mail->Altbody      = "To view the message, please use and HTML compatible email viewer.";
        $mail->MsgHTML($body);
        $mail->send();
        //echo "Mail sent OK.";
        return true;
    } catch (phpmailerException $e){
        //echo $e->getMessage();
        return false;
    } catch (Exception $ex) {
        //echo $ex->getMessage();
        return false;
    }
}
function note_email($subject,$body){
    $html = <<<END_OF_TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>$subject</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
    <body>
        $body
    </body>
</html>      
END_OF_TEXT;
    return $html;
}
function alert_email($name,$email,$tel,$msg){
    $site = SITE;
    $root = ROOTS;
    $html = <<<END_OF_TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Customer Contact | $site</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
    </style>
    </head>
    <body>
        <table>
            <tr><!-- logo -->
                <td class="logo">
                    <h1 id='logo'>
                        <a href='$root' id='alogo' class='logo-mylogo' title='$site'>
                        $site
                        </a>
                    </h1>
                </td>
            </tr>
            <tr>
                <td>
                    <div id="content">
                        <div id="letter">
                            <p class="dear">Hi admin,</p>
                            <p class="inside">
                                Name : $name <br/>
                                Email : $email <br/>
                                Tel : $tel <br/>
                                Message : $msg
                            </p>
                            <p class='inside'>
                                Please note:<br/>
                            </p>
                            <p class='inside'>
                                <a href='$root' title='$site' target='_top'>$site</a>
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>      
END_OF_TEXT;
    return $html;
}
function email_ct($url){
    $root = ROOTS;
    $site = SITE;
    $html = <<<END_OF_TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Reset Password Request | CalForLife.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href='$root/css/email.css' rel='stylesheet' type='text/css'/>
    <style>
        * {margin:0;padding:0;font-family:'Open Sans';}
        h1 a {
            text-decoration:none;
        }
        table {
            width:100%;
            min-width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            margin-left:auto;
            margin-right:auto;
            border:1px #bbb solid;
        }
        table td {
            width:100%;
        }
        h1#logo {
            display:inline-block;
            float:left;
            width:100%;
            padding-left:10px;
            height:40px;
            box-sizing:border-box;
        }
        #alogo {
            display:inline-block;
            float:left;
            font-weight:600;
            font-size:21px !important;
            color:#1597ce !important;
            padding-right:15px;
            text-decoration:none;
            position:relative;
            top:3px;
        }
        #alogo img {
            height:30px;
            width:auto;
            position:relative;
            top:6px;
            left:5px;
        }
        #content {
            background-color:#f5f5f5;
            width:100%;
            height:627px;
            padding-top:30px;
        }
        #letter {
            height:450px;
            width:90%;
            margin-left:auto;
            margin-right:auto;
            background-color:#fff;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.13);
            box-sizing:border-box;
            padding:15px;
        }
        .dear {
            font-weight:500;
            margin-bottom:25px;
        }
        .inside {
            font-size:14px;
            margin-bottom:25px;
        }
        .inside a {
            color: #1597ce;
        }
        .inside a:visited {
            color: purple;
        }
    </style>
</head>
    <body>
        <table>
            <tr><!-- logo -->
                <td class="logo">
                    <h1 id='logo'>
                        <a href='$root' id='alogo' class='logo-mylogo' title='$site'>
                        $site
                        </a>
                    </h1>
                </td>
            </tr>
            <tr>
                <td>
                    <div id="content">
                        <div id="letter">
                            <p class="dear">เรียน คุณลูกค้า</p>
                            <p class="inside">
                                กรุณากดลิงค์ด้านล่างเพื่อดำเนินการเปลี่ยนรหัสผ่าน<br/>
END_OF_TEXT;
    $html .= "<a href='$url' title='Reset password link' target='_top'>$url</a>";
    $html .= <<<END_OF_TEXT
                            </p>
                            <p class='inside'>
                                Please note:<br/>
For security purposes, this link will expire 24 hours from the time it was sent.
If you cannot access this link, copy and paste the entire URL into your browser.
                            </p>
                            <p class='inside'>
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>      
END_OF_TEXT;
    return $html;
}
