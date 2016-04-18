<?php
session_start();
include_once(dirname(dirname(__FILE__))."/p-admin/myfunction.php");
include_once("p-option.php");
$pauth = page_auth(basename(current_url()));
check_auth($pauth);
$root = PAP;
$redirect = $root.basename(__FILE__);
__autoload("papmenu");
__autoload("pappdo");
$db = new PAPdb(DB_PAP);
$menu = new PAPmenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->pap_menu();
$menu->pageTitle = "PAP | Lay";
$menu->ascript[] = $root."js/pap.js";
$menu->astyle[] = $root."css/lay.css";
$menu->extrascript = <<<END_OF_TEXT
<style>
        .tb-guide-row {
            background-color:#aed581 !important;
        }
        #tb-lay tr th:nth-child(2),
        #tb-lay tr td:nth-child(2) {
            border-right:1px solid rgb(200,200,200);
            border-left:1px solid rgb(200,200,200);
        }
        .tb-remark {
            font-size:14px;
        }
        [class*='lay-i-'],
        [class*='lay-c-']{
            font-size:12px;
            line-height:2em;
        }
</style>
END_OF_TEXT;

$content = $menu->showhead();
$content .= $menu->pappanel("เสนอราคา","คำนวณการ Lay");

$form = new myform("lay","",PAP."request.php");
$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$sid = filter_input(INPUT_GET,'sid',FILTER_SANITIZE_STRING);
if($action=="add"){
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size'");
    $paper_info = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='paper_size'");
    $pinfo = json_encode(array_values($paper_info));
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $grip = (float)$cinfo['grip_size'];
    $bleed = (float)$cinfo['bleed_size'];
    $content .= "<h1 class='page-title'>เพิ่มขนาดชิ้นงาน และการ Lay</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='cheight'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name","","เช่น A4 29.5x21.0 cm","ขนาดชิ้งาน","","label-3070")
            . $form->show_num("height","",0.01,"","ความสูงชิ้นงาน(cm)","","lay-input label-3070")
            . $form->show_num("width","",0.01,"","ความกว้างชิ้นงาน(cm)","","lay-input label-3070")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<h4>เลือกกระดาษ</h4>"
            . "<div class='left-50'>"
            . $form->show_select("cover_paper",$paper_size,"label-3070","ปก",null)
            . $form->show_select("cover_div",$op_paper_div,"label-3070","ผ่า",null)
            . $form->show_num("cover_lay","",1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>"
            . "<div class='right-50'>"
            . $form->show_select("inside_paper",$paper_size,"label-3070","เนื้อ",null)
            . $form->show_select("inside_div",$op_paper_div,"label-3070","ผ่า",null)
            . $form->show_num("inside_lay","",1,"","Lay","(หน้า/กรอบ)","label-3070")
            . $form->show_submit("submit","Add New","but-right")
            . "</div>"
            . "</div><!-- .col-50 -->"
            . "</div><!-- .cheight -->";

    
    //lay guide
    $content .= "<div id='lay-guide' class='col-50'>"
            . "<h3>Lay Guide</h3>"
            . $form->show_num("cover_thick",1,0.01,"","สันปก+ปีก (cm)","","label-3070")
            . "<div id='tb-lay' class='ez-table'><table>"
            . "<tr class='tb-head'><th></th><th>ผ่าก่อนพิมพ์</th><th>ปก</th><th>เนื้อ</th></tr>"
            . "<tr><th>ขนาดงาน</th><td></td><td class='size-cover'></td><td class='size-inside'></td></tr>"
            . "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='3'>เลย์แนวตั้ง</th></tr>";

    foreach($paper_info as $k=>$v){
        $wh = json_decode($v,true);
        $wcm = $wh['width']*2.54-$grip;
        $hcm = $wh['length']*2.54;
        $whcm = $wcm."x".$hcm;
        $content .= "<tr class='tb-data'>"
                . "<th>$k\"<br/>$whcm cm*</th>"
                . "<td>".$form->show_select("pdiv", $op_paper_div, "label-inline pdiv", null, null,null,"pdiv[]")."</td>"
                . "<td class='lay-box-c'>"
                . "<span class='lay-cover'></span><br/>"
                . "<span class='lay-c-rem'></span>"
                . "</td>"
                . "<td class='lay-box-i'>"
                . "<span class='lay-inside'></span><br/>"
                . "<span class='lay-i-rem'></span>"
                . "</td>"
                . "</tr>";
    }
    $content .= "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='3'>เลย์แนวนอน</th></tr>";
    foreach($paper_info as $k=>$v){
        $content .= "<tr class='tb-data'>"
                . "<th>$k\"</th>"
                . "<td></td>"
                . "<td class='lay-box-cr'>"
                . "<span class='lay-cover-r'></span><br/>"
                . "<span class='lay-c-rem-r'></span>"
                . "</td>"
                . "<td class='lay-box-ir'>"
                . "<span class='lay-inside-r'></span><br/>"
                . "<span class='lay-i-rem-r'></span>"
                . "</td>"
                . "</tr>";
    }
    $content .= "</table></div><!-- .ez-table -->"
            . "<div class='tb-remark'>"
            . "<p>* = ขนาดกระดาษเป็น cm หัก Grip $grip cm</p>"
            . "<p>** = % กระดาษเหลือ</p>"
            . "</div>"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div id='show-lay-cover'><div id='show-lay'></div></div>"
            . "</div><!-- .col-50 -->"
            . "<script>lay_guide($pinfo,$grip,$bleed);</script>";

    $content .= $form->show_hidden("request","request","add_job_size")
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name','height','width','cover_lay','inside_lay'));
    $content .= $form->submitscript("$('#lay').submit();");
} else if(isset($sid)) {
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_size","size_id",$sid);
    //edit
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size'");
    $paper_info = $db->get_keypair("pap_option", "op_name", "op_value","WHERE op_type='paper_size'");
    $pinfo = json_encode(array_values($paper_info));
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $grip = (float)$cinfo['grip_size'];
    $bleed = (float)$cinfo['bleed_size'];
    $content .= "<h1 class='page-title'>แก้ไขการ Lay</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='cheight'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name",$info['size_name'],"เช่น A4 29.5x21.0 cm","ขนาดชิ้นงาน","","label-3070")
            . $form->show_num("height",$info['size_height'],0.01,"","ความสูงชิ้นงาน(cm)","","lay-input label-3070")
            . $form->show_num("width",$info['size_width'],0.01,"","ความกว้างชิ้นงาน(cm)","","lay-input label-3070")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<h4>เลือกกระดาษ</h4>"
            . "<div class='left-50'>"
            . $form->show_select("cover_paper",$paper_size,"label-3070","ปก",$info['cover_paper'])
            . $form->show_select("cover_div",$op_paper_div,"label-3070","ผ่า",$info['cover_div'])
            . $form->show_num("cover_lay",$info['cover_lay'],1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>"
            . "<div class='right-50'>"
            . $form->show_select("inside_paper",$paper_size,"label-3070","เนื้อ",$info['inside_paper'])
            . $form->show_select("inside_div",$op_paper_div,"label-3070","ผ่า",$info['inside_div'])
            . $form->show_num("inside_lay",$info['inside_lay'],1,"","Lay","(หน้า/กรอบ)","label-3070")
            . $form->show_submit("submit","Update","but-right")
            . "</div>"
            . "</div><!-- .col-50 -->"
            . "</div><!-- .cheight -->";

    //lay guide
    $content .= "<div id='lay-guide' class='col-50'>"
            . "<h3>Lay Guide</h3>"
            . $form->show_num("cover_thick",$info['cover_thick'],0.01,"","สันปก+ปีก (cm)","","label-3070")
            . "<div class='ez-table'><table>"
            . "<tr class='tb-head'><th></th><th>ผ่าก่อนพิมพ์</th><th>ปก</th><th>เนื้อ</th></tr>"
            . "<tr><th>ขนาดงาน</th><td></td><td class='size-cover'></td><td class='size-inside'></td></tr>"
            . "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='3'>เลย์แนวตั้ง</th></tr>";
    foreach($paper_info as $k=>$v){
        $wh = json_decode($v,true);
        $wcm = $wh['width']*2.54-$grip;
        $hcm = $wh['length']*2.54;
        $whcm = $wcm."x".$hcm;
        $content .= "<tr class='tb-data'>"
                . "<th>$k\"<br/>$whcm cm*</th>"
                . "<td>".$form->show_select("pdiv", $op_paper_div, "label-inline", null, null,null,"pdiv[]")."</td>"
                . "<td class='lay-box-c'>"
                . "<span class='lay-cover'></span><br/>"
                . "<span class='lay-c-rem'></span>"
                . "</td>"
                . "<td class='lay-box-i'>"
                . "<span class='lay-inside'></span><br/>"
                . "<span class='lay-i-rem'></span>"
                . "</td>"
                . "</tr>";
    }
    $content .= "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='3'>เลย์แนวนอน</th></tr>";
    foreach($paper_info as $k=>$v){
        $content .= "<tr class='tb-data'>"
                . "<th>$k\"</th>"
                . "<td></td>"
                . "<td class='lay-box-cr'>"
                . "<span class='lay-cover-r'></span><br/>"
                . "<span class='lay-c-rem-r'></span>"
                . "</td>"
                . "<td class='lay-box-ir'>"
                . "<span class='lay-inside-r'></span><br/>"
                . "<span class='lay-i-rem-r'></span>"
                . "</td>"
                . "</tr>";
    }
    $content .= "</table></div><!-- .ez-table -->"
            . "<div class='tb-remark'>"
            . "<p>* = ขนาดกระดาษเป็น cm หัก Grip $grip cm</p>"
            . "<p>** = % กระดาษเหลือ</p>"
            . "</div>"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div id='show-lay-cover'><div id='show-lay'></div></div>"
            . "</div>";
    $content .= $form->show_hidden("request","request","edit_job_size")
            . $form->show_hidden("sid","sid",$sid)
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name','height','width','cover_lay','inside_lay'));
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "lay_guide($pinfo,$grip,$bleed);"
            . "</script>";
} else {
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    //view
    $head = array("ชื่อ","ขนาด","กระดาษปก","ปกเลย์","กระดาษเนื้อ","เนื้อเลย์");
    $addhtml = "";
    if($pauth==1){
        $rec = $tbpdo->view_jobsize(1);
    } else {
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
        array_unshift($head, "แก้ไข");
        $rec = $tbpdo->view_jobsize();
    }
    
    $content .= "<h1 class='page-title'>ขนาดชิ้นงาน $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_table($head,$rec)
            . "</div><!-- .col-100 -->";
}
    
$content .= $menu->showfooter();
echo $content;

