function sel_supplier(){
$(document).ready(function(){
    var url = $("#ajax_req").val();
    //sel supplier
    var sel = $("#supplier");
    sel.on("change",function(){
        var data = {};
        data['request'] = "get_sup_ct";
        data['sid'] = $(this).val();
        data['target'] = "sup_ct_box";
        post_ajax(data,url);
    });
});
}
function po_function(jpre){
    $(document).ready(function(){
        inputenter(['cost'],'add-mat');
        var url = $("#ajax_req").val();
        var edform = $(".my-tab-inside");
        
        //add mat
        var but = $("#add-mat");
        var header = ["ลบ","แก้ไข","รายการ","อ้างอิง","จำนวน","ราคา/ขนาดบรรจุ","ราคา"];
        var rec = [];
        var targ = $("#po-list");
        if($.type(jpre)!=="undefined"){
            pre_tb();
        }
        function pre_tb(){
            $.each(jpre,function(k,v){
                var mid = v[0];
                var item = v[1];
                var oid = v[2];
                var ref = v[3];
                var amount = parseFloat(v[4]);
                var cost = parseFloat(v[5]);
                var tt = amount*cost;
                var ed = [mid,item,oid,ref,amount,cost];
                rec[mid] = [
                    "<span class='del-po-list icon-delete-circle' mid='"+mid+"'></span>",
                    "<span class='edit-po-list icon-page-edit' info='"+ed.toString()+"'></span>",
                    item+"<input type='hidden' name='mid[]' value='"+mid+"' />",
                    ref+"<input type='hidden' name='oid[]' value='"+oid+"' />",
                    numformat(amount,3)+"<input type='hidden' name='vol[]' value='"+amount+"' />",
                    numformat(cost,2)+"<input type='hidden' name='cost[]' value='"+cost+"' />",
                    numformat(tt,2)
                ];
            });
            draw_tb();
        }
        but.on("click",function(){
            if(!valNoBlank("mat_auto")||!valNoBlank("amount")||!valNoBlank("cost")){
                //show msg
            } else {
                var mid = $("#mat_auto").attr("mid");
                var item = $("#mat_auto").val();
                var ref = $("#oref").val();
                if(ref===""||$("#oref").attr("oid")==="undefined"){
                    var oid = 0;
                } else {
                    var oid = $("#oref").attr("oid");
                }
                var amount = parseFloat($("#amount").val());
                var cost = parseFloat($("#cost").val());
                var tt = amount*cost;
                var ed = [mid,item,oid,ref,amount,cost];
                rec[mid] = [
                    "<span class='del-po-list icon-delete-circle' mid='"+mid+"'></span>",
                    "<span class='edit-po-list icon-page-edit' info='"+ed.toString()+"'></span>",
                    item+"<input type='hidden' name='mid[]' value='"+mid+"' />",
                    ref+"<input type='hidden' name='oid[]' value='"+oid+"' />",
                    numformat(amount,3)+"<input type='hidden' name='vol[]' value='"+amount+"' />",
                    numformat(cost,2)+"<input type='hidden' name='cost[]' value='"+cost+"' />",
                    numformat(tt,2)
                ];
                draw_tb();
                clear();
            }
        });
        $("#amat").on("change",clear);
        function draw_tb(){
            targ.html(show_table(header,rec,'tb-po-list'));
            var del = $(".del-po-list");
            del.off("click");
            del.on("click",function(){
                delete rec[$(this).attr("mid")];
                $(this).parents("tr").remove();
            });
            //edit
            var edit = $(".edit-po-list");
            edit.off("click");
            edit.on("click",function(){
                edform.removeClass("form-hide");
                cancel.removeClass("form-hide");
                edform.children("h4").text("แก้ไขรายการ");
                var info = $(this).attr("info").split(",");
                but.val("แก้ไข");
                $("#mat_auto").attr("mid",info[0]);
                $("#mat_auto").val(info[1]);
                $("#oref").attr("oid",info[2]);
                $("#oref").val(info[3]);
                $("#amount").val(info[4]);
                $("#cost").val(info[5]);
            });
        }
        //cancel
        var cancel = $("#cancel");
        cancel.on("click",function(){
            clear();
        });
        
        function clear(){
            but.val("เพิ่มลงรายการ");
            $("#mat_auto").val("");
            $("#oref").val("");
            $("#amount").val("");
            $("#cost").val("");
            cancel.addClass("form-hide");
            edform.children("h4").text("เพิ่มรายการ");
        }

        //autocomplete mat_auto
        var id2 = "mat_auto";
        auto_complete_input(id2,findex1,show1);
        function findex1(find,f1,f2){
            var data = {};
            data['request'] = "find_mat";
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
        function show1(ele){
            if(ele.hasClass("nores")){
                $("#"+id2).val("");
            } else {
                $("#"+id2).attr("mid",ele.attr("cid"));
                $("#"+id2).val(ele.text());
            }
        }
    });
}
function out_function(jpre){
$(document).ready(function(){
    inputenter(['cost'],'add-mat');
    var url = $("#ajax_req").val();
    var edform = $(".my-tab-inside");

    //add mat
    var but = $("#add-mat");
    var header = ["ลบ","แก้ไข","รายการ","อ้างอิง","หน่วย","จำนวน","ราคา/หน่วย","ราคา"];
    var rec = [];
    var targ = $("#po-list");
    if($.type(jpre)!=="undefined"){
        pre_tb();
    }
    function pre_tb(){
        $.each(jpre,function(k,v){
            var pid = v[0];
            var item = v[1];
            var cproid = v[2];
            var ref = v[3];
            var unit = v[4]
            var amount = parseFloat(v[5]);
            var cost = parseFloat(v[6]);
            var tt = amount*cost;
            var ed = [pid,item,cproid,ref,unit,amount,cost];
            rec[cproid] = [
                "<span class='del-po-list icon-delete-circle' cproid='"+cproid+"'></span>",
                "<span class='edit-po-list icon-page-edit' info='"+ed.toString()+"'></span>",
                item+"<input type='hidden' name='pid[]' value='"+pid+"' />",
                ref+"<input type='hidden' name='cproid[]' value='"+cproid+"' />",
                numformat(unit,2)+"<input type='hidden' name='unit[]' value='"+unit+"' />",
                numformat(amount,2)+"<input type='hidden' name='vol[]' value='"+amount+"' />",
                numformat(cost,2)+"<input type='hidden' name='cost[]' value='"+cost+"' />",
                numformat(tt,2)
            ];
        });
        draw_tb();
    }
    but.on("click",function(){
        if(!valNoBlank("pro_auto")||!valNoBlank("oref")||!valNoBlank("unit")||!valNoBlank("amount")||!valNoBlank("cost")){
            //show msg
        } else {
            var pid = $("#pro_auto").attr("pid");
            var item = $("#pro_auto").val();
            var cproid = $("#oref").attr("cproid");
            var ref = $("#oref").val();
            var unit = $("#unit").val();
            var amount = parseFloat($("#amount").val());
            var cost = parseFloat($("#cost").val());
            var tt = amount*cost;
            var ed = [pid,item,cproid,ref,unit,amount,cost];
            rec[cproid] = [
                "<span class='del-po-list icon-delete-circle' cproid='"+cproid+"'></span>",
                "<span class='edit-po-list icon-page-edit' info='"+ed.toString()+"'></span>",
                item+"<input type='hidden' name='pid[]' value='"+pid+"' />",
                ref+"<input type='hidden' name='cproid[]' value='"+cproid+"' />",
                numformat(unit,2)+"<input type='hidden' name='unit[]' value='"+unit+"' />",
                numformat(amount,2)+"<input type='hidden' name='vol[]' value='"+amount+"' />",
                numformat(cost,2)+"<input type='hidden' name='cost[]' value='"+cost+"' />",
                numformat(tt,2)
            ];
            draw_tb();
            clear();
        }
    });
    $("#amat").on("change",clear);
    function draw_tb(){
        targ.html(show_table(header,rec,'tb-po-list'));
        var del = $(".del-po-list");
        del.off("click");
        del.on("click",function(){
            delete rec[$(this).attr("cproid")];
            $(this).parents("tr").remove();
        });
        //edit
        var edit = $(".edit-po-list");
        edit.off("click");
        edit.on("click",function(){
            edform.removeClass("form-hide");
            cancel.removeClass("form-hide");
            var info = $(this).attr("info").split(",");
            but.val("แก้ไข");
            $("#pro_auto").attr("pid",info[0]);
            $("#pro_auto").val(info[1]);
            $("#oref").attr("cproid",info[2]);
            $("#oref").val(info[3]);
            $("#unit").val(info[4]);
            $("#amount").val(info[5]);
            $("#cost").val(info[6]);
        });
    }
    //cancel
    var cancel = $("#cancel");
    cancel.on("click",function(){
        clear();
    });

    function clear(){
        but.val("เพิ่มลงรายการ");
        $("#pro_auto").val("");
        $("#oref").val("");
        $("#unit").val("");
        $("#amount").val("");
        $("#cost").val("");
        cancel.addClass("form-hide");
    }

    //autocomplete pro_auto
    var id2 = "pro_auto";
    auto_complete_input(id2,findex1,show1);
    function findex1(find,f1,f2){
        var data = {};
        data['request'] = "find_process";
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
    function show1(ele){
        if(ele.hasClass("nores")){
            $("#"+id2).val("");
        } else {
            $("#"+id2).attr("pid",ele.attr("cid"));
            $("#"+id2).val(ele.text());
        }
    }
    //autocomplete cproid ref
    var id = "oref";
    auto_complete_input(id,findex,show);
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_cproid";
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
            $("#"+id).attr("cproid",ele.attr("cid"));
            $("#"+id).val(ele.text());
        }
    }
});
}
function check_po(e){
    var mat = $("input[name='vol[]']").length;
    if(mat<=0){
        e.preventDefault();
        show_submit_error('ez-msg');
    } else {
        $("#papform").submit();
    }
}
function delete_po(){
    var but = $(".del-poid");
    but.on("click",function(){
        if(confirm("กำลังจะลบข้อมูลใบสั่งซื้อ\nกด OK เพื่อยึนยันการลบข้อมูล")){
            var data = {};
            var url = $("#ajax_req").val();
            data['request'] = "delete_po";
            data['poid'] = $(this).attr("poid");
            post_ajax(data,url);
        }
    });  
}
function del_process_po(){
    var but = $("#del-process-po");
    but.on("click",function(){
        if(confirm("กำลังจะลบข้อมูลใบจ้างผลิต\nกด OK เพื่อยึนยันการลบข้อมูล")){
            var data = {};
            var url = $("#ajax_req").val();
            data['request'] = "delete_process_po";
            data['poid'] = $("#poid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,url);
        }
    });  
}
function del_mat_po(){
    var but = $("#del-mat-po");
    but.on("click",function(){
        if(confirm("กำลังจะลบข้อมูลใบสั่งวัตถุดิบ\nกด OK เพื่อยึนยันการลบข้อมูล")){
            var data = {};
            var url = $("#ajax_req").val();
            data['request'] = "delete_mat_po";
            data['poid'] = $("#poid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,url);
        }
    });  
}