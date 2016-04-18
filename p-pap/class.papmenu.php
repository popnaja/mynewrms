<?php
__autoloada("menu");
class PAPmenu extends mymenu {
    public $full_menu;
    public function __construct($lang = null) {
        parent::__construct($lang);
        $this->site = "PAP";
        $this->root = $root = PAP;
        $this->logo = PAP."image/pap_logo.jpg";
        $this->full_menu = array(
            "ลูกค้า" => array(
                "รายการลูกค้า" => $root."customer.php",
                "กลุ่มลูกค้า" => $root."term.php?tax=customer",
                "ที่อยู่จัดส่ง" => $root."shipping_address.php"
            ),
            "เสนอราคา" => array(
                "ใบเสนอราคา" => $root."quotation.php",
                "คำนวณการ Lay" => $root."lay.php"
            ),
            "บัญชี" => array(
                "ตารางวางบิลรับเช็ค" => $root."ac_schedule.php",
                "วางบิล/ใบเสร็จ/ใบกำกับ" => $root."ac_bill.php",
                "บัญชีสั่งซื้อ" => $root."ac_buy.php",
                "วงเงินลูกค้า" => $root."ac_credit.php"
            ),
            "ฝ่ายกราฟฟิก" => array(
                "ใบสั่งงาน" => $root."order.php?d=ga"
            ),
            "ฝ่ายผลิต" => array(
                "ใบสั่งงาน" => $root."order.php",
                "แผนการผลิต" => $root."production.php",
                "สถานะการผลิต" => $root."status.php",
                "จ้างผลิต" => $root."outsource.php",
                "ใบจ้างผลิต" => $root."outsource.php?action=viewpo",
                "ตารางส่งมอบ" => $root."index.php",
                "ใบส่งของ/แจ้งหนี้" => $root."delivery.php",
                "กระบวนการผลิต" => $root."process.php",
                "เครื่องจักร" => $root."machine.php"
            ),
            "ฝ่ายจัดซื้อ" => array(
                "สั่งกระดาษ" => $root."paper.php",
                "ใบสั่งซื้อวัตถุดิบ" => $root."paper.php?action=viewpo",
                "วัตถุดิบ" => $root."mat.php",
                "Supplier" => $root."supplier.php",
                "กลุ่มผู้ผลิต" => $root."term.php?tax=supplier"
            ),
            "ฝ่ายคลัง" => array(
                "รับวัตถุดิบ" => $root."mat_received.php",
                "รับสินค้าจ้างผลิต" => $root."outsource_rc.php",
            ),
            "ผู้ดูแลระบบ" => array(
                "ข้อมูลผู้ใช้" => $root."userinfo.php",
                "รายการผู้ใช้" => $root."user.php",
                "กลุ่มผู้ใช้" => $root."role.php",
                "กลุ่มสินค้า" => $root."pap_option.php?type=product_cat",
                "กลุ่มวัสดุ" => $root."pap_option.php?type=mat_cat",
                "กลุ่มกระบวนการ" => $root."process_cat.php",
                "เผื่อกระดาษเสีย" => $root."pap_option.php?type=paper_allo",
                "ชนิดกระดาษ" => $root."pap_option.php?type=paper_type",
                "ขนาดกระดาษ" => $root."pap_option.php?type=paper_size",
                "แกรมกระดาษ" => $root."pap_option.php?type=paper_weight",
                "ตั้งค่าระบบ" => $root."setting.php",
                "Upload" => $root."upload.php"
            )
        );
    }
    public function pap_menu(){
        $menu = $this->full_menu;
        $auth = $_SESSION['upap'][1];
        //filter menu
        function test_auth($auth){
            return $auth>0;
        }
        $menuv = array_filter($auth,"test_auth");
        //var_dump($menuv);
        $mauth = array_keys($menuv);
        //var_dump($mauth);
        //in case want absolute menu 
        //in_array(preg_replace("/\\?.*/","",basename($val)),$mauth); 
        foreach($menu as $k=>$v){
            if(is_array($v)){
                $tm[$k] = array_filter($v,function($val) use($mauth){
                    return in_array(basename($val),$mauth);
                });
                if(sizeof($tm[$k])==0){
                    unset($tm[$k]);
                }
            } else {
                if(in_array(basename($v),$mauth)){
                    $tm[$k] = $v;
                }
            }
        }
        $this->menu = $tm;
    }
    public function pappanel($active,$sub="",$visible=true){
        $root = $this->root;
        $logout = $this->root."logout.php";
        $uid = (isset($_SESSION['upap'])?$_SESSION['upap'][0]:0);
        $requrl = PAP."request.php";
        $rms_white = $this->aroot."image/resolutems_logo_white.png";
        if($visible){
            $panel = "<div id='top-panel' class='noselect'>"
                . "<div id='menu-mobile' class='icon-three-bars'></div>"
                . "<h1 id='logo'>PAP by </h1>"
                . "<div class='rms-logo'>"
                    . "<a href='http://www.resolutems.com/' title='Resolute MS'><img src='$rms_white'></a>"
                    . "</div><!-- .rms-logo -->"
                . "<a href='$logout' title='ออกจากระบบ' class='icon-logout'></a>"
                    . "<a href='$root' title='หน้าแรก' class='icon-home'></a>"
                
                . "</div><!-- #top-panel -->"
                . "<div id='panel'>"
                . "<ul id='mymenu'>"
                . $this->show_menuli($active,$sub)
                . "</ul>"
                . "</div><!-- #panel -->"
                . "<script>"
                . "show_mobilem();"
                //. "user_log('$uid','$requrl');"
                . "flex_menu();"
                . "</script>\n";
        } else {
            $panel = "";
        }
        $html = "<div id='wrapper' class='cheight'>\n"
                . $panel
                . "<div id='content' class='cheight'>"
                . $this->loading();
        return $html;
    }
}

