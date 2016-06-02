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
function mdeli_function(pre,typen){
$(document).ready(function(){
    var cancel = $("#cancel");
    var addbut = $("#add-list");
    var edform = $(".my-tab-inside");
    var targ = $("#deli-list");
    var header = ["ลบ","แก้ไข","ชื่องาน","รายละเอียด","ชนิด","ยอดงาน","ราคาต่อหน่วย","ส่วนลด"];
    var rec = {};
    var detail = $("[name='jdt[]']")
    inputenter(['deli'],'add-list');
    //cancel
    var cancel = $("#cancel");
    cancel.on("click",function(){
        clear();
    });
    if($.type(pre)!=="undefined"){
        pre_tb();
    }
    //escape comma
    $("#name , [id^='jdt_']").on("blur",function(){
        var nname = $(this).val().replace(/,/g,"");
        $(this).val(nname);
    });
    function pre_tb(){
        $.each(pre,function(k,v){
            var name = v[0];
            var type = parseInt(v[1]);
            var amount = parseFloat(v[2]);
            var price = parseFloat(v[3]);
            var discount = parseFloat(v[4]);
            var dts = v[5].split(",");
            var ed = [name,type,amount,price,discount];
            rec[name] = [
                "<span class='del-list icon-delete-circle' rid='"+name+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"' jdt='"+dts.toString()+"'></span>",
                name+"<input type='hidden' name='name[]' value='"+name+"' />",
                show_list(dts)+"<input type='hidden' name='job_detail[]' value='"+dts.toString()+"' />",
                typen[type]+"<input type='hidden' name='type[]' value='"+type+"' />",
                numformat(amount,0)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(price,2)+"<input type='hidden' name='price[]' value='"+price+"' />",
                numformat(discount,2)+"<input type='hidden' name='discount[]' value='"+discount+"' />"
            ];
        });
        draw_tb();
    }
    addbut.on("click",function(){
        if(!valNoBlank("name")||!valZero("amount")||!valZero(['price'])){
            //show msg
        } else {
            var name = $("#name").val();
            var type = $("#type").val();
            var amount = parseFloat($("#amount").val());
            var price = parseFloat($("#price").val());
            var discount = parseFloat($("#discount").val());
            var dts = [];
            $.each(detail,function(){
                if($(this).val().length>0){
                    dts.push($(this).val());
                }
            });
            console.log(dts);
            var ed = [name,type,amount,price,discount];
            rec[name] = [
                "<span class='del-list icon-delete-circle' rid='"+name+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"' jdt='"+dts.toString()+"'></span>",
                name+"<input type='hidden' name='name[]' value='"+name+"' />",
                show_list(dts)+"<input type='hidden' name='job_detail[]' value='"+dts.toString()+"' />",
                typen[type]+"<input type='hidden' name='type[]' value='"+type+"' />",
                numformat(amount,0)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(price,2)+"<input type='hidden' name='price[]' value='"+price+"' />",
                numformat(discount,2)+"<input type='hidden' name='discount[]' value='"+discount+"' />"
            ];
            draw_tb();
            clear();
        }
    });
    //show list
    function show_list(arr){
        var list = "<ul style='padding-left:15px;'>";
        $.each(arr,function(i,v){
           list += "<li style='text-align:left;'>"+v+"</li>";
        });
        list += "</ul>";
        return list;
    }
    //draw table
    function draw_tb(){
        targ.html(show_table(header,rec,'tb-mdeli-list'));
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
            var dt = $(this).attr("jdt").split(",");
            addbut.val("แก้ไข");
            $("#name").val(info[0]);
            $("#type").val(info[1]);
            $("#amount").val(info[2]);
            $("#price").val(info[3]);
            $("#discount").val(info[4]);
            $.each(dt,function(i,v){
                detail.eq(i).val(v);
            });
        });
    }
    function clear(){
        addbut.val("เพิ่มลงรายการ");
        $("#name").val("");
        $("#type").val(10);
        $("#amount").val("");
        $("#price").val("");
        $("#discount").val("");
        detail.val("");
        cancel.addClass("form-hide");
        //edform.addClass("form-hide");
    }
});
}
function mtdeli_function(pre){
$(document).ready(function(){
    var cancel = $("#cancel");
    var addbut = $("#add-list");
    var edform = $(".my-tab-inside");
    var targ = $("#deli-list");
    var header = ["ลบ","แก้ไข","ชื่องาน","ยอดงาน","ค้างส่ง","ยอดส่ง"];
    var rec = {};
    inputenter(['deli'],'add-list');
    //cancel
    var cancel = $("#cancel");
    cancel.on("click",function(){
        clear();
    });
    if($.type(pre)!=="undefined"){
        pre_tb();
    }
    function pre_tb(){
        $.each(pre,function(k,v){
            var name = v[0];
            var amount = parseFloat(v[1]);
            var remain = parseFloat(v[2]);
            var deli = parseFloat(v[3]);
            var ed = [name,amount,remain,deli];
            rec[name] = [
                "<span class='del-list icon-delete-circle' rid='"+name+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='name[]' value='"+name+"' />",
                numformat(amount,0)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,0)+"<input type='hidden' name='remain[]' value='"+remain+"' />",
                numformat(deli,0)+"<input type='hidden' name='deli[]' value='"+deli+"' />"
            ];
        });
        draw_tb();
    }
    addbut.on("click",function(){
        var remain = parseFloat($("#remain").val());
        var deli = parseFloat($("#deli").val());
        if(!valZero("deli")||!valZero(['remain'])){
            //show msg
        } else if(deli>remain){
            pg_dialog("คำเตือน","ยอดส่งมากกว่ายอดค้างส่ง");
        } else {
            var name = $("#name").val();
            var amount = parseFloat($("#amount").val());
            var ed = [name,amount,remain,deli];
            rec[name] = [
                "<span class='del-list icon-delete-circle' rid='"+name+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='name[]' value='"+name+"' />",
                numformat(amount,0)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,0)+"<input type='hidden' name='remain[]' value='"+remain+"' />",
                numformat(deli,0)+"<input type='hidden' name='deli[]' value='"+deli+"' />"
            ];
            draw_tb();
            clear();
        }
    });
    //draw table
    function draw_tb(){
        targ.html(show_table(header,rec,'tb-mdeli-list'));
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
            $("#name").val(info[0]);
            $("#amount").val(info[1]);
            $("#remain").val(info[2]);
            $("#deli").val(info[3]);
        });
    }
    function clear(){
        addbut.val("เพิ่มลงรายการ");
        $("#name").val("");
        $("#amount").val("");
        $("#remain").val("");
        $("#deli").val("");
        cancel.addClass("form-hide");
        //edform.addClass("form-hide");
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
function check_mdeli(e){
    var deli = $("input[name='name[]']").length;
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
            data['did'] = $("#did").val();
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
function get_cus_info(){
$(document).ready(function(){
    //auto search customer
    var id = "scid";
    var target = $("#cid");
    auto_complete_input(id,findex,show);
    function show(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).val(ele.html());
            target.val(ele.attr("cid"));
            //get new select
            var url = $("#ajax_req").val();
            var cdata = {};
            cdata['request'] = "get_contact_ad";
            cdata['cid'] = ele.attr("cid");
            post_ajax(cdata,url);
        }
    }
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_customer";
        data['f'] = find;
        data['pauth'] = $("#pauth").val();
        data['uid'] = $("#uid").val();
        var url = $("#ajax_req").val();
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
});
}