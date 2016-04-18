<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
$root = PAP;
__autoload("papmenu");
__autoload("pappdo");
$redirect = $root.basename(__FILE__);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->pageTitle = "PAP (Printing & Administration Program)";
$menu->extrascript = <<<END_OF_TEXT
<style>
        body{
            padding:0;
            background-color:#00925e;
        }
        #content,footer {
            padding-left:0;
        }
        #login-box {
            width:300px;
            margin-left:auto;
            margin-right:auto;
            text-align:center;
            box-sizing:border-box;
            padding: 20px;
            box-shadow: 2px 3px 4px rgba(0,0,0,0.2);
            background-color: #f5f5f5;
            color: rgba(0,0,0,.84);
            margin-bottom:50px;
        }
        #img-logo {
            text-align:center;
            padding-bottom:25px;
            padding-top:20px;
        }
        #img-logo img{
            width:200px;
            height:auto;
        }
        a{
            text-decoration:none;
        }
        a:hover {
            text-decoration:underline;
        }
        #forget-pass {
            display:inline-block;
            margin-bottom:25px;
        }
        
        #back-tologin {
            display:inline-block;
            padding-top:10px;
        }
        h3 {
            position:relative;
            top:-25px;
        }
        @media only screen and (min-width: 376px) {
            #login-box {
                margin-top:15%;
            }
        }
        @media only screen and (min-width: 769px) {
            #login-box {
                margin-top:15%;
            }
        }
</style>
<script>
        $(document).ready(function(){
            $("#login").focus();
        });
</script>
END_OF_TEXT;
$menu->show_contact();
$content = $menu->showhead();
$content .= $menu->pappanel("", "", false);

$form = new myform("ulogin","",PAP."request.php");
$logo = PAP."image/pap_logo.jpg";
$fg = $root."login.php?f";
$login = $root."login.php";
$content .= $menu->contact
        . "<div id='login-box'>"
        . "<div id='img-logo'><img src='$logo' alt='PAP Logo' /></div>"
        . showmsg()
        . "<div class='float-box-inside'>";
$f = filter_input(INPUT_GET,'f',FILTER_SANITIZE_STRING);
if(isset($f)){
    //forget pass
    $content .= $form->show_st_form()
        . $form->show_text("email","email","","Email","","","label-inline","email")
        . $form->show_submit("login_submit","ขอเปลี่ยนรหัสผ่าน","but-100")
        . $form->show_hidden("request","request","pass-reset")
        . $form->show_hidden("error_direct","error_direct",$root."login.php")
        . $form->show_hidden("redirect","redirect",$root)
        . "<a href='$login' id='back-tologin' title='กลับไปหน้าเข้าสู่ระบบ'>กลับไปหน้าเข้าสู่ระบบ</a>";
$form->addformvalidate("float-msg",array('email'),null,'email');
$arrn = json_encode($form->array_name);
$content .= $form->submitscript("$('#ulogin').submit();")
        . "</div><!-- .float-box-inside -->"
        . "</div><!-- login-box -->";
} else if(isset($_GET['r'])){
    //reset pass
    $db = new PAPdb(DB_PAP);
    $rq = $db->checkr($_GET['r']);
    if(!is_numeric($rq)){
        $_SESSION['error']="การขอเปลี่ยนรหัสผ่านหมดอายุ";
        header("location:".$redirect);
        exit();
    } else {
        $content .= "<h3>ตั้งรหัสผ่านใหม่</h3>"
            . $form->show_st_form()
            . $form->show_text("pass","pass","","password","","","label-inline","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","","","label-inline","password"," maxlength='32'")
            . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_upass")
            . $form->show_hidden("uid","uid",$rq)
            . $form->show_hidden("redirect","redirect",$root."login.php");
        $form->addformvalidate("ez-msg", ['pass','repass'],['pass','repass']);
        $content .= $form->submitscript("$('#edit').submit();")
                . "<script>"
                . "pass_strength('pass','repass','pass-indicator');"
                . "</script>"
                . "</div><!-- .float-box-inside -->"
                . "</div><!-- login-box -->";
    }
} else {
    $content .= $form->show_st_form()
        . $form->show_text("login","login","","Username","","","label-inline")
        . $form->show_text("z","z","","Password","","","label-inline","password")
        . "<a href='$fg' id='forget-pass' title='Forget password'>ลืมรหัสผ่าน?</a>"
        . $form->show_submit("login_submit","Login","but-100")
        . $form->show_hidden("request","request","login")
        . $form->show_hidden("error_direct","error_direct",$root."login.php")
        . $form->show_hidden("redirect","redirect",(isset($_SESSION['referurl'])?$_SESSION['referurl']:$root));
$form->addformvalidate("float-msg",array('login','z'));
$arrn = json_encode($form->array_name);
$content .= $form->submitscript("$('#ulogin').submit();")
        . "</div><!-- .float-box-inside -->"
        . "<script>"
        . "inputenter(['z'],'login_submit');"
        . "</script>"
        . "</div><!-- login-box -->";
}
    
$content .= $menu->showfooter();
echo $content;

