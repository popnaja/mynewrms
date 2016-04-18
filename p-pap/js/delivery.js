function deli_function(pre){
    $(document).ready(function(){
        var cancel = $("#cancel");
        var addbut = $("#add-list");
        var edform = $(".my-tab-inside");
        var targ = $("#deli-list");
        var header = ["แก้ไข","งาน","ยอดงาน","ค้างส่ง","จัดส่ง"];
        var rec = [];
        
        inputenter(['deli'],'add-list');
        //cancel
        var cancel = $("#cancel");
        cancel.on("click",function(){
            clear();
        });
        
        if($.type(pre)!=="undefined"){
            pre_tb();
        }
        addbut.on("click",function(){
            if(!valNoBlank("oref")||!valNoBlank("deli")||!valZero(['deli'])){
                //show msg
            } else {
                var oid = $("#oref").attr("oid");
                var name = $("#oref").val();
                var amount = parseFloat($("#amount").val());
                var remain = parseFloat($("#remain").val());
                var deli = parseFloat($("#deli").val());
                if(deli>remain){
                    deli = remain;
                }
                var ed = [oid,name,amount,remain,deli];
                rec[oid] = [
                    //"<span class='del-list icon-delete-circle' rid='"+oid+"'></span>",
                    "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                    name+"<input type='hidden' name='oid[]' value='"+oid+"' />",
                    amount+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                    remain+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                    deli+"<input type='hidden' name='deli[]' value='"+deli+"' />"
                ];
                draw_tb();
                clear();
            }
        });
        
        function pre_tb(){
            $.each(pre,function(k,v){
                var oid = v[0];
                var name = v[1];
                var amount = v[2]
                var remain = amount - v[3];
                var deli = typeof v[4] !=="undefined" ? v[4] : remain;
                var ed = [oid,name,amount,remain,deli];
                rec[oid] = [
                    //"<span class='del-list icon-delete-circle' rid='"+oid+"'></span>",
                    "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                    name+"<input type='hidden' name='oid[]' value='"+oid+"' />",
                    amount+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                    remain+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                    deli+"<input type='hidden' name='deli[]' value='"+deli+"' />"
                ];
            });
            draw_tb();
        }

        //draw table
        function draw_tb(){
            targ.html(show_table(header,rec,'tb-deli-list'));
            var del = $(".del-list");
            del.off("click");
            del.on("click",function(){
                delete rec[$(this).attr("rid")];
                $(this).parents("tr").remove();
            });
            //edit
            var edit = $(".edit-list");
            edit.off("click");
            edit.on("click",function(){
                edform.removeClass("form-hide");
                cancel.removeClass("form-hide");
                var info = $(this).attr("info").split(",");
                addbut.val("แก้ไข");
                $("#oref").attr("oid",info[0]);
                $("#oref").val(info[1]);
                $("#amount").val(info[2]);
                $("#remain").val(info[3]);
                $("#deli").val(info[4]);
            });
        }
        
        function clear(){
            addbut.val("เพิ่มลงรายการ");
            $("#oref").val("");
            $("#amount").val("");
            $("#remain").val("");
            $("#deli").val("");
            cancel.addClass("form-hide");
            edform.addClass("form-hide");
        }
    });
    
}
function check_deli(e){
    var deli = $("input[name='deli[]']").length;
    if(deli<=0){
        e.preventDefault();
        show_submit_error('ez-msg');
    } else {
        $("#papform").submit();
    }
}
function mix_deli(){
    $(document).ready(function(){
       var but = $("#mix-deli");
       but.on("click",function(){
           //check mix
           var check = $("input[type='checkbox']:checked");
           if(check.length<2){
               pg_dialog("<span class='icon-alert' style='color:orange;'></span>คำเตือน","ต้องการ 2 รายการขึ้นไป");
           } else {
               var re = $("#redirect").val()
               var id = [];
               $.each(check,function(){
                   id.push($(this).val());
               });
               window.location.replace(re+"?action=add&oid="+id.toString());
           }
       });
    });
}
function delete_deli(){
    $(document).ready(function(){
        var del = $(".del-job-delivery");
        var url = $("#ajax_req").val();
        del.on("click",function(){
            if(confirm("โปรดยืนยันการลบใบส่งของ")){
                var data = {};
                data['request'] = "del_job_delivery";
                data['did'] = $(this).attr("did");
                post_ajax(data,url);
            }
        });
    });
}
function del_temp_deli(){
$(document).ready(function(){
    $("#del-temp-deli").on("click",function(){
        if(confirm("โปรดยืนยันการลบใบส่งของ")){
            var data = {};
            data['request'] = "delete_temp_deli";
            data['tdid'] = $("#tdid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}
function del_job_deli(){
$(document).ready(function(){
    $("#del-job-deli").on("click",function(){
        if(confirm("โปรดยืนยันการลบใบส่งของ")){
            var data = {};
            data['request'] = "delete_job_deli";
            data['did'] = $("#did").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}