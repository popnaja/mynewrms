function customer_fun(){
    $(document).ready(function(){
        var st_ref = {};
        var id = "receiver";
        var target = "rec-list";
        var url = $("#ajax_req").val();
        auto_complete_input(id,findex,show);
        function findex(find,f1,f2){
            var data = {};
            data['request'] = "find_user_email";
            data['f'] = find;
            $.ajax({
                url:url,
                type:'POST',
                dataType:"json",
                data:data,
                success: function(response) {
                    f1(response);
                },
                error: function(err){
                    f2("ERROR"+JSON.stringify(err));
                }
            });
        }
        function show(ele){
            if(ele.hasClass("nores")){
                $("#"+id).val("");
            } else {
                $("#"+id).val("");
                update_tags(ele);
            }
        }
        function update_tags(ele){
            var ntag = {};
            ntag[ele.attr("cid")] = ele.text();
            st_ref = $.extend({},st_ref,ntag);
            draw_tags();
        }
        function draw_tags(){
            var tg = $("#"+target);
            var html = "";
            $.each(st_ref,function(k,v){
                html += "<span class='tag-list' idref='"+v+"'><span class='tag-remove icon-delete-circle'></span>"+v+"<input type='hidden' name='"+target+"-list[]' value='"+k+"' /></span>";
            });
            tg.html(html);
            var del = $(".tag-remove");
            del.on("click",function(){
                var idref = $(this).attr("idref");
                delete st_ref[idref];
                $(this).remove();
                //console.log(cprod);
            }); 
        }
        
        //month change
        var sel = $("#month");
        sel.on("change",function(){
            var data = {};
            data['request'] = "update_quote_report";
            data['cid'] = $("#cid").val();
            data['month'] = $(this).val();
            post_ajax(data,url);
        });
    });
}
function del_cus(){
$("document").ready(function(){
    var del = $("#del-cus");
    var url = $("#ajax_req").val();
    del.on("click",function(){
        if(confirm("คุณแน่ใจแล้วที่จะลบรายการลูกค้านี้")){
            var data = {};
            data['request'] = "delete_customer";
            data['cid'] = $("#cid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,url);
        }
    });
});
}
function send_note(){
    $("document").ready(function(){
        var but = $("#send_email");
        var ct = $(".note-edit").eq(0);
        var url = $("#ajax_req").val();
        but.on("click",function(){
            var list = $(".tag-list");
            if(list.length <= 0){
                alert("กรุณาระบุชื่อผู้รับอีเมล");
            } else {
                var data = {};
                data['request'] = "send_email";
                data['cid'] = $("#cid").val();
                data['uid'] = $("#uid").val();
                data['pauth'] = $("#pauth").val();
                data['subject'] = $("#code").val()+" : "+$("#name").val() + " วันที่ "+ct.text();
                data['email[]'] = [];
                $.each($("input[name='rec-list-list[]']"),function(){
                    data['email[]'].push($(this).val());
                });
                //console.log(data);
                post_ajax(data,url);
            }
        });
    });
}
function edit_note(){
$(document).ready(function(){
    var note = $(".note-edit");
    var ninfo;
    var cancel = $("#cancel-edit");
    note.on("click",function(){
        ninfo = $(this).attr("ninfo").split(";");
        var datestr = ninfo[1].replace(" ","T");
        var mdate = new Date(datestr);
        mdate.setHours(mdate.getHours()-7);
        var hour = mdate.getHours();
        var min = mdate.getMinutes();
        var month = mdate.getMonth()+1;
        var d = mdate.getFullYear()+"-"+leadzero(month,2)+"-"+leadzero(mdate.getDate(),2);
        $("#nid").val(ninfo[0]);
        $("#date").val(d);
        $("#hour").val(hour);
        $("#min").val(min);
        $("#note").val(ninfo[2]);
        $("#type").val(ninfo[3]);
        $("#submit").val("แก้ไข");
        $("#request").val("edit_note");
        cancel.removeClass("form-hide");
    });
    cancel.on("click",function(){
        $("#nid").val(0);
        $("#date").val("");
        $("#note").val("");
        $("#submit").val("เพิ่มบันทึก");
        $("#request").val("add_note");
        cancel.addClass("form-hide");
    })
});
}
function add_contact(aname){
    $(document).ready(function(){
        var but = $("#add-more-ct");
        but.on("click",function(){
            if(!valNoBlank("cname")||!valNoBlank("ctel")){
                //show msg
            } else {
                var data = get_val(aname);
                data['request'] = "add_cus_ct";
                data['redirect'] = $("#redirect").val();
                data['cid'] = $("#cid").val();
                data['ctid'] = $("#ctid").val();
                data['ct_cat'] = $("#ct_cat").val();
                var url = $("form").attr("action");
                post_ajax(data,url);
            }
        });

        //edit contact
        var ct = $(".cus-ct");
        ct.on("click",function(){
            var ainfo,i=1;
            ainfo = $(this).attr("ctinfo").split(";");
            $.each(aname,function(k,v){
                $("[name="+k+"]").val(ainfo[i]);
                i++;
            });
            but.val("Edit");
            $("#ctid").val(ainfo[0]);
        });
    });
}
function add_sup_ct(aname){
    $(document).ready(function(){
        var but = $("#add-more-ct");
        but.on("click",function(){
            if(!valNoBlank("cname")||!valNoBlank("ctel")){
                //show msg
            } else {
                var data = get_val(aname);
                data['request'] = "add_sup_ct";
                data['redirect'] = $("#redirect").val();
                data['sid'] = $("#sid").val();
                data['ctid'] = $("#ctid").val();
                var url = $("form").attr("action");
                post_ajax(data,url);
            }
        });

        //edit contact
        var ct = $(".sup-ct");
        ct.on("click",function(){
            var ainfo,i=1;
            ainfo = $(this).attr("ctinfo").split(";");
            $.each(aname,function(k,v){
                $("[name="+k+"]").val(ainfo[i]);
                i++;
            });
            but.val("Edit");
            $("#ctid").val(ainfo[0]);
        });
    });
}