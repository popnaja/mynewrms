function show_pic(id,url,flag){
    $(document).ready(function(){
        var but = $(".md-add-but");
        var inputf = $("#"+id);
        var mstatus = $(".md-status-f");
        var files;
        var limit = 5*Math.pow(10,6);
        but.on("click",function(){
            $("#"+id).trigger("click");
        });
        inputf.on("change",function(){
            pg_loading(true);
            var input = document.getElementById(id);
            files = input.files;
            uploadFiles();
        });
        function uploadFiles(){
            var mydata = new FormData();
            for(var i=0;i<files.length;i++){
                var file= files[i];
                var msg = file.name+" OK.";
                //check file type
                if(!file.type.match("image.*")){
                    msg = file.name+" is wrong file type. Only accept image files.";
                    pg_dialog("คำเตือน",msg);
                    pg_loading(false);
                    return false;
                }
                if(file.size > limit){
                    msg = file.name+" is over file size limit at 5 MB.";
                    pg_dialog("คำเตือน",msg);
                    pg_loading(false);
                    return false;
                }
                //append file data to mydata
                mydata.append('fileUp[]', file, file.name);            
            }
            mydata.append('request',"show_pic");
            mydata.append('flag',flag);
            //set up the request.
            var xhr = new XMLHttpRequest();
            //open the connection
            xhr.open("POST",url, true);
            //setup handler when the request finished
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    if(xhr.status === 200){
                        pg_loading(false);
                        var data = JSON.parse(xhr.responseText);
                        inputf.val("");
                        if(data[0] === "error"){
                            mstatus.html(data[1]);
                            mstatus.addClass("up-ng");
                        } else {
                            mstatus.removeClass("up-ng");
                            var flag = data[0];
                            if(flag==="replace"){
                                var target = $("#"+data[1]);
                                var html = data[2];
                                $(".md-input").addClass("form-hide");
                                target.html(html);
                            } else if(flag==="append"){
                                var target = $("#"+data[1]);
                                var html = data[2];
                                target.append(html);
                            }
                        }
                    }
                }
            };
            //send the data
            xhr.send(mydata);
        }
    });
}
function show_pdf(id,url){
    $(document).ready(function(){
        var but = $(".md-add-but");
        var inputf = $("#"+id);
        var files;
        var limit = 5*Math.pow(10,6);
        but.on("click",function(){
            $("#"+id).trigger("click");
        });
        inputf.on("change",function(){
            pg_loading(true);
            var input = document.getElementById(id);
            files = input.files;
            uploadFiles();
        });
        function uploadFiles(){
            var mydata = new FormData();
            for(var i=0;i<files.length;i++){
                var file= files[i];
                var msg = file.name+" OK.";
                //check file type
                if(!file.type.match("image.*")&&!file.type.match("application/pdf")){
                    msg = file.name+" is wrong file type. Only accept image or PDF.";
                    pg_dialog("คำเตือน",msg);
                    pg_loading(false);
                    return false;
                }
                if(file.size > limit){
                    msg = file.name+" is over file size limit at 5 MB.";
                    pg_dialog("คำเตือน",msg);
                    pg_loading(false);
                    return false;
                }
                //append file data to mydata
                mydata.append('fileUp[]', file, file.name);            
            }
            mydata.append('request',"up_pdf");
            //set up the request.
            var xhr = new XMLHttpRequest();
            //open the connection
            xhr.open("POST",url, true);
            //setup handler when the request finished
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    if(xhr.status === 200){
                        pg_loading(false);
                        var data = JSON.parse(xhr.responseText);
                        inputf.val("");
                        if(data[0] === "error"){
                        } else {
                            var flag = data[0];
                            if(flag==="replace"){
                                var target = $("#"+data[1]);
                                var html = data[2];
                                $(".md-input").addClass("form-hide");
                                target.html(html);
                            } else if(flag==="append"){
                                var target = $("#"+data[1]);
                                var html = data[2];
                                target.append(html);
                            }
                        }
                    }
                }
            };
            //send the data
            xhr.send(mydata);
        }
    });
}
function del_pic(){
    $(document).ready(function(){
        var but = $(".del-media-pic");
        but.on("click",function(e){
            e.preventDefault();
            $(".md-input").removeClass("form-hide");
            var data = {};
            var url = $("#ajax_req").val();
            data['request'] = "del_pic_file";
            data['pic'] = $(this).siblings("input").val();
            $(this).parent().remove();
            post_ajax(data,url);
        });
    });
}
function delete_md_file(){
    $(document).ready(function(){
        var but = $(".delete-md-file");
        but.on("click",function(e){
            e.preventDefault();
            if(confirm("ยืนยันการลบไฟล์")){
                $(".md-input").removeClass("form-hide");
                var data = {};
                var url = $("#ajax_req").val();
                data['request'] = "del_pic_file";
                data['pic'] = $(this).siblings("input").val();
                $(this).parent().remove();
                post_ajax(data,url);
            }
        });
    });
}
function post_draft(fdata){
    var row = $("#ing-food table tr");
    var id,serv,qty,arr;
    fdata['request'] = "post_draft_food";
    fdata['ing'] = [];
    row.each(function(){
        id = $(this).children("td").eq(0).children().attr("value");
        if(typeof id == "undefined"){
            return true;
        }
        serv = $(this).children("td").eq(2).children("span").attr("exsid");
        qty = $(this).children("td").eq(3).text();
        arr = [id,serv,qty];
        fdata['ing'].push(arr);
    });
    console.log(fdata);
    post_ajax(fdata,fdata['referurl']);
}

function unlink_pic(url){
    window.onbeforeunload = function(e){
        if(($("#fpic").length>0)&&($("#isposted").length===0)){
            var data = {};
            data['request'] = "del_fpic";
            data['pic_url'] = $(".del-food-pic").attr('picurl');
            post_ajax(data,url);
        }
    };
}
function show_big(){
    $(document).ready(function(){
        var pic = $(".md-pic-thumb img");
        pic.on("click",function(){
            var url = $(this).attr("src");
            var html = "<div class='md-dialog'>\n\
            <div>\n\
            <a href='' title='Close' class='icon-delete-circle close-md'></a>\n\
            <img src='"+url+"'/>\n\
            </div>\n\
            </div><!-- .md-dialog -->\n\
            <script>close_md();</script>";
            $("#content").prepend(html);
            $("body").addClass("md-showing");
        });
        
    });
}
function close_md(){
    var box = $(".md-dialog");
    var but = $(".close-md");
    box.on("click",function(){
        pic_close();
    });
    but.on("click",function(e){
        e.preventDefault();
        pic_close();
    });
}
function pic_close(){
    $(".md-dialog").remove();
    $("body").removeClass("md-showing");
}

