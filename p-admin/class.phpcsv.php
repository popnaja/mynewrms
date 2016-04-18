<?php
class phpcsv{
    private $accept = array(
        ".csv" => "csv",
        "application/vnd.ms-excel" => ".xls",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => ".xlsx",
        "text/plain" => ".txt",
        "image/*" => "image",
        "video/*" => "video",
        "audio/*" => "audio",
        "text/html" => "html",
        ".pdf" => "pdf"
    );
    public function __construct() {

    }
    public function move_file($file,$dir){
        $name = $file["name"];
        $temp = $file["tmp_name"];
        if(is_thai($name)){
            $thfile = pathinfo($temp,PATHINFO_FILENAME).".".pathinfo($name,PATHINFO_EXTENSION);
            $targetfile = $dir.$thfile;
        } else {
            $targetfile = $dir.basename($name);
        }
        $ntargetfile = check_exist($targetfile);
        $move_res = move_uploaded_file($temp,$ntargetfile);
        if($move_res){
            return $ntargetfile;
        } else {
            return false;
        }
    }
    public function sel_csv_file($id,$name,$class=""){
        $html = "<div class='csv-sel $class'>"
                . "<input type='file' id='$id' name='$name' accept='.csv'>"
                . "</div><!-- .csv-sel -->";
        return $html;
    }
}

