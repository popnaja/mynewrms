function place_bill(){
$(document).ready(function(){
    var but = $("#place_bill");
    but.on("click",function(){
        var input = $("input[name='oid[]']:checked");
        if(input.length==0){
            pg_dialog("Alert","เลือกรายการอย่างน้อย 1 รายการเพื่อออกบิล");
        } else {
            var bill = [];
            $.each(input,function(){
                bill.push($(this).val());
            });
            window.location.replace($("#redirect").val()+"?action=add&oid="+bill.toString());
        }
    });
});
}
function customer_search(){
$(document).ready(function(){
    var id = "cusid";
    auto_complete_input(id,findex2,show2);
    function show2(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).val(ele.html());
            $("#cusid-but").trigger("click");
        }
    }
    function findex2(find,f1,f2){
        var data = {};
        data['request'] = "find_customer";
        data['f'] = find;
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
function check_sel(e){
$(document).ready(function(){
    var check = $("input[name='did[]']:checked");
    if(check.length===0){
        e.preventDefault();
        pg_dialog("คำเตือน","กรุณาเลือกบิลอย่างน้อย 1 รายการเพื่อสร้างใบวางบิล");
    } else {
        var cus = [];
        $.each(check,function(){
            var data = $(this).val().split(",");
            if($.inArray(data[1],cus)===-1){
                cus.push(data[1]);
            }
        });
        if(cus.length>1){
            if(confirm("รายการใบส่งของที่เลือก เป็นลูกค้าต่างเจ้ากัน\n หากแน่ใจที่จะออกวางบิลกด OK")){
                
            } else {
                e.preventDefault();
            }
        }
    }
});
}
function del_pbill(){
$(document).ready(function(){
    $("#del-pbill").on("click",function(){
        if(confirm("โปรดยืนยันการลบใบวางบิล")){
            var data = {};
            data['request'] = "delete_pbill";
            data['bid'] = $("#bid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}
function mycd_bill(){
$(document).ready(function(){
    //change month
    var but = $(".mycd-c-month");
    var url = $("#ajax_req").val();
    but.on("click",function(){
        if(!$(this).hasClass("mycd-disable")){
            var data = {};
            data['request'] = "mycd_bill_m";
            data['year'] = $(this).attr("year");
            data['month'] = $(this).attr("month");
            data['type'] = $(this).attr('cdtype');
            post_ajax(data,url)
        }
    });

    //change type
    var tbut = $(".mycd-switch-type");
    tbut.on("click",function(){
        if(!$(this).hasClass("mycd-active")){
            var data = {};
            data['request'] = "mycd_bill_t";
            data['type'] = $(this).attr('cdtype');
            data['year'] = $(this).attr("year");
            data['month'] = $(this).attr("month");
            post_ajax(data,url)
        }
    });

});
}
function inv_function(pre){
$(document).ready(function(){
    var cancel = $("#cancel");
    var addbut = $("#add-list");
    var edform = $(".my-tab-inside");
    var targ = $("#deli-list");
    var header = ["แก้ไข","ใบแจ้งหนี้","งาน","ยอด","คงเหลือ","ยอดออกใบกำกับ<br/>(ยังไม่รวม Vat)"];
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
        if(!valNoBlank("dno")||!valNoBlank("inv")||!valZero(['inv'])){
            //show msg
        } else {
            var did = $("#dno").attr("did");
            var name = $("#dno").val();
            var amount = parseFloat($("#amount").val());
            var job = $("#jobn").val();
            var remain = parseFloat($("#remain").val());
            var inv = parseFloat($("#inv").val());
            if(inv>remain){
                inv = remain;
            }
            var ed = [did,name,job,amount,remain,inv];
            rec[did] = [
                //"<span class='del-list icon-delete-circle' rid='"+did+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='did[]' value='"+did+"' />",
                job,
                numformat(amount,2)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,2)+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                numformat(inv,2)+"<input type='hidden' name='inv[]' value='"+inv+"' />"
            ];
            draw_tb();
            clear();
        }
    });

    function pre_tb(){
        $.each(pre,function(k,v){
            var did = v[0];
            var name = v[1];
            var job = v[2];
            var amount = parseFloat(v[3]);
            var remain = amount - v[4];
            var inv = typeof v[5] !=="undefined" ? v[5] : remain;
            var ed = [did,name,job,amount,remain,inv];
            rec[did] = [
                //"<span class='del-list icon-delete-circle' rid='"+did+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='did[]' value='"+did+"' />",
                job,
                numformat(amount,2)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,2)+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                numformat(inv,2)+"<input type='hidden' name='inv[]' value='"+inv+"' />"
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
            $("#dno").attr("did",info[0]);
            $("#dno").val(info[1]);
            $("#jobn").val(info[2]);
            $("#amount").val(info[3]);
            $("#remain").val(info[4]);
            $("#inv").val(info[5]);
        });
    }

    function clear(){
        edform.addClass("form-hide");
        $("#dno").val("");
        $("#amount").val("");
        $("#remain").val("");
        $("#inv").val("");
        cancel.addClass("form-hide");
    }
});
}
function del_invoice(){
$(document).ready(function(){
    $("#del-invoice").on("click",function(){
        if(confirm("โปรดยืนยันการลบใบกำกับภาษี")){
            var data = {};
            data['request'] = "delete_invoice";
            data['ivid'] = $("#ivid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}
function rec_function(pre){
$(document).ready(function(){
    var cancel = $("#cancel");
    var addbut = $("#add-list");
    var edform = $(".my-tab-inside");
    var targ = $("#deli-list");
    var header = ["แก้ไข","ใบกำกับ","ยอดตามใบกำกับ","ค้างชำระ","ยอดออกใบเสร็จ"];
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
        if(!valNoBlank("ivno")||!valNoBlank("pay")||!valZero(['pay'])){
            //show msg
        } else {
            var ivid = $("#ivno").attr("ivid");
            var name = $("#ivno").val();
            var amount = parseFloat($("#amount").val());
            var remain = parseFloat($("#remain").val());
            var pay = parseFloat($("#pay").val());
            if(pay>remain){
                pay = remain;
            }
            var ed = [ivid,name,amount,remain,pay];
            rec[ivid] = [
                //"<span class='del-list icon-delete-circle' rid='"+ivid+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='ivid[]' value='"+ivid+"' />",
                numformat(amount,2)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,2)+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                numformat(pay,2)+"<input type='hidden' name='pay[]' value='"+pay+"' />"
            ];
            draw_tb();
            clear();
        }
    });

    function pre_tb(){
        $.each(pre,function(k,v){
            var ivid = v[0];
            var name = v[1];
            var amount = parseFloat(v[2]);
            var remain = amount - v[3];
            var pay = typeof v[4] !=="undefined" ? v[4] : remain;
            var ed = [ivid,name,amount,remain,pay];
            rec[ivid] = [
                //"<span class='del-list icon-delete-circle' rid='"+ivid+"'></span>",
                "<span class='edit-list icon-page-edit' info='"+ed.toString()+"'></span>",
                name+"<input type='hidden' name='ivid[]' value='"+ivid+"' />",
                numformat(amount,2)+"<input type='hidden' name='amount[]' value='"+amount+"' />",
                numformat(remain,2)+"<input type='hidden' name='rem[]' value='"+remain+"' />",
                numformat(pay,2)+"<input type='hidden' name='pay[]' value='"+pay+"' />"
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
            $("#ivno").attr("ivid",info[0]);
            $("#ivno").val(info[1]);
            $("#amount").val(info[2]);
            $("#remain").val(info[3]);
            $("#pay").val(info[4]);
        });
    }

    function clear(){
        edform.addClass("form-hide");
        $("#ivno").val("");
        $("#amount").val("");
        $("#remain").val("");
        $("#pay").val("");
        cancel.addClass("form-hide");
    }
});
}
function del_receipt(){
$(document).ready(function(){
    $("#del-receipt").on("click",function(){
        if(confirm("โปรดยืนยันการลบใบเสร็จ")){
            var data = {};
            data['request'] = "delete_receipt";
            data['rcid'] = $("#rcid").val();
            data['ivid'] = $("#ivid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,$("#ajax_req").val());
        }
    });
});
}
function po_paid_function(){
$(document).ready(function(){
    var poid = $("#poid");
    var table = $("#table");
    var ed = $(".po-paid");
    ed.on("click",function(e){
        e.preventDefault();
        my_float_box('paid-box',true);
        poid.val($(this).attr("poid"));
        table.val($(this).attr("ttable"));
    });
    
    //edit
    var edit = $(".edit-po-paid");
    edit.on("click",function(e){
        e.preventDefault();
        my_float_box('paid-box',true);
        poid.val($(this).attr("poid"));
        table.val($(this).attr("ttable"));
        var info = $(this).attr("info").split(",");
        $("#date").val(info[0]);
        $("#ref").val(info[1]);
    });
});
}