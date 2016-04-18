function sel_transport_type(type){
    $(document).ready(function(){
        var sel = $("#type");
        var gas = $("#bygas");
        var veh = $("#byvehicle");
        if(type==="gas"){
            gas.show();
            veh.hide();
        } else if(type==="none"){
            gas.hide();
            veh.hide();
        } else {
            gas.hide();
            veh.show();
        }
        sel.on("change",function(){
            if(sel.val()==="gas"){
                gas.show();
                veh.hide();
            } else if(sel.val()==="none"){
                gas.hide();
                veh.hide();
            } else {
                gas.hide();
                veh.show();
            }
        });
    });
}
//page machine_test.php
function mac_sel(arrunit){
    var sel = $("#maid");
    var tg1 = $("#input").siblings("label");
    var tg2 = $("#output").siblings("label");
    sel.on("change",function(){
        var unit = arrunit[sel.val()];
        tg1.text("ชิ้นงานขาเข้า ("+unit+")");
        tg2.text("ชิ้นงานสมบูรณ์ ("+unit+")");
    });
}

function process_sel(marr){
    var sel = $(".sel-process select");
    sel.on("change",function(){
        var tg = $(this).attr("id").replace("process","mach");
        var list = marr[$(this).val()];
        var html = "";
        if(typeof list === "object"){
            $.each(list,function(k,v){
                html += "<option value='"+k+"'>"+v+"</option>";
            });
        } else {
            html += "<option value='0'>-- ไม่พบข้อมูล --</option>";
        }
        //console.log(html);
        $("#"+tg).html(html);
        //show mult if process = ตัด
        if($(this).val()==="1"){
            var mult = $(this).attr("id").replace("process","mult");
            $("#"+mult).parent().removeClass("form-hide");
        } else {
            var mult = $(this).attr("id").replace("process","mult");
            $("#"+mult).parent().addClass("form-hide");
            $("#"+mult).val(1);
        }
    });
}
function sel_matcat(link){
    var sel = $("#cat");
    sel.on("change",function(){
        window.location.replace(link+"?cat="+$(this).val());
    });
}
function sel_cal_month(link,month,type,scomp,search){
    var sel = $("#month");
    var v;
    sel.on("change",function(){
        v = "?m="+$(this).val()+"&t="+type+"&sc="+scomp+"&s="+search;
        window.location.replace(link+v);
    });
    var stype = $("#type");
    stype.on("change",function(){
        v = "?t="+$(this).val()+"&m="+month+"&sc="+scomp+"&s="+search;
        window.location.replace(link+v);
    });
    var sc = $("#scomp");
    sc.on("change",function(){
        v = "?sc="+$(this).val()+"&t="+type+"&m="+month+"&s="+search;
        window.location.replace(link+v);
    });
}
function add_more_comp(){
    var but = $("#more-comp");
    var comp = $(".com-sec");
    but.on("mousedown",function(e){
        e.preventDefault();
        comp.removeClass("form-hide");
    });
}
function open_print(){
    $(document).ready(function(){
        window.print();
    });
}
function del_mat(mid,re,url){
    var but = $("#del-mat-but");
    but.on("click",function(){
        if(confirm("การลบวัตถุดิบจะทำให้ข้อมูลการพิมพ์ย้อนหลังที่เคยใช้วัตถุดิบนี้ถูกเปลี่ยน โปรดยืนยันการลบรายการวัตถุดิบ")){
            var data = {};
            data['request'] = "delete_mat";
            data['mid'] = mid;
            data['redirect'] = re;
            post_ajax(data,url);
        }
    });
}
function del_process(pid,re,url){
    var but = $("#del-process-but");
    but.on("click",function(){
        if(confirm("การลบกระบวนการพิมพ์จะทำให้ข้อมูลการพิมพ์ย้อนหลังที่เคยใช้กระบวนการนี้ถูกลบไปด้วย โปรดยืนยันการลบกระบวนการพิมพ์")){
            var data = {};
            data['request'] = "delete_process";
            data['pid'] = pid;
            data['redirect'] = re;
            post_ajax(data,url);
        }
    });
}
function del_function_unit(fid,re,url){
    var but = $("#del-fn-but");
    but.on("click",function(){
        if(confirm("โปรดยืนยันการลบรายการสิ่งพิมพ์")){
            var data = {};
            data['request'] = "del_fn";
            data['fid'] = fid;
            data['redirect'] = re;
            post_ajax(data,url);
        }
    });
}
function del_machine(maid,re,url){
    var but = $("#del-machine-but");
    but.on("click",function(){
        if(confirm("การลบเครื่องจักรจะทำให้ข้อมูลการพิมพ์ย้อนหลังที่เคยใช้เครื่องจักรนี้ถูกเปลี่ยน โปรดยืนยันการลบรายการเครื่องจักร")){
            var data = {};
            data['request'] = "del_machine";
            data['maid'] = maid;
            data['redirect'] = re;
            post_ajax(data,url);
        }
    });
}
function dis_sel(dis_type){
    $(document).ready(function(){
        $("#"+dis_type).removeClass("form-hide");
        var sel = $("#dis_type");
        sel.on("change",function(){
            var tg = $(this).val();
            
            if(tg==="cal-none"){
                $(".cal-group").addClass("form-hide");
            } else {
                $(".cal-group").addClass("form-hide");
                $("#"+tg).removeClass("form-hide");
            }
        });
    });
    
}