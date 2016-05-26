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
        [class*='lay-c-'],
        [class*='lay-o-']{
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
/*--------------------------------------------------------------  ADD NEW ----------------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //add
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size'");
    $paper_info = $db->get_paper_size();
    $pinfo = json_encode(array_values($paper_info));
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $grip = (float)$cinfo['grip_size'];
    $bleed = (float)$cinfo['bleed_size'];
    $normal = "<div class='left-50'>"
            . $form->show_select("cover_paper",$paper_size,"label-3070","ปก",null)
            . $form->show_select("cover_div",$op_paper_div,"label-3070","ผ่า",null)
            . $form->show_num("cover_lay","",1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>"
            . "<div class='right-50'>"
            . $form->show_select("inside_paper",$paper_size,"label-3070","เนื้อ",null)
            . $form->show_select("inside_div",$op_paper_div,"label-3070","ผ่า",null)
            . $form->show_num("inside_lay","",1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>";
    $ctype = $op_comp_type;
    unset($ctype[1],$ctype[2],$ctype[3]);
    $custom = "";
    for($i=0;$i<count($ctype);$i++){
        $hid = ($i==0?"":"form-hide");
        $custom .= "<div class='tab-section cus-lay $hid'>"
            . $form->show_select("custom_type_$i",$ctype,"label-3070","ส่วนประกอบ",null,"","ctype[]")
            . $form->show_select("custom_paper_$i",$paper_size,"label-3070","ขนาดกระดาษ",null,"","cpaper[]")
            . $form->show_select("custom_div_$i",$op_paper_div,"label-3070","ผ่า",null,"","cdiv[]")
            . $form->show_num("custom_lay_$i","",1,"","Lay","(หน้า/กรอบ)","label-3070","min=1","clay[]")
            . "</div>";
    }
    $custom .= "<input id='view-more-but' type='button' value='เพิ่ม Custom Lay' style='width:100%'/>";
    
    $content .= "<h1 class='page-title'>เพิ่มขนาดชิ้นงาน และการ Lay</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='cheight'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name","","เช่น A4 29.5x21.0 cm","ขนาดชิ้นงาน","","label-3070")
            . $form->show_num("height","",0.01,"","ความสูงชิ้นงาน(cm)","","lay-input label-3070")
            . $form->show_num("width","",0.01,"","ความกว้างชิ้นงาน(cm)","","lay-input label-3070")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<h4>เลือกกระดาษ</h4>"
            . $form->show_tabs("lay-tab", array("ปกและเนื้อ","Custom"), array($normal,$custom))
            . $form->show_submit("submit","Add New","but-right")
            . "</div><!-- .col-50 -->"
            . "</div><!-- .cheight -->";
    //lay guide
    $content .= "<div id='lay-guide' class='col-50'>"
            . "<h3>Lay Guide</h3>"
            . $form->show_num("cover_thick",1,0.01,"","สันปก+ปีก (cm)","","label-3070")
            . $form->show_num("grip1",$grip,0.01,"","ขนาดกริบ1 (cm)","","label-3070")
            . $form->show_num("grip2",0,0.01,"","ขนาดกริบ2 (cm)","","label-3070")
            . show_layguide($form,$paper_info,$grip);
            
    $content .= $form->show_hidden("request","request","add_job_size")
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name','height','width','cover_lay','inside_lay'));
    $content .= $form->submitscript("$('#lay').submit();")
            . "<script>"
            . "lay_guide($pinfo,$grip,$bleed,0);"
            . "view_more_section('cus-lay');"
            . "</script>";
} else if(isset($sid)) {
/*--------------------------------------------------------------  EDIT ----------------------------------------------------------*/
    //check
    if($pauth<=1){
        header("location:$redirect");
        exit();
    }
    //load
    $info = $db->get_info("pap_size","size_id",$sid)+$db->get_meta("pap_size_meta", "size_id", $sid);
    if(isset($info['custom_lay'])){
        $cuslay = json_decode($info['custom_lay'],true);
    } else {
        $cuslay = array();
    }
    //edit
    $paper_size = $db->get_keypair("pap_option", "op_id", "op_name","WHERE op_type='paper_size'");
    $paper_info = $db->get_paper_size();
    $pinfo = json_encode(array_values($paper_info));
    $cinfo = $db->get_keypair("pap_option", "op_name", "op_value", "WHERE op_type='cinfo'");
    $grip1 = (isset($info['grip1'])?$info['grip1']:(float)$cinfo['grip_size']);
    $grip2 = (isset($info['grip2'])?$info['grip2']:0);
    $bleed = (float)$cinfo['bleed_size'];
    $normal = "<div class='left-50'>"
            . $form->show_select("cover_paper",$paper_size,"label-3070","ปก",$info['cover_paper'])
            . $form->show_select("cover_div",$op_paper_div,"label-3070","ผ่า",$info['cover_div'])
            . $form->show_num("cover_lay",$info['cover_lay'],1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>"
            . "<div class='right-50'>"
            . $form->show_select("inside_paper",$paper_size,"label-3070","เนื้อ",$info['inside_paper'])
            . $form->show_select("inside_div",$op_paper_div,"label-3070","ผ่า",$info['inside_div'])
            . $form->show_num("inside_lay",$info['inside_lay'],1,"","Lay","(หน้า/กรอบ)","label-3070")
            . "</div>";
    $ctype = $op_comp_type;
    unset($ctype[1],$ctype[2],$ctype[3]);
    $custom = "";
    for($i=0;$i<count($ctype);$i++){
        if(isset($cuslay[$i])){
            $custom .= "<div class='tab-section cus-lay'>"
                . $form->show_select("custom_type_$i",$ctype,"label-3070","ส่วนประกอบ",$cuslay[$i][0],"","ctype[]")
                . $form->show_select("custom_paper_$i",$paper_size,"label-3070","ขนาดกระดาษ",$cuslay[$i][1],"","cpaper[]")
                . $form->show_select("custom_div_$i",$op_paper_div,"label-3070","ผ่า",$cuslay[$i][2],"","cdiv[]")
                . $form->show_num("custom_lay_$i",$cuslay[$i][3],1,"","Lay","(หน้า/กรอบ)","label-3070","min=1","clay[]")
                . "</div>";
        } else {
            $hid = ($i==0?"":"form-hide");
            $custom .= "<div class='tab-section cus-lay $hid'>"
                . $form->show_select("custom_type_$i",$ctype,"label-3070","ส่วนประกอบ",null,"","ctype[]")
                . $form->show_select("custom_paper_$i",$paper_size,"label-3070","ขนาดกระดาษ",null,"","cpaper[]")
                . $form->show_select("custom_div_$i",$op_paper_div,"label-3070","ผ่า",null,"","cdiv[]")
                . $form->show_num("custom_lay_$i","",1,"","Lay","(หน้า/กรอบ)","label-3070","min=1","clay[]")
                . "</div>";
        }
    }
    $custom .= "<input id='view-more-but' type='button' value='เพิ่ม Custom Lay' style='width:100%'/>";
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
            . $form->show_tabs("lay-tab", array("ปกและเนื้อ","Custom"), array($normal,$custom))
            . $form->show_submit("submit","Update","but-right")
            . "</div><!-- .col-50 -->"
            . "</div><!-- .cheight -->";

    //lay guide
    $content .= "<div id='lay-guide' class='col-50'>"
            . "<h3>Lay Guide</h3>"
            . $form->show_num("cover_thick",$info['cover_thick'],0.01,"","สันปก+ปีก (cm)","","label-3070")
            . $form->show_num("grip1",$grip1,0.01,"","ขนาดกริบ1 (cm)","","label-3070")
            . $form->show_num("grip2",$grip2,0.01,"","ขนาดกริบ2 (cm)","","label-3070")
            . show_layguide($form,$paper_info);
    $content .= $form->show_hidden("request","request","edit_job_size")
            . $form->show_hidden("sid","sid",$sid)
            . $form->show_hidden("redirect","redirect",$redirect);
    $form->addformvalidate("ez-msg", array('name','height','width','cover_lay','inside_lay'));
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "lay_guide($pinfo,$grip1,$bleed,$grip2);"
            . "view_more_section('cus-lay');"
            . "</script>";
} else {
/*--------------------------------------------------------------  VIEW ----------------------------------------------------------*/
    __autoload("pdo_tb");
    $tbpdo = new tbPDO();
    $tb = new mytable();
    
    $s = (isset($_GET['s'])&&$_GET['s']!=""?$_GET['s']:null);
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $iperpage = 20;
    
    //view
    $head = array("แก้ไข","ชื่อ","ขนาด(กว้างxสูง)","กระดาษปก","ปกเลย์","กระดาษเนื้อ","เนื้อเลย์");
    $all_rec = $tbpdo->view_jobsize($pauth,$s);
    $rec = $tbpdo->view_jobsize($pauth,$s,$page,$iperpage);
    $max = ceil(count($all_rec)/$iperpage);
    $addhtml = "";
    if($pauth>1){
        $add = $redirect."?action=add";
        $addhtml = "<a class='add-new' href='$add' title='Add New'>Add New</a>";
    }
   
    $content .= "<h1 class='page-title'>ขนาดชิ้นงาน $addhtml</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . "<div class='col-100'>"
            . $tb->show_search(current_url(), "cusid", "s","ค้นหา",$s)
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"tb-layguide")
            . "</div><!-- .col-100 -->";
}
    
$content .= $menu->showfooter();
echo $content;

function show_layguide($form,$pinfo){
    global $op_paper_div;
    $html = "<div id='tb-lay' class='ez-table'><table>"
            . "<tr class='tb-head'><th></th><th>ผ่าก่อนพิมพ์</th><th>ปก</th><th>เนื้อ</th><th>Custom</th></tr>"
            . "<tr>"
            . "<th>ขนาดงาน</th><td></td><td class='size-cover'></td><td class='size-inside'></td>"
            . "<td class='size-custom'><a href='' title='Custom Size' class='icon-page-edit'></a><br/><span></span></td>"
            . "</tr>"
            . "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='4'>เลย์แนวตั้ง</th></tr>";
    foreach($pinfo as $k=>$v){
        $wh = json_decode($v['psize'],true);
        $wcm = $wh['width']*2.54;
        $hcm = $wh['length']*2.54;
        $whcm = $wcm."x".$hcm;
        $html .= "<tr class='tb-data'>"
                . "<th>".$v['op_name']."\"<br/>$whcm cm</th>"
                . "<td>".$form->show_select("pdiv", $op_paper_div, "label-inline pdiv", null, null,null,"pdiv[]")."</td>"
                . "<td class='lay-box-c'>"
                . "<span class='lay-cover'></span><br/>"
                . "<span class='lay-c-rem'></span>"
                . "</td>"
                . "<td class='lay-box-i'>"
                . "<span class='lay-inside'></span><br/>"
                . "<span class='lay-i-rem'></span>"
                . "</td>"
                . "<td class='lay-box-o'>"
                . "<span class='lay-custom'></span><br/>"
                . "<span class='lay-o-rem'></span>"
                . "</td>"
                . "</tr>";
    }
    $html .= "<tr class='tb-guide-row'><th>กระดาษ</th><th colspan='4'>เลย์แนวนอน</th></tr>";
    foreach($pinfo as $k=>$v){
        $html .= "<tr class='tb-data'>"
                . "<th>".$v['op_name']."\"</th>"
                . "<td></td>"
                . "<td class='lay-box-cr'>"
                . "<span class='lay-cover-r'></span><br/>"
                . "<span class='lay-c-rem-r'></span>"
                . "</td>"
                . "<td class='lay-box-ir'>"
                . "<span class='lay-inside-r'></span><br/>"
                . "<span class='lay-i-rem-r'></span>"
                . "</td>"
                . "<td class='lay-box-or'>"
                . "<span class='lay-custom-r'></span><br/>"
                . "<span class='lay-o-rem-r'></span>"
                . "</td>"
                . "</tr>";
    }
    $html .= "</table></div><!-- .ez-table -->"
            . "<div class='tb-remark'>"
            . "<p>** = % กระดาษเหลือ</p>"
            . "</div><!-- .tb-remark -->"
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div id='show-lay-cover'>"
            . "<h4></h4>"
            . "<div id='lay-sel'></div>"
            . "<div id='show-lay'></div>"
            . "</div><!-- #show-lay-cover -->"
            . "</div><!-- .col-50 -->";
    //custom size box
    $box = "<h4>กำหนดขนาด Custom</h4>"
            . "<div id='box-msg'></div>"
            . $form->show_num("c_height","",0.01,"","สูง(cm)","","label-3070")
            . $form->show_num("c_width","",0.01,"","กว้าง(cm)","","label-3070")
            . $form->show_button('edit-custom','กำหนดขนาด',"","")
            . "<script>inputenter(['c_height','c_width'],'edit-custom');</script>";

    $html .= $form->show_float_box($box,"custom-size");
    return $html;
}
