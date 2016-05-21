function mod_slug(){
$(document).ready(function(){
    var input = $("#slug");
    input.on("change",function(){
        var slug = $(this).val().toUpperCase();
        slug = slug.replace(/\W/g,"");
        slug = slug.substring(0,5);
        $(this).val(slug);
    });
});
}
function del_term(){
$(document).ready(function(){
    var but = $("#del-term");
    but.on("click",function(){
        var tax = $("#tax").val();
        var title;
        if(tax=="customer"){
            title = "ลูกค้า";
        } else {
            title = "ผู้ผลิต";
        }
        confirm_dialog("ลบข้อมูลกลุ่ม"+title+"?","กำลังจะลบข้อมูลกลุ่ม"+title+" กด OK เพื่อยืนยัน",delterm,cancel)
        function delterm(){
            var data = {};
            var url = $("#ajax_req").val();
            data['request'] = "delete_term";
            data['tid'] = $("#tid").val();
            data['tax'] = tax;
            data['redirect'] = $("#redirect").val();
            post_ajax(data,url);
        }
        function cancel(){
            console.log("cancel");
        }
    }); 
});
}