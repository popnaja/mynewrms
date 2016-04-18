<?php
class mycalendar{
    private $st_day;
    private $year;
    private $month;
    private $data;
    private $adj;
    private $thm = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    private $thmonth = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    private $dayname = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    private $dayname_th = array("อาทิตย์","จันทร์","อังคาร","พุทธ","พฤหัสบดี","ศุกร์","เสาร์");
    public function __construct($year,$month,$adj) {
        $this->year = $year;
        $this->month = $month;
        $date = new DateTime("$year-$month-01",new DateTimeZone("Asia/Bangkok"));
        $sub = -$date->format("w");
        $this->tt = $date->format("t");
        $date->sub(new DateInterval("P".abs($sub)."D"));
        $this->st_day = $date;
        $this->adj = $adj;
    }
    public function show_calendar($data,$type="month",$script=""){
        $this->data = $data;   //data is array key=yyyy-mm-dd val=array(max,id,text)
        if($type=="day"){
            $cdbox = $this->my_schedule();
        } else {
            $cdbox = "<div class='mycd-box'>"
                . $this->my_dayname()
                . $this->my_calendar()
                . "</div><!-- .mycd-box -->";
        }
        $html = "<div class='mycd-container'>"
                . $this->mycd_nav($type)
                . $cdbox
                . "</div><!-- .mycd-container -->"
                . "<script>"
                . "mycd_height($this->adj);"
                . "$script"
                . "</script>";
        return $html;
    }
    private function mycd_nav($type){
        $next = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
        $next->add(new DateInterval("P1M"));
        $prev = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
        $prev->sub(new DateInterval("P1M"));
        $now_m = date_format(date_create(null,timezone_open("Asia/Bangkok")),"m");
        $now_y = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y");
        if($this->year==$now_y&&$this->month==$now_m){
            $tclass = "mycd-disable";
        } else {
            $tclass = "";
        }
        if($type=="month"){
            $month_class = "mycd-active";
            $day_class = "";
        } else {
            $month_class = "";
            $day_class = "mycd-active";
        }
        $thyear = $this->year+543;
        $show_month = $this->thmonth[$this->month-1]." พ.ศ. $thyear";
        $html = "<div class='mycd-menu'>"
                . "<div class='mycd-title'>$show_month</div>"
                . "<div class='mycd-nav'>"
                . "<span class='gray-but mycd-c-month $tclass' year='$now_y' month='$now_m' cdtype='$type'>Today</span>"
                . "<span class='gray-but mycd-c-month icon-chevron-left' year='".$prev->format("Y")."' month='".$prev->format("m")."' cdtype='$type'></span>"
                . "<span class='gray-but mycd-c-month icon-chevron-right' year='".$next->format("Y")."' month='".$next->format("m")."' cdtype='$type'></span>"
                . "</div><!-- . -->"
                . "<div class='mycd-switch'>"
                . "<span class='gray-but mycd-switch-type $day_class' cdtype='day' year='$this->year' month='$this->month'>DAY</span>"
                . "<span class='gray-but mycd-switch-type $month_class' cdtype='month' year='$this->year' month='$this->month'>MONTH</span>"
                . "</div>"
                . "</div><!-- .mycd-menu -->";
        return $html;
    }
    private function my_dayname(){
        $html = "<table class='mycd-daynames'>"
                . "<tr>";
        foreach ($this->dayname_th as $k=>$v){
            $html .= "<th>$v</th>";
        }
        $html .= "</tr>"
                . "</table>";
        return $html;
    }
    private function my_calendar(){
        $html = "<div class='mycd-calendar'>";
        for($i=0;$i<5;$i++){
            $top = $i*20;
            $html .= "<div class='month-row' style='height:21%;top:$top%;'>"
                    . $this->mycd_rundate()
                    . "</div><!-- .month-row -->";
        }
        $html .= "</div><!-- .mycd-calendar -->";
        return $html;
    }
    private function load_info($stdate,$max){
        $info = "";
        $stdate->sub(new DateInterval("P6D"));
        for($j=0;$j<$max;$j++){
            $info .= "<tr>";
            for($i=0;$i<7;$i++){
                $date = $stdate->format("Ymd");
                $dd = $stdate->format("d");
                if(isset($this->data[$date])){
                    if(isset(explode(",",$this->data[$date][1])[$j])){
                        $oid = explode(",",$this->data[$date][1])[$j];
                        $name = explode(",",$this->data[$date][2])[$j];
                        $info .= "<td class='mycd-rec' oid='$oid'>$name</td>";
                    } else {
                        $info .= "<td>&nbsp;</td>";
                    }
                } else if(isset($this->data[$dd])){
                    $oid = explode(",",$this->data[$dd][1])[$j];
                    $name = explode(",",$this->data[$dd][2])[$j];
                    $info .= "<td class='mycd-rec' oid='$oid'>$name</td>";
                } else {
                    $info .= "<td>&nbsp;</td>";
                }
                $stdate->add(new DateInterval("P1D"));
            }
            $info .= "</tr>";
            $stdate->sub(new DateInterval("P7D"));
        }
        return $info;
    }
    private function mycd_rundate(){
        $big = "<table class='mycd-bg-tb'>"
                . "<tr>";
        $html = "<table class='mycd-week-tb'>"
                . "<tr>";
        $max = 0;
        
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Ymd");
        for($i=0;$i<7;$i++){
            $date = $this->st_day->format("Ymd");
            $dd = $this->st_day->format("d");
            $dofm = $this->st_day->format("j");
            $month = $this->st_day->format("m");
            $tdate = new DateTime($this->st_day->format("Ymd"),new DateTimeZone("Asia/Bangkok"));
            //loop big tb
            if($date==$today){
                $bclass = "mycd-bg-today";
                $istoday = "mycd-today";
            } else {
                $bclass = "";
                $istoday = "";
            }
            $big .= "<td class='$bclass'>&nbsp;</td>";
            //check same month
            if($month!==$this->month){
                $ismonth = "mycd-gray";
            } else {
                $ismonth = "";
            }
            //check is 1st 
            $show = ($dofm==1?$dofm." ".$this->thm[$month-1]:$dofm);
            $html .= "<td class='mycd-dtitle $ismonth $istoday'>$show</td>";
            //check max data
            if(isset($this->data[$date])){
                $max = max($max,$this->data[$date][0]);
            } else if(isset($this->data[$dd])){
                $max = max($max,$this->data[$dd][0]);
            }
            $this->st_day->add(new DateInterval("P1D"));
        }
        $big .= "</tr>"
                . "</table>";
        $html .= "</tr>"
                . $this->load_info($tdate, $max)
                . "</table>";
        
        return $big.$html;
    }
    private function my_schedule(){
        $html = "<div class='mycd-schedule'>";
        $st = new DateTime("$this->year-$this->month-01",new DateTimeZone("Asia/Bangkok"));
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Ymd");
        for($i=0;$i<$this->tt;$i++){
            $day = $st->format("j");
            $dd = $st->format("d");
            $wday = $this->dayname_th[$st->format("w")];
            $date = $st->format("Ymd");
            $istoday = ($date==$today?"mycd-day-today":"");
            $html .= "<div class='mycd-day $istoday'>"
                    . "<div class='mycd-day-show'>"
                    . "<span>$day</span>"
                    . "<span>$wday</span>"
                    . "</div>"
                    . "<div class='mycd-day-rec'>";
            if(isset($this->data[$date])){
                $aoid = explode(",",$this->data[$date][1]);
                $aname = explode(",",$this->data[$date][2]);
                for($j=0;$j<count($aoid);$j++){
                    $html .= "<span class='mycd-rec' oid='$aoid[$j]'>$aname[$j]</span>";
                }
            } else if(isset($this->data[$dd])){
                $aoid = explode(",",$this->data[$dd][1]);
                $aname = explode(",",$this->data[$dd][2]);
                for($j=0;$j<count($aoid);$j++){
                    $html .= "<span class='mycd-rec' oid='$aoid[$j]'>$aname[$j]</span>";
                }
            }
            $html .= "</div>"
                    . "</div>";
            $st->add(new DateInterval("P1D"));
        }
        $html .="</div><!-- .mycd-schedule -->";
        return $html;
    }
}

