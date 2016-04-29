<?php
class mymedia {
    public $req;
    private $pics = 0;
    public function __construct($req=null) {
        $this->req = $req;
    }
    public function show_input($id,$name,$pic){
        $hide = ($this->pics==0?"":"form-hide");
        $html = "<div id='media-pic-box'>$pic</div>"
                . "<div class='md-input $hide'>"
                . "<div class='md-add'>"
                . "<span class='md-add-but'>+</span>"
                . "<input type='file' id='$id' name='$name' accept='image/*;capture=camera'>"
                . "</div>"
                . "<div class='md-des'>"
                . "Add a picture.<br/>"
                . "Maximum file size = 5 MB"
                . "</div><!-- .md-des -->"
                . "<p class='md-status-f'></p>"
                . "</div><!-- .md-input -->"
                . "<script>show_pic('$id','$this->req','single');</script>";
        return $html;
    }
    public function show_minput($id,$name,$pic){
        $html = "<div id='md-pics-box'>"
                . "<div class='md-minput'>"
                . "<div class='md-add'>"
                . "<span class='md-add-but'>+</span>"
                . "<input type='file' id='$id' name='$name' accept='image/*;capture=camera' multiple>"
                . "</div>"
                . "</div><!-- .md-minput -->"
                . "$pic"
                . "</div><!-- .md-pics-box -->"
                . "<script>show_pic('$id','$this->req','multi');</script>";
        return $html;
    }
    public function show_uppdf($id,$name,$file){
        $hid = ($file==""?"":"form-hide");
        $html = "<div id='media-file-box'>$file</div>"
                . "<div class='md-input $hid'>"
                . "<div class='md-add'>"
                . "<span class='md-add-but'>+</span>"
                . "<input type='file' id='$id' name='$name' accept='application/pdf,image/*;capture=camera'>"
                . "</div>"
                . "<div class='md-des'>"
                . "Add a pdf or picture.<br/>"
                . "Maximum file size = 5 MB"
                . "</div><!-- .md-des -->"
                . "<p class='md-status-f'></p>"
                . "</div><!-- .md-input -->"
                . "<script>show_pdf('$id','$this->req');</script>";
        return $html;
    }
    public function media_view($pic,$root,$dir){
        $html = "<div class='md-view'>";
        $pic_dir = $dir.$pic;
        if(strlen($pic)>0&&file_exists($pic_dir)){
            $pic_url = substr($root,0,-1).$pic;
            $html .= "<div class='md-pic'>"
                . "<a href='' title='Delete this picture' class='icon-delete-circle del-media-pic'></a>"
                . "<a href='' title='Zoom' class='icon-search zoom-media' imgsrc='$pic_url'></a>"
                . "<img src='$pic_url' alt=''/>"
                . "<input type='hidden' name='media' value='$pic' />"
                . "</div><!-- .md-pic -->";
            $this->pics++;
        } else {
            $html .= "";
        }
        $html .= "</div><!-- .md-view -->"
                . "<script>del_pic();zoom_pic();</script>";
        return $html;
    }
    public function media_mul_view($pics,$root,$dir){
        $html = "";
        for($i=0;$i<count($pics);$i++){
            $pic_dir = $dir.$pics[$i];
            if(strlen($pics[$i])>0&&file_exists($pic_dir)){
                $pic_url = substr($root,0,-1).$pics[$i];
                $html .= "<div class='md-m-pic'>"
                    . "<a href='' title='Delete this picture' class='icon-delete-circle del-media-pic'></a>"
                    . "<a href='' title='Zoom' class='icon-search zoom-media' imgsrc='$pic_url'></a>"
                    . "<img src='$pic_url' alt=''/>"
                    . "<input type='hidden' name='media[]' value='$pics[$i]' />"
                    . "</div><!-- .md-m-pic -->";
                $this->pics++;
            }
        }
        $html .= "<script>del_pic();zoom_pic();</script>";
        return $html;
    }
    public function media_mul_show($pics,$root,$dir){
        $html = "<div class='md-mul-view'>";
        for($i=0;$i<count($pics);$i++){
            $pic_dir = $dir.$pics[$i];
            if(strlen($pics[$i])>0&&file_exists($pic_dir)){
                $pic_url = substr($root,0,-1).$pics[$i];
                $html .= "<div class='md-pic-thumb'>"
                    . "<img src='$pic_url' alt=''/>"
                    . "</div><!-- .md-m-pic -->";
            }
        }
        $html .= "<script>show_big();</script>"
                . "</div><!-- .md-mul-view -->";
        return $html;
    }
    public function move_file($file,$des){
        if(file_exists($des)){
            chmod($des,0755);   //Change the file permissions if allowed
            unlink($des);       //remove the file
        }
        $res = rename($file, $des);
    }
    public function save_tmp_file($files,$dir){
        $num = count($files['name']);
        $res = array();
        /*create foloder if not exists */
        if(!file_exists($dir)) {
            mkdir($dir,0777,true);
        }
        for($i=0;$i<$num;$i++){
            $file = $files['name'][$i];
            $temp = $files['tmp_name'][$i];
            if(is_thai($file)){
                $thfile = pathinfo($temp,PATHINFO_FILENAME).".".pathinfo($file,PATHINFO_EXTENSION);
                $targetfile = $dir.$thfile;
            } else {
                $targetfile = $dir.basename($file);
            }
            $ntargetfile = check_exist($targetfile);
            $move_res = move_uploaded_file($temp,$ntargetfile);
            if($move_res == true){
                //rename ori_file from jpeg to jpg
                $o_ext = pathinfo($ntargetfile,PATHINFO_EXTENSION);
                if($o_ext=="jpeg"){
                    $path = pathinfo($ntargetfile,PATHINFO_DIRNAME)."/";
                    rename($ntargetfile,$path.pathinfo($ntargetfile,PATHINFO_FILENAME).".jpg");
                    $ntargetfile = str_replace(".jpeg",".jpg",$ntargetfile);
                }
                $sfile = $this->img_resize(1024, $ntargetfile,true); //true will delete original file
                //create_thumbnail(200, $ntargetfile);
                //logo_stamp($sfile,"../image/calforlife_logo.png");
                array_push($res,$sfile);
            }
        }
        return $res;
    }
    public function move_temp_file($files,$dir){
        $num = count($files['name']);
        $res = array();
        /*create foloder if not exists */
        if(!file_exists($dir)) {
            mkdir($dir,0777,true);
        }
        for($i=0;$i<$num;$i++){
            $file = $files['name'][$i];
            $temp = $files['tmp_name'][$i];
            if(is_thai($file)){
                $thfile = pathinfo($temp,PATHINFO_FILENAME).".".pathinfo($file,PATHINFO_EXTENSION);
                $targetfile = $dir.$thfile;
            } else {
                $targetfile = $dir.basename($file);
            }
            $ntargetfile = check_exist($targetfile);
            $move_res = move_uploaded_file($temp,$ntargetfile);
            if($move_res == true){
                array_push($res,$ntargetfile);
            }
        }
        return $res;
    }
    public function file_view($file,$root,$dir){
        if(strlen($file)==0||!file_exists($dir.$file)){
            $html = "";
        } else {
            $url = substr($root,0,-1).$file;
            $filename = pathinfo($file,PATHINFO_BASENAME);
            $ext = pathinfo($file,PATHINFO_EXTENSION);
            $html = "<div class='md-file-icon'>"
                    . "<a href='' title='Delete File' class='delete-md-file icon-delete-circle'></a>"
                    . "<input type='hidden' name='mdfile' value='$file' />"
                    . "<a href='$url' title='$filename' target='_blank'>";
            if($ext == "pdf"){
                $pdf = AROOTS."image/file_icon/file_icon_pdf.jpg";
                $html .= "<img src='$pdf' />";
            } else if($ext=="jpg"||$ext=="jpeg"||$ext=="png"){
                $img = AROOTS."image/file_icon/file_icon_image.jpg";
                $html .= "<img src='$img' />";
            }
            $html .= "</a>"
                    . "</div><!-- .md-file-icon -->"
                    . "<script>delete_md_file();</script>";
        }
        return $html;
    }
    private function create_thumbnail($n_w,$ori_file){
        $info = getimagesize($ori_file);
        list($w,$h) = $info;
        $mime = $info['mime'];
        switch($mime){
            case 'image/jpeg' :
                $img = imagecreatefromjpeg($ori_file);
                $img_save_func = "imagejpeg";
                $ext = "jpg";
                break;
            case 'image/png' :
                $img = imagecreatefrompng($ori_file);
                $img_save_func = "imagepng";
                $ext = "png";
                break;
            case 'image/gif' :
                $img = imagecreatefromgif($ori_file);
                $img_save_func = "imagegif";
                $ext = "gif";
                break;
            default :
                throw Exception('unknow image type.');
        }
        if($w >= $h){
            $sx = ($w-$h)/2;
            $sy = 0;
            $sh = $h;
            $sw = $h;
        } else {
            $sx = 0;
            $sy = ($h-$w)/2;
            $sh = $w;
            $sw = $w;
        }
        $tmp = imagecreatetruecolor($n_w,$n_w);
        imagecopyresampled($tmp,$img,0,0,$sx,$sy,$n_w,$n_w,$sw,$sh);
        //imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
        $path = pathinfo($ori_file,PATHINFO_DIRNAME)."/";
        $new_file = $path.pathinfo($ori_file,PATHINFO_FILENAME)."_thumb.".$ext;
        $img_save_func($tmp,$new_file);
        return $new_file;
    }
    private function img_resize($n_w,$ori_file,$del=false){
        $info = getimagesize($ori_file);
        list($w,$h) = $info;
        $mime = $info['mime'];
        switch($mime){
            case 'image/jpeg' :
                $img = imagecreatefromjpeg($ori_file);
                $img_save_func = "imagejpeg";
                $ext = "jpg";
                break;
            case 'image/png' :
                $img = imagecreatefrompng($ori_file);
                $img_save_func = "imagepng";
                $ext = "png";
                break;
            case 'image/gif' :
                $img = imagecreatefromgif($ori_file);
                $img_save_func = "imagegif";
                $ext = "gif";
                break;
            default :
                throw Exception('unknow image type.');
        }
        if($n_w > $w){
            $n_w = $w;
            $n_h = $h;
        } else {
            $n_h = $n_w*$h/$w;
        }
        $tmp = imagecreatetruecolor($n_w,$n_h);
        imagecopyresampled($tmp,$img,0,0,0,0,$n_w,$n_h,$w,$h);
        $dir = pathinfo($ori_file,PATHINFO_DIRNAME)."/";
        $new_file = $dir.pathinfo($ori_file,PATHINFO_FILENAME)."_s.".$ext;
        $img_save_func($tmp,$new_file);
        //delete ori file
        if($del){
            if(file_exists($ori_file)){
                chmod($ori_file,0755);   //Change the file permissions if allowed
                unlink($ori_file);       //remove the file
            }
        }
        return $new_file;
    }
    private function logo_stamp($ori_file,$logo_url){
        $logo = imagecreatefrompng($logo_url);
        list($lw,$lh) = getimagesize($logo_url);
        $info = getimagesize($ori_file);
        list($w,$h) = $info;
        $mime = $info['mime'];
        switch($mime){
            case 'image/jpeg' :
                $img = imagecreatefromjpeg($ori_file);
                $img_save_func = "imagejpeg";
                $ext = "jpg";
                break;
            case 'image/png' :
                $img = imagecreatefrompng($ori_file);
                $img_save_func = "imagepng";
                $ext = "png";
                break;
            case 'image/gif' :
                $img = imagecreatefromgif($ori_file);
                $img_save_func = "imagegif";
                $ext = "gif";
                break;
            default :
                throw Exception('unknow image type.');
        }
        $x = $w-90;
        $y = $h-90;
        imagecopyresampled($img, $logo, $x, $y, 0, 0, 80, 80, $lw, $lh);
        //rename ori_file from jpeg to jpg
        $o_ext = pathinfo($ori_file,PATHINFO_EXTENSION);
        if($o_ext=="jpeg"){
            rename($ori_file,$dir.pathinfo($ori_file,PATHINFO_FILENAME).".jpg");
        }
        $img_save_func($img,$ori_file);
    }
}

