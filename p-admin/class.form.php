<?php
class myform{
    public $atext = array();
    public $atextarea = array();
    public $ahidden = array();
    public $abutton = array();
    public $formid = "";
    public $formclass = "";
    public $other = "";
    public $inputname = array();
    public $formvalscript = "";
    public $inputhidden = array();
    public $submitid = array();
    public $ajax_function_url = "";
    public $array_name = array();
    public $formval = array();
    private $req = "";
    
    public function __construct($id="",$class="",$req=null){
        $this->formid = $id;
        $this->formclass = $class;
        $this->req = (isset($req)?$req:AROOTS."request.php");
    }
    /*show start form incase form is complecate */
    public function show_st_form($action=null,$file=false,$novalidate=false){
        $file = ($file?"enctype='multipart/form-data'":"");
        $nov = ($novalidate?"novalidate":"");
        $url = (isset($action)?$action:$this->req);
        $form = "<div class='$this->formclass'>\n";
        $form .= "<form id='$this->formid' name='$this->formid' method='POST' action='$url' $file $nov>\n";
        return $form;
    }
    public function show_end_form(){
        $form = "</form>"
                . "</div><!-- .$this->formclass -->\n";
        return $form;
    }
    public function show_hidden($id,$name,$val){
        $this->array_name[$name]="hidden";
        $html = "<input type='hidden' id='$id' name='$name' value='$val'>";
        return $html;
    }
    public function show_text($id,$name,$val="",$placeholder = "",$label="",$des="",$classes="",$type=null,$ext=""){
        $this->array_name[$name] = "text";
        $codelabel = ($label==""?"":"<label for='$id'>$label</label>");
        $paragraph = ($des==""?"":"<p>$des</p>");
        $value = "value='$val'";
        $type = (isset($type)?$type:"text");
        $text = "<div class='$classes'>"
                . $codelabel
                . "<input type='$type' id='$id' name='$name' $value autocomplete='off' placeholder='$placeholder' $ext>"
                . $paragraph
                . "</div><!-- .$classes -->";
        return $text;
    }
    public function show_text_wbutton($id,$name,$val="",$placeholder = "",$label="",$des="",$classes="",$type=null,$ext="",$but=""){
        $this->array_name[$name] = "text";
        $codelabel = ($label==""?"":"<label for='$id'>$label</label>");
        $paragraph = ($des==""?"":"<p>$des</p>");
        $value = "value='$val'";
        $type = (isset($type)?$type:"text");
        $text = "<div class='$classes'>"
                . $codelabel
                . "<input type='$type' id='$id' name='$name' $value autocomplete='off' placeholder='$placeholder' $ext>"
                . $but
                . $paragraph
                . "</div><!-- .$classes -->";
        return $text;
    }
    public function show_num($id,$val="",$step=null,$place="",$label="",$des="",$classes="",$ext="min='0'",$name=null){
        $this->array_name[$id] = "number";
        $paragraph = ($des==""?"":"<p>$des</p>");
        $codelabel = ($label==""?"":"<label for='$id'>$label</label>");
        $html_step = (isset($step)?"step='$step'":"");
        $name_t = (isset($name)?$name:$id);
        $num = "<div class='$classes'>\n"
                . $codelabel
                . "<input type='number' id='$id' name='$name_t' value='$val' placeholder='$place' $html_step $ext>\n"
                . $paragraph
                . "</div><!-- .$classes -->";
        return $num;
    }
    public function show_textarea($id,$val="",$row=4,$col=10,$place="",$label="",$classes="",$name=null){
        $this->array_name[$id] = "textarea";
        $name_t = (isset($name)?$name:$id);
        $ta = "<div class='$classes'>"
                . "<label for='$id'>$label</label>"
                . "<textarea id='$id' name='$name_t' rows='$row' cols='$col' placeholder='$place'>"
                . "$val"
                . "</textarea>"
                . "</div><!-- .$classes -->";
        return $ta;
    }
    public function show_check($id,$name,$val="",$label="",$des="",$classes="",$ck=null,$left=true){
        $this->array_name[$name] = "checkbox";
        $paragraph = ($des==""?"":"<p>$des</p>");
        $codelabel = ($label==""?"":"<label for='$id'>$label</label>");
        $checked = ($ck==$val?" checked":"");
        $check = "<div class='$classes'>\n"
                . ($left?$codelabel:"")
                . "<input type='checkbox' id='$id' name='$name' value='$val' $checked>"
                . ($left?"":$codelabel)
                . $paragraph
                . "</div><!-- .$classes -->";
        return $check;
    }
    /*function to show checkbox from array */
    public function show_checkbox($id,$name,$arrayval,$show="",$class="") {
        $this->array_name[$name] = "checkbox";
        $html = "<div id='$id' class='$class'>\n"
                . "<span class='form-check-lb'>$show</span>"
                . "<ul>";
        $i = 0;
        foreach($arrayval as $k => $v){  //key=id, value=[show,check] 1=check
            $check = (is_array($v)?($v[1]===1?"checked":""):"");
            $html .="<li>"
                    . "<input type='checkbox' id='$name-$i' name='$name"."[]' value='$k' $check>"
                    . "<label for='$name-$i'>".(is_array($v)?$v[0]:$v)."</label>"
                    . "</li>";
            $i++;
        }
        $html .= "</ul>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_submit($id,$value,$class = ""){
        $this->submitid['submit'] = $id;
        $name = $id;
        $html = "<div class='$class'>"
                . "<input type='submit' name='$name' id='$name' value='$value'>\n"
                . "</div><!-- .$class -->";
        return $html;
    }
    public function show_button($id,$val,$class="",$onclick=""){
        $name = $id;
        $html = "<div class='$class'>"
                . "<input type='button' name='$name' id='$name' value='$val' onclick='$onclick'>\n"
                . "</div><!-- .$class -->";
        return $html;
    }
    public function show_smallcheck($id,$name,$label,$val,$class="",$inside){
        $this->array_name[$name] = "checkbox";
        $html = "<div class='noselect $class'>\n"
                . "<input type='checkbox' id='$id' name='$name' value='$val'>"
                . "<label for='$id'>$label</label>";
        $html .= "</div><!-- #id -->\n"
                . "<div id='inside-smcheck' class='form-hide'>$inside</div>"
                . "<script>\n"
                . "smcheck_showhid('$id');"
                . "</script>\n";
        return $html;
    }
    public function show_radio($id,$arrvaltext,$class="",$title="",$checked=null){
        $name = $id;
        $this->array_name[$name] = "radio";
        $html = "<div class='cheight $class'>"
                . "<div class='radio-title'>$title</div>";
        $i=0;
        foreach($arrvaltext as $val => $text){
            $check = ($val==$checked?"checked='checked'":"");
            $html .= "<div class='radio-list'>"
                    . "<input type='radio' name='$name' id='$id-$i' value='$val' $check>\n"
                    . "<label for='$id-$i' class='radio-label'>$text</label>\n"
                    . "</div>";
            $i++;
        }
        $html .= "</div>\n";
        return $html;
    }
    public function show_select($id,$avaltext,$class='',$show=null,$selected=null,$des="",$name=null){
        $this->array_name[$id] = "select";
        $label = (isset($show)?"<label for='$id'>$show</label>":"");
        $tn = (isset($name)?$name:$id);
        $html = "<div class='$class'>\n"
                . $label
                . "<select id='$id' name='$tn'>\n";
        foreach($avaltext as $k => $v){
            if($k==$selected){
                $html .= "<option value='$k' selected='selected'>$v</option>\n";
            } else {
                $html .= "<option value='$k'>$v</option>\n";
            }
        }        
        $html .= "</select>"
                . (strlen(trim($des))>0?"<p>$des</p>":"")
                . "</div>";
        return $html;
    }
    public function pg_select($id,$name,$avaltext,$class="",$show,$selected){
        $this->array_name[$id] = "text";
        $selected = (isset($selected)?$avaltext[$selected]:"Select");
        $html = "<div class='pg-select $class'>"
                . "<div>"
                . "<input type='text' name='$id' id='$id' class='pg-input-nostyle' value='$selected' readonly/>"
                . "</div>"
                . "<ul class='form-hide'>";
        foreach($avaltext AS $k=>$v){
            $html .= "<li pg-val='$k'>$v</li>";
        }
        $html .= "</ul>"
                . "</div>";
        return $html;
    }
    public function show_number($id,$name,$class,$label,$val=null){
        $this->array_name[$name] = "number";
        $html = "<div class='$class'>\n"
                . "<label for='$id'>$label</label>\n"
                . "<input type='number' id='$id' name='$name' value='$val' step='any'>"
                . "</div>";
        return $html;
    }
    public function arr_hour(){
        $res = [];
        for($i=0;$i<24;$i++){
            $time = $this->twodigitint($i);
            $res += [$time=>$time];
        }
        return $res;
    }
    public function arr_min(){
        $res = [];
        for($i=0;$i<60;$i++){
            $time = $this->twodigitint($i);
            $res += [$time=>$time];
        }
        return $res;
    }
    public function show_time($id,$timezone){
        date_default_timezone_set("UTC");
        $now = time()+$timezone*60*60;
        $year = date("Y",$now);
        $month = date("m",$now);
        $dofmonth = date("d",$now);
        $hour = date("H",$now);
        $min = date("i",$now);
        $html = "<div id='$id' class='timestamp-wrap'>";
        $html .= $this->selectmonth("mm",$month).",";
        $html .= $this->show_text("dd","dd",$dofmonth,2);
        $html .= $this->show_text("yy","yy",$year,4)."@";
        $html .= $this->show_text("hh","hh",$hour,2).":";
        $html .= $this->show_text("mn","mn",$min,2);
        $html .= "</div><!-- .timestamp-wrap -->";
        return $html;
    }
    public function current_time($timezone){
        return time()+$timezone*60*60;
    }
    private function selectmonth($id,$current){
        $html = "<select id='$id' name='$id'>";
        for($i=1;$i<13;$i++){
            $ni = $this->twodigitint($i);
            $selected = ($ni == $current ? " selected" : "");
            $html .= "<option value='$ni'$selected>$ni-".$this->monthstr($i,"M")."</option>";
        }
        $html .= "</select>";
        return $html;
    }
    private function twodigitint($i){
        return sprintf("%02d",$i);
    }
    private function monthstr($monthi,$format){
        return date($format, mktime(0,0,0,$monthi,10));
    }
    private function scriptFormValidate($arrayid,$type){
        $script = "";
        $formid = "";
        $check = "";
        if($type=="noblank"){
            $length = sizeof($arrayid,0);
            for($i=0;$i<$length;$i++){
                $formid = $arrayid[$i];           //
                $this->formvalscript .= "valNoBlank('$formid');\n";    //keep each check to formvalscipt and runfirst
                $script .= "(!valNoBlank('$formid'))";                   //join all check with or ||
                if($i!==$length-1){$script .= "||";}
            }
        } else if($type=='match'){
            $id1 = $arrayid[0];
            $id2 = $arrayid[1];
            $script = "(!valNoMatch('$id1','$id2'))";
        } else if($type=="nameok"){
            foreach($arrayid AS $k=>$v){
                $script .= "||(!nameOk('$v'))";
            }
        }
        return $script;
        
    }
    public function addformvalidate($msgid,$noblank,$arrmatch=null,$email=null,$nosel=null,$nameok=null,$anozero=null,$pregcheck=null){
        if(isset($arrmatch)){
            $match = "||".$this->scriptFormValidate($arrmatch, "match");
        } else {
            $match = "";
        }
        if(isset($email)){
            $email = "||(!valEmail('$email'))";
        } else {
            $email = "";
        }
        if(isset($nosel)){
            $nosele = (is_array($nosel)?json_encode($nosel):$nosel);
            $nosel = "||(!valSel($nosele))";
        } else {
            $nosel = "";
        }
        if(isset($nameok)){
            $ok = $this->scriptFormValidate($nameok, "nameok");
        } else {
            $ok = "";
        }
        if(isset($anozero)){
            $z = json_encode($anozero);
            $zero = "||(!valZero($z))";
        } else {
            $zero = "";
        }
        if(isset($pregcheck)){
            $p = json_encode($pregcheck);
            $preg = "||(!valPreg($p))";
        } else {
            $preg = "";
        }
        $temp = "if(";
        $butid = $this->submitid['submit'];
        $temp .= $this->scriptFormValidate($noblank, 'noblank')     
                . "$match$email$nosel$ok$zero$preg){"
                . "e.preventDefault();"
                . "show_submit_error('$msgid');"
                . "return false;"
                . "}";
        $this->formvalscript .= $temp;                                  //then put all if to formvalscript
    }
    public function submitscript($extra=""){
        $buttonid = $this->submitid['submit'];
        $script = "<script>\n"
                . "$('#$buttonid').on('click',function(e){\n"
                . "$this->formvalscript\n"
                . "$('.submit-error').remove();"
                . "$extra"
                . "});"
                . "</script>\n"
                . $this->show_end_form();
        
        return $script;
    }
    public function show_tabs($id,$arrtab,$arrdata,$active=0){
        $html = "<div id='$id' class='pg-tabs'>"
                . "<div class='pg-tab-list'>";
        foreach($arrtab AS $k=>$v){
            $act = ($k==$active?"pg-tab-active":"");
            $html .= "<a href='' title='$v' class='pg-tab-tab $act'>$v</a>";
        }
        $html .= "</div><!-- .pg-tab-list -->"
                . "<div class='pg-tab-data'>";
        foreach($arrdata AS $k=>$v){
            $act = ($k==$active?"":"form-hide");
            $html .= "<div class='pg-tab-item $act'>"
                    . $v
                    . "</div><!-- .pg-tab-item -->";
        }
        $html .= "</div><!-- .pg-tab-data -->"
                . "</div><!-- .pg-tabs -->"
                . "<script>pg_tab_act('$id');</script>";
        return $html;
    }
    public function show_require(){
        $html = "<span class='form-required'>*"
        . "<span class='des'>"
        . "<span class='des-arr-up'></span>"
        . "สำคัญต้องใส่"
        . "</span>"
        . "</span>";
        return $html;
    }
    public function checked_array($arrall,$arrchecked){
        foreach($arrall as $k=>$v){
            if(in_array($k,$arrchecked)){
                $arrall[$k] = array($v,1);
            } else {
                $arrall[$k] = array($v,0);
            }
        }
        return $arrall;
    }
    public function show_upload($id,$name,$label,$class){
        $html = "<div class='$class'>"
                . "<label for='$id'>$label</label>"
                . "<input type='file' id='$id' name='$name' accept='image/*;capture=camera'/>"
                . "</div><!-- .$class -->";
        return $html;
    }
    public function show_float_box($body,$id=""){
        $html = "<div class='graying'></div>"
                . "<div id='$id' class='my-float-box'>"
                . "<span class='my-box-close icon-delete-circle'></span>"
                . "$body"
                . "</div>";
        return $html;
    }
    public function my_toggle_tab($id,$label,$inside){
        $html = "<div id='$id'>"
                . "<div><span class='my-tab-top'>$label</span></div>"
                . "<div class='my-tab-inside form-hide'>"
                . $inside
                . "</div><!-- .my-tab-inside -->"
                . "</div><!-- #$id -->"
                . "<script>"
                . "my_tab_toggle('$id');"
                . "</script>";
        return $html;
    }
}
